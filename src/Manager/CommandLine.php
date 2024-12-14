<?php

namespace Villeon\Manager;

use JetBrains\PhpStorm\NoReturn;
use Villeon\Utils\Log;

/**
 *
 */
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
                    Log::d(self::TAG,"Running migrations...");
                    break;
                case 'runserver':
                    ServerCommand::serve();
                    break;
                default:
                    Log::e(self::TAG, "Unknown command: $command");
                    Log::i(self::TAG, "Available commands: migrate, runserver\n");
                    break;
            }
        } else {
            Log::e(self::TAG, "No command provided.");
            Log::i(self::TAG, "Usage: php manage [command]");
        }

    }

    /**
     * @param $args
     * @return void
     */
    #[NoReturn] public static function run_command_line($args = null): void
    {

        (new CommandLine)->execute($args);
    }

    /**
     *
     */
    public const TAG = "CommandLine";
}


