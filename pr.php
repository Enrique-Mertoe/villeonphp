<?php

abstract class Pipes
{

    public array $pipes;
    protected array $files;
    protected array $fileHandlers;

    public function __construct()
    {
        $this->pipes = [];
        $tempDir = sys_get_temp_dir();

        foreach ([1 => "out", 2 => "err"] as $index => $file) {
            $filePath = sprintf('%s/smv_proc_%02X.%s', $tempDir, $index, $file);

            $handler = fopen($filePath, 'c+');
            if (!$handler) {
                throw new RuntimeException("Cannot open file: $filePath");
            }

            $this->fileHandlers[$index] = $handler;
            $this->files[$index] = $filePath;
        }

    }

    abstract public function getDescriptors(): array;

    abstract public function read_pipes();

    abstract public function prepareCommand(string $command): string;

    final static function instance(): static
    {
        return new static;
    }
}

class WindowPipes extends Pipes
{

    public function getDescriptors(): array
    {
        return [
            ["pipe", "r"],
            ["file", "NUL", "w"],
            ["pipe", "NUL", "w"],
        ];
    }

    public function read_pipes(): array
    {
        $data = [];

        foreach ($this->fileHandlers as $type => $handler) {
            if (flock($handler, LOCK_EX)) {
                fseek($handler, 0);

                $currentContent = stream_get_contents($handler);
                if (!empty($currentContent)) {
                    $data[$type] = $currentContent;

                    // Clear file content after reading
                    ftruncate($handler, 0);
                    rewind($handler);
                }

                flock($handler, LOCK_UN);
            } else {
                throw new RuntimeException("Unable to acquire lock on file handler.");
            }
        }

        return $data;
    }

    public function prepareCommand(string $command): string
    {
        return $command . " 1> \"" . str_replace("/", "\\", $this->files[1]) . "\" 2>&1";
    }
}

class UnixPipes extends Pipes
{

    public function getDescriptors(): array
    {
        return [
            ["pipe", "r"],
            ["pipe", "w"],
            ["pipe", "w"],
        ];
    }

    public function read_pipes()
    {
        $content = [];
        unset($this->pipes[0]);
        foreach ($this->pipes as $index => $pipe) {

            stream_set_blocking($pipe, false);
            if (!feof($pipe)) {
                $content[$index] = stream_get_contents($pipe);
            }

            stream_set_blocking($pipe, true);

        }
        return $content;
    }

    public function prepareCommand(string $command): string
    {
        return $command;
    }
}

class Process
{
    private string $command;
    private Closure $callback;
    private array $eventsHandlers = [];
    private $process;
    private array $files;
    private array $status;
    private array $pipes = [];

    /**
     * @var WindowPipes|UnixPipes
     */
    private WindowPipes|UnixPipes $pipesHandler;

    public const ERR = "err";
    public const OUT = "out";

    public function __construct($command)
    {
        $this->command = $command;

        if ('\\' == DIRECTORY_SEPARATOR) {
            $this->pipesHandler = WindowPipes::instance();
        } else {
            $this->pipesHandler = UnixPipes::instance();
        }

    }

    public function isActive()
    {
        $this->update();
        return $this->status["running"] ?? 0;
    }

    public function start(): void
    {
        $this->initiate();
        $input = fopen('php://stdin', 'r');
        while ($this->isActive()) {
            if ($this->checkTrapped($input)) {
            };
            usleep(500000);
        }
        fclose($input);
    }

    private function checkTrapped($stdin)
    {

        $read = [$stdin];
        $write = $except = null;
        $changed = stream_select($read, $write, $except, 0, 100000);
        if ($changed > 0) {
            print_r("oop");
            $input = fgets($stdin);
            if (trim($input) === "") {
                echo "Ctrl+C detected. Stopping process.\n";
                return true;
            }
        }
    }

    private function update(): void
    {
        $this->status = proc_get_status($this->process);
        $result = $this->pipesHandler->read_pipes();
        $pipes = [1 => Process::OUT, 2 => Process::ERR];
        foreach ($result as $type => $res) {
            $this->handleEvent("update", $pipes[$type], $res);
        }
    }

    public function on(string $event, Closure $controller): void
    {
        $this->eventsHandlers[$event][] = $controller;
    }

    private function handleEvent(string $name, ...$args): void
    {
        foreach ($this->eventsHandlers[$name] ?? [] as $event) {
            $event(...$args);
        }
    }

    private function initiate(): void
    {
        if ($this->process)
            return;
        $descriptors = $this->descriptors();
        $command = $this->pipesHandler->prepareCommand($this->command);
        $this->process = proc_open($command, $descriptors, $this->pipesHandler->pipes);
    }

    private function descriptors(): array
    {
        return $this->pipesHandler->getDescriptors();
    }


}

class ServerCommand
{
    public function __construct()
    {
        $command = [
            PHP_BINARY,
            "-S",
            "localhost:3500"
        ];
        $command = implode(" ", $command);
        $process = new Process($command);


        $process->on("stop", function () {

        });
        $process->on("update", function ($type, $data) {
            $this->flash($data);
        });

        $process->start();

    }

    private function debug($directoryPath, Closure $callback)
    {
        print_r($directoryPath);
        $previousState = [];

        while (true) {
            // Get all files in the directory
            $files = glob($directoryPath . '/*'); // or use scandir for more control

            foreach ($files as $file) {
                if (is_file($file)) {
                    // Get file modification time
                    $modTime = filemtime($file);

                    // If the file is modified, print out a message
                    if (!isset($previousState[$file]) || $previousState[$file] !== $modTime) {
                        $previousState[$file] = $modTime;  // Update the stored modification time
                        $callback();
                    }
                }
            }

            usleep(1000000); // Check every 1 second
        }
    }

    public static function serve(): ServerCommand
    {
        return new ServerCommand;
    }

    private function flash($data)
    {

        if (str_contains($data, "Development Server")) {
            print_r("Listening on http://localhost:3000");
        }
    }

}

ServerCommand::serve();


