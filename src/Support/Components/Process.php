<?php

namespace Villeon\Support\Components;

use Closure;
use RuntimeException;

/**
 *
 */
class Process
{
    /**
     *
     */
    public const ERR = "err";
    /**
     *
     */
    public const OUT = "out";

    /**
     *
     */
    public const READY = "ready";
    /**
     *
     */
    public const STARTED = "started";
    /**
     *
     */
    public const TERMINATED = "terminated";

    /**
     * @var string|null
     */
    private ?string $cwd;
    /**
     * @var Closure|callable|null
     */
    private ?Closure $callback;
    /**
     * @var string
     */
    private string $command;
    public bool $isRunning = false;
    /**
     * @var array
     */
    private array $env;
    /**
     * @var array|array[]
     */
    private array $descriptors;

    /**
     * @var array
     */
    private array $pipes = [];
    /**
     * @var string
     */
    private string $status = self::READY;
    /**
     * @var null|resource
     */
    private $process = null;

    private string $outputFile;
    private string $errorFile;
    private array $processInfo;


    /**
     * @param string $command
     * @param callable|null $callback
     * @param array $env
     * @param string|null $cwd
     */
    public function __construct(string    $command,
                                ?callable $callback = null,
                                array     $env = [],
                                ?string   $cwd = null)
    {
        if ($cwd !== null && !is_dir($cwd)) {
            throw new RuntimeException(sprintf('The provided cwd "%s" does not exist.', $cwd));
        }
        $this->cwd = $cwd;
        $this->callback = $callback;
        $this->command = $command;
        $this->env = [];
        foreach ($env as $key => $value) {
            if ($value !== false) {
                $this->env[] = "$key=$value";
            }
        }
        $this->outputFile = sys_get_temp_dir() . '\\smv_process.out';
        $this->errorFile = sys_get_temp_dir() . '\\smv_process.err';
        $this->descriptors = [
            0 => ['pipe', 'r'], // STDIN
            1 => ['file', $this->outputFile, 'w'], // STDOUT
            2 => ['file', $this->errorFile, 'w'], // STDERR
        ];

    }

    /**
     * @return void
     */
    public function start(): void
    {

        if ('\\' === \DIRECTORY_SEPARATOR)
            $this->processCommand();
        try {


            $this->process = proc_open($this->command, $this->descriptors, $this->pipes, $this->cwd, $this->env);
        } finally {

        }
        if (!is_resource($this->process)) {
            throw new RuntimeException('Failed to start the process.');
        }
        fclose($this->pipes[0]);


        $this->status = self::STARTED;
        if ($this->callback !== null) {
            $this->handleOutput();
        }
//        $this->enableSignalHandling();
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        if (self::STARTED !== $this->status) {
            return false;
        }
        $this->updateStatus();
        return $this->processInfo['running'];
    }

    private function updateStatus(): void
    {
        if (self::STARTED !== $this->status) {
            return;
        }
        $this->processInfo = proc_get_status($this->process);
    }

    /**
     * @return void
     */
    private function handleOutput(): void
    {
        if (file_exists($this->outputFile) && filesize($this->outputFile) > 0) {
            $output = file_get_contents($this->outputFile);
            if ($output !== '') {
                print_r($output);
                ($this->callback)(self::OUT, $output);
            }
        }
        if (file_exists($this->errorFile) && filesize($this->errorFile) > 0) {
            $error = file_get_contents($this->errorFile);
            if ($error !== '') {
                ($this->callback)(self::ERR, $error);
            }
        }
    }

    /**
     * @return void
     */
    public function terminate(): void
    {
        if ($this->process === null) {
            throw new RuntimeException('Cannot terminate a process that has not been started.');
        }

        proc_terminate($this->process);
        proc_close($this->process);
        $this->process = null;
        $this->status = self::TERMINATED;
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->process !== null) {
            $this->terminate();
        }
        foreach ($this->pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }
    }

    private function processCommand(): void
    {
//        $outputFile = sys_get_temp_dir() . '\\villeon_.out';
//        $errorFile = sys_get_temp_dir() . '\\villeon_.err';
//        $cmd = '"C:\Windows\system32\cmd.exe" /V:ON /E:ON /D /C (';
//        $cmd .=$this->command . ") ". '1>"C:\Users\LOM-TE~1\AppData\Local\Temp\sf_proc_0.out" 2>"C:\Users\LOM-TE~1\AppData\Local\Temp\sf_proc_0.err"';
//        $this->command = $cmd;

    }
    public function enableSignalHandling(): void
    {
//        if (!function_exists('pcntl_signal')) {
//            throw new RuntimeException('PCNTL extension is not available.');
//        }
        $this->isRunning = true;

//        pcntl_signal(\SIGINT, function () {
//            echo "SIGINT received. Stopping process...\n";
//            $this->stopRequested = true;
//        });
    }

    public function update()
    {
        $this->updateStatus();
    }
}
