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

class ServerCommand
{
    public function __construct()
    {
        $command = [
            PHP_BINARY,
            "-S",
            "localhost:3500",
            "\"" . __DIR__ . "/server.php\""
        ];
        $command = implode(" ", $command);
        $process = new Process($command);


        $process->on("stop", function () {

        });
        $process->on("update", function ($type, $data) {
            foreach ($data as $d) {
                $this->flash($d);
            }
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

        if (str_contains($data, "Development Server")) {
            Console::Success("SERVER: <b>[http://localhost:3500]</b>");
            Console::Error("<b>DEBUG: OFF</b>");
            Console::Warn("<b><i>Press CTR + C to stop.</i></b>");
        } else if (str_contains($data, " GET /")) {
            Console::Write($this->formatBuffer($data));
        } else if (str_contains($data, " Warning:")) {
            Console::Warn($data);
        }
    }

    private function formatBuffer($buffer)
    {
        $cleanBuffer = preg_replace('#\x1B(?:[@-Z\\-_]|\[[0-?]*[ -/]*[@-~])#', '', $buffer);
        $pattern = '/\[(.*?)] (\S+) (.*?) (\S+)/';
        if (preg_match($pattern, $cleanBuffer, $matches)) {
            $time = $matches[1];
            $method = $matches[2];
            $uri = $matches[3];
            $status = str_replace("-", "", $matches[4]);

            return "~$time   <b>$method:$status</b> › <i>$uri</i>";
        }
        return $buffer;
    }

}
