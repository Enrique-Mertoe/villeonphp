<?php
/**
 * WindowPipes.php
 * @package   Villeon\Manager\Process
 * @author     Abuti Martin <abutimartin778@gmail.com>
 * @copyright  2024 Villeon
 * @license    MIT License
 * @version    1.1.0
 * @link       https://github.com/Enrique-Mertoe/villeonphp
 */

namespace Villeon\Manager\Process;

use const LOCK_UN;

class WindowPipes extends Pipes
{
    private int $readPosition = 0;

    public function __construct()
    {
        $this->initWinPipes();
    }


    public function getDescriptors(): array
    {
        //Windows encounter problem when dealing with proc_open especially when you work with
        //pipe descriptors for both stdout and stderr. Therefore, it's a good idea to use file
        //descriptor and setting filename to NUL so that the output is regarded useless
        return [
            ["pipe", "r"],
            ["file", "NUL", "w"],
            ["file", "NUL", "w"],
        ];
    }

    public function read_pipes(): array
    {
        $res = stream_get_contents($this->fileHandler, -1, $this->readPosition);
        if (!empty($res)) {
            $this->readPosition += strlen($res);
        }
        return [$res];
    }

    public function prepareCommand(string $command): string
    {
        //The command is constructed to redirected output to an external file
        // to be used by the @fileHandler to
        //extract the stdout and stderr
        return $command . ' 1> ' . $this->bufferFile . ' 2>&1';
    }

    public function __destruct()
    {
        ftruncate($this->fileHandler, 0);
        rewind($this->fileHandler);
        fclose($this->fileHandler);
        flock($this->lock, LOCK_UN);
        fclose($this->lock);
        unset($this->fileHandler, $this->lock);
    }
}
