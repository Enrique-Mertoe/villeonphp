<?php

namespace Villeon\Manager;

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


