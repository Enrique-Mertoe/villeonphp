<?php

namespace Villeon\Manager\Process;

abstract class Pipes
{

    public array $pipes;
    protected array $files;
    protected array $fileHandlers;

    public function __construct()
    {
        $this->pipes = [];
        $tempDir = sys_get_temp_dir();

        foreach ([1 => "out", 2 => "err"] as $index => $file) {
            $filePath = sprintf('%s/smv_proc_%02X.%s', $tempDir, $index, $file);

            $handler = fopen($filePath, 'c+');
            if (!$handler) {
                throw new RuntimeException("Cannot open file: $filePath");
            }

            $this->fileHandlers[$index] = $handler;
            $this->files[$index] = $filePath;
        }

    }

    abstract public function getDescriptors(): array;

    abstract public function read_pipes();

    abstract public function prepareCommand(string $command): string;

    final static function instance(): static
    {
        return new static;
    }
}
