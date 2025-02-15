<?php

namespace Villeon\Utils;

class File
{
    private string $name;
    private string $tmpPath;
    private string $type;

    public function __construct(array $file)
    {
        $this->name = $file['name'];
        $this->tmpPath = $file['tmp_name'];
        $this->type = mime_content_type($this->tmpPath);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function save(string $destination, ?string $newName = null): bool
    {
        $filename = $this->ensureExtension($newName ?? $this->name);
        $targetPath = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        return move_uploaded_file($this->tmpPath, $targetPath);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->type, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->type, 'video/');
    }

    public function isDoc(): bool
    {
        return in_array($this->type, [
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ]);
    }

    public function scale(float $factor, float $quality = 1.0, ?string $newName = null): bool
    {
        if (!$this->isImage()) {
            return false;
        }

        list($width, $height, $type) = getimagesize($this->tmpPath);

        $newWidth = (int) ($width * $factor);
        $newHeight = (int) ($height * $factor);

        $image = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($this->tmpPath),
            IMAGETYPE_PNG => imagecreatefrompng($this->tmpPath),
            IMAGETYPE_GIF => imagecreatefromgif($this->tmpPath),
            default => null
        };

        if (!$image) {
            return false;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $qualityValue = (int) ($quality * 100);
        $qualityValue = max(1, min($qualityValue, 100));

        $outputName = $this->ensureExtension($newName ?? $this->name);
        $outputPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $outputName;

        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $outputPath, $qualityValue);
                break;
            case IMAGETYPE_PNG:
                $pngQuality = (int) ((1 - $quality) * 9);
                imagepng($newImage, $outputPath, $pngQuality);
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $outputPath);
                break;
        }

        imagedestroy($image);
        imagedestroy($newImage);

        $this->tmpPath = $outputPath;
        $this->name = $outputName;

        return true;
    }

    private function ensureExtension(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (empty($extension)) {
            $extension = $this->getFileExtension();
            $filename .= '.' . $extension;
        }
        return $filename;
    }

    private function getFileExtension(): string
    {
        return match ($this->type) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'text/plain' => 'txt',
            default => 'bin' // fallback extension
        };
    }

    public static function init(): array
    {
        $files = [];
        foreach ($_FILES as $key => $file) {
            $files[$key] = new self($file);
        }
        return $files;
    }
}


