<?php

namespace Villeon\Manager;

use Villeon\Support\Components\Process;
use Villeon\Support\Components\Signals;
use Villeon\Utils\Console;
use Villeon\Utils\Log;
use function Symfony\Component\String\b;

function runPhpServer($cmd): void
{
    // Define the PHP command to run
//    $cmd = 'C:\xampp\php\php.exe -S 127.0.0.1:500';

    // Define the paths for the output and error logs
    $outputFile = "C:\\Users\\LOM-TE~1\\AppData\\Local\\Temp\\sf_proc_0.out";
    $errorFile = "C:\\Users\\LOM-TE~1\\AppData\\Local\\Temp\\sf_proc_0.err";

    // Open the process
    $descriptorspec = [
        0 => ["pipe", "r"],  // stdin is a pipe that you can write to
        1 => ["file", $outputFile, "w"],  // stdout is redirected to a file
        2 => ["file", $errorFile, "w"]  // stderr is redirected to a file
    ];

    $process = proc_open($cmd, $descriptorspec, $pipes);


    if (is_resource($process)) {
        // Optionally, you can interact with the process, write to stdin or read from stdout
        // Example: You can write data to stdin if needed
        // fwrite($pipes[0], 'your input data');

        // Close pipes
        fclose($pipes[0]);

        print_r("fff");
        // Wait for the process to finish and get the status
//        $return_value = proc_close($process);
        print_r("fff");

        // You can check the return value to ensure the process was successful
//        if ($return_value === 0) {
//            echo "Process completed successfully.";
//        } else {
//            echo "Process failed with exit code: $return_value";
//        }
        print_r("fff");
    } else {
        echo "Failed to start process.";
        print_r("fff");
    }
}

class ServerCommand
{

    private string $buffer = '';


    public function __construct()
    {
        $command = implode(' ', [
            php_executor(),
            "-S", "127.0.0.1:500",
            __DIR__ . "/s.php"
        ]);
        $this->startProcess($command);

    }


    public static function serve(): ServerCommand
    {
        return new static;
    }

    private function startProcess($command): void
    {
        $process = new Process($command, callback: $this->handleOutPut(), env: array(
            'PATH' => dirname(BASE_PATH),
        ), cwd: "bootstrap");
        $process->start();
        while (true){
//            if (function_exists('pcntl_signal_dispatch')) {
//                print_r("sss");
//                pcntl_signal_dispatch();
//            }
//            $process = $this->getProcess($command);
            $process->update();
            print_r("ml");
            usleep(500 * 1000);
        }
    }


    private function handleOutPut(): \Closure
    {
        return function ($type, $buffer) {
            $this->buffer = $buffer;
//            $this->flash();
            if ($type = Process::OUT)
                Console::Error($this->buffer);
            else
                Console::Error($this->buffer);

        };
    }

    private function flash(): void
    {
        $str = $this->buffer;
        Console::Error($str);
    }


}