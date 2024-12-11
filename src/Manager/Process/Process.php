<?php
/**
 * Process.php
 * @package    Villeon\Manager\Process
 * @author     Abuti Martin <abutimartin778@gmail.com>
 * @copyright  2024 Villeon
 * @license    MIT License
 * @version    1.1.0
 * @link       https://github.com/Enrique-Mertoe/villeonphp
 */

namespace Villeon\Manager\Process;

use Closure;

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
            usleep(500000);
        }
        fclose($input);
    }

    public function stop(): void
    {
        proc_close($this->process);
        foreach ($this->pipesHandler->pipes as $pipe) {
            fclose($pipe);
        }
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
            $res = explode("\n", $res);
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

    public function __destruct()
    {
        $this->stop();
    }


}
