<?php
/**
 * ServerCommand.php
 *
 * This file contains the implementation of the ServerCommand class,
 * which manages a PHP development server process and handles console output.
 *
 * @package    Villeon\Manager
 * @author     Abuti Martin <abutimartin778@gmail.com>
 * @copyright  2024 Villeon
 * @license    MIT License
 * @version    1.1.0
 * @link       https://github.com/Enrique-Mertoe/villeonphp
 */

namespace Villeon\Manager;

use Closure;
use Villeon\Manager\Process\Process;
use Villeon\Utils\Console;
use Villeon\Utils\Log;

/**
 *
 */
class ServerCommand
{
    private const TAG = "SERVER";
    private int $port;

    /**
     *
     */
    public function __construct()
    {
        $this->port = 3500;
        $this->tryRun($this->port);

    }

    private function tryRun(int $port): void
    {
        $command = $this->command($port);
        $command = implode(" ", $command);
        $process = new Process($command);
        $process->on("update", function ($type, $data) {
            foreach ($data as $d) {
                $this->flash($d);
            }
        });
        if (!$this->isPortInUse("127.0.0.1", $port)) {
            $this->port = $port;
            $process->start();
        } else if ($port > 3510) {
            Log::e("SERVER ERROR", "All allowed ports are in use.");
        } else {
            $newPort = $port + 1;
            Log::e("SERVER ERROR", "Port $port already in use. Tying port $newPort");
            $this->tryRun($newPort);
        }
    }

    private function isPortInUse($ip, $port): bool
    {
        $connection = @fsockopen($ip, $port, $errno, $err_str, 2); // 2 seconds timeout
        if ($connection) {
            fclose($connection);
            return true;
        }
        return false;
    }

    /**
     * @param $directoryPath
     * @param Closure $callback
     * @return mixed
     */
    private function debug($directoryPath, Closure $callback): mixed
    {

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

    /**
     * Starts the server by creating a new instance of ServerCommand.
     *
     * @return ServerCommand The instance of the ServerCommand.
     */
    public static function serve(): ServerCommand
    {
        return new ServerCommand;
    }

    /**
     * Processes and formats server output for console display.
     *
     * @param string $data The data output by the server process.
     */
    private function flash(string $data): void
    {
        $data = str($data);
        if (str_contains($data, "Development Server")) {
            Console::Info($this->getLabel());
            Console::Success("SERVER: <b>[http://127.0.0.1:$this->port]</b>");
            Console::Error("<b>DEBUG: OFF</b>");
            Console::Warn("<b><i>Press CTR + C to stop.</i></b>");
        } else if ($data->contains("[GET:", "[POST:")) {
            $this->display($this->formatBuffer($data));
        } else if (str_contains($data, "[ERROR]")) {
            $data = str($data);
            $data->replace(["__smv__", "[ERROR]"], ["\n", ""])->trim();
            Log::e(self::TAG, $data);
        } elseif ($data->contains("[USER_OUT]")) {
            $data->replace(["__smv__", "[USER_OUT]"], ["\n", ""])->trim();
            Log::d("SYSTEM OUT", $data);
        }
    }


    /**
     * @param string $buffer
     * @return array
     */
    private function formatBuffer(string $buffer): array
    {
        $cleanBuffer = preg_replace('#\x1B(?:[@-Z\\-_]|\[[0-?]*[ -/]*[@-~])#', '', $buffer);

        $type = null;
        $pattern = '/~\[(.*?)] \[(.*?):(\d+)] › (.*)/';

        if (preg_match($pattern, $cleanBuffer, $matches)) {
            $type = (int)$matches[3];
            $buffer = "~$matches[1] [$matches[2]:$matches[3]] › <i>$matches[4]</i>";
        }
        return [$buffer, $type];
    }

    private function display($buffer): void
    {
        match ($buffer[1]) {
            200 => Console::Write($buffer[0]),
            404 => Console::Info($buffer[0]),
            500 => Console::Error($buffer[0]),
            default => Console::Warn($buffer[0]),
        };
    }

    /**
     * @return array
     */
    private function command(int $port): array
    {
        return [
            PHP_BINARY,
            "-S",
            "127.0.0.1:$port",
            "\"" . __DIR__ . "/server.php\""
        ];
    }

    function getLabel(): string
    {
        $str = <<<EOT
          __     ___ _ _                  ____  _   _ ____  
          \ \   / (_) | | ___  ___  _ __ |  _ \| | | |  _ \ 
           \ \ / /| | | |/ _ \/ _ \| '_ \| |_) | |_| | |_) |
            \ V / | | | |  __/ (_) | | | |  __/|  _  |  __/ 
             \_/  |_|_|_|\___|\___/|_| |_|_|   |_| |_|_| Version 1.0
          EOT;
        return str($str);
    }

}
