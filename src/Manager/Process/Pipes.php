<?php

namespace Villeon\Manager\Process;

/**
 *
 */
abstract class Pipes
{

    /**
     * @var array $pipes
     */
    public array $pipes=[];
    /**
     * @var string $bufferFile
     */
    protected string $bufferFile;

    /**
     * @var resource $fileHandler
     */
    protected $fileHandler;
    /**
     * @var resource $lock
     */
    protected $lock = null;
    /**
     * @return void
     */
    protected function initWinPipes(): void
    {
        $tempDir = sys_get_temp_dir();
        //Suppress errors
        set_error_handler(fn() => null);
        for ($i = 0; ; ++$i) {
            $filePath = sprintf('%s/smv_process_%02X.out', $tempDir, $i);
            if (!($handler = fopen($filePath, 'w'))
                || !fclose($handler) ||
                !$handler = fopen($filePath, 'r')) {
                continue;
            }
            $this->fileHandler = $handler;
            $this->bufferFile = $filePath;
            break;
        }
        //restore errors
        restore_error_handler();

    }

    /**
     * @return array
     */
    abstract public function getDescriptors(): array;

    /**
     * @return mixed
     */
    abstract public function read_pipes();

    /**
     * @param string $command
     * @return string
     */
    abstract public function prepareCommand(string $command): string;

    /**
     * @return static
     */
    final static function instance(): static
    {
        return new static;
    }
}
