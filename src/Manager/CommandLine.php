<?php

namespace Villeon\Manager;

use RuntimeException;
use Villeon\Utils\Log;


function startProcess(string $command, ?callable $callback = null, array $env = [], ?string $cwd = null): void
{
    print_r($command);
    if ($cwd !== null && !is_dir($cwd)) {
        throw new RuntimeException(sprintf('The provided cwd "%s" does not exist.', $cwd));
    }
    $envPairs = [];
    foreach ($env as $key => $value) {
        if ($value !== false) {
            $envPairs[] = "$key=$value";
        }
    }

    $descriptors = [
        0 => ['pipe', 'r'], // STDIN
        1 => ['pipe', 'w'], // STDOUT
        2 => ['pipe', 'w'], // STDERR
    ];

    // Start the process using proc_open
    $process = proc_open($command, $descriptors, $pipes, $cwd, $envPairs);

    if (!is_resource($process)) {
        throw new RuntimeException('Failed to start the process.');
    }

    // Handle real-time output with the callback if provided
    if ($callback) {
        while ($output = fgets($pipes[1])) {
            $callback('out', $output); // STDOUT
        }
        while ($error = fgets($pipes[2])) {
            $callback('err', $error); // STDERR
        }
    }

    // Close the pipes
    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);
    if ($exitCode !== 0) {
//        throw new RuntimeException("Process exited with code $exitCode.");
    }
}


class CommandLine
{
    /**
     * @param $args
     * @return void
     */
    public function execute($args): void
    {

        if (count($args) > 1) {
            $command = $args[1];

            switch ($command) {
                case 'migrate':
                    echo "Running migrations...\n";
                    break;
                case 'runserver':
                    ServerCommand::serve();
                    echo "Starting server...\n";
                    error_reporting(0);
                    ini_set('display_errors', 0);
                    exec("php -S localhost:8000 -t bootstrap bootstrap/index.php -q");
                    error_reporting(0);
                    ini_set('display_errors', 0);

                    break;
                default:
                    echo "Unknown command: $command\n";
                    echo "Available commands: migrate, runserver\n";
                    break;
            }
        } else {
            echo "No command provided.\n";
            echo "Usage: php script.php [command]\n";
        }

    }

    public static function run_command_line($args = null): void
    {
        (new CommandLine())->execute($args);
    }
}


