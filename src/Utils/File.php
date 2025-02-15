<?php

namespace Villeon\Utils;

class File
{
    private string $name;
    private string $tmpName;
    private string $type;
    private int $size;
    private int $error;

    public function __construct(array $file)
    {
        $this->name = $file['name'];
        $this->tmpName = $file['tmp_name'];
        $this->type = $file['type'];
        $this->size = $file['size'];
        $this->error = $file['error'];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTmpName(): string
    {
        return $this->tmpName;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function save(string $destinationPath): bool
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            return false;
        }

        // Ensure destination directory exists
        if (!is_dir($destinationPath) && !mkdir($destinationPath, 0777, true) && !is_dir($destinationPath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $destinationPath));
        }

        $filePath = rtrim($destinationPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->name;

        return move_uploaded_file($this->tmpName, $filePath);
    }

    public static function init(): array
    {
        $files = [];
        foreach ($_FILES as $key => $file) {
            if (is_array($file['name'])) {
                foreach ($file['name'] as $index => $name) {
                    $files[$key][$index] = new self([
                        'name' => $name,
                        'tmp_name' => $file['tmp_name'][$index],
                        'type' => $file['type'][$index],
                        'size' => $file['size'][$index],
                        'error' => $file['error'][$index],
                    ]);
                }
            } else {
                $files[$key] = new self($file);
            }
        }
        return $files;
    }
}
