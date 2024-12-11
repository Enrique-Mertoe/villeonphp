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
                    ServerCommand::serve();
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


