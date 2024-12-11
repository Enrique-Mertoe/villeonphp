<?php

namespace Villeon\Manager\Process;

class WindowPipes extends Pipes
{

    public function getDescriptors(): array
    {
        return [
            ["pipe", "r"],
            ["file", "NUL", "w"],
            ["pipe", "NUL", "w"],
        ];
    }

    public function read_pipes(): array
    {
        $data = [];

        foreach ($this->fileHandlers as $type => $handler) {
            if (flock($handler, LOCK_EX)) {
                fseek($handler, 0);

                $currentContent = stream_get_contents($handler);
                if (!empty($currentContent)) {
                    $data[$type] = $currentContent;

                    // Clear file content after reading
                    ftruncate($handler, 0);
                    rewind($handler);
                }

                flock($handler, LOCK_UN);
            } else {
                throw new RuntimeException("Unable to acquire lock on file handler.");
            }
        }

        return $data;
    }

    public function prepareCommand(string $command): string
    {
        return $command . " 1> \"" . str_replace("/", "\\", $this->files[1]) . "\" 2>&1";
    }
}
