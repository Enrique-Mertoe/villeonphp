<?php
/**
 * UnixPipes.php
 * @package    Villeon\Manager\Process
 * @author     Abuti Martin <abutimartin778@gmail.com>
 * @copyright  2024 Villeon
 * @license    MIT License
 * @version    1.1.0
 * @link       https://github.com/Enrique-Mertoe/villeonphp
 */

namespace Villeon\Manager\Process;

class UnixPipes extends Pipes
{

    public function getDescriptors(): array
    {
        return [
            ["pipe", "r"],
            ["pipe", "w"],
            ["pipe", "w"],
        ];
    }

    public function read_pipes(): array
    {
        $content = '';
        unset($this->pipes[0]);
        foreach ($this->pipes as $index => $pipe) {
            stream_set_blocking($pipe, false);
            if (!feof($pipe)) {
                $content.= stream_get_contents($pipe);
            }
            stream_set_blocking($pipe, true);

        }
        return [$content];
    }

    public function prepareCommand(string $command): string
    {
        return $command;
    }
}
