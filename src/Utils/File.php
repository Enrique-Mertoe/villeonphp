<?php

namespace Villeon\Utils;

use Villeon\Utils\PathLib\Path;

class File
{
    private string $name;
    private string $size;
    private Path $tmpPath;
    private string $type;
    public ?string $savedPath = null;

    public function __construct(array $file)
    {
        $this->name = $file['name'];
        $this->size = $file['size'];
        $this->tmpPath = Path::of($file['tmp_name']);
        $this->type = mime_content_type($this->tmpPath->__toString());
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function getSavedPath(): ?string
    {
        return $this->savedPath;
    }

    public function save(string $destination, ?string $newName = null): bool
    {
        $filename = $this->ensureExtension($newName ?? $this->name);
        $targetPath = Path::of($destination)->join($filename);
        $this->savedPath = $filename;
        return move_uploaded_file($this->tmpPath->__toString(), $targetPath->__toString());
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
        if (!$this->tmpPath->exists() || !$this->isImage()) {
            return false;
        }

        [$width, $height, $type] = getimagesize($this->tmpPath->__toString());
        $newWidth = (int)($width * $factor);
        $newHeight = (int)($height * $factor);

        $image = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($this->tmpPath->__toString()),
            IMAGETYPE_PNG => imagecreatefrompng($this->tmpPath->__toString()),
            IMAGETYPE_GIF => imagecreatefromgif($this->tmpPath->__toString()),
            default => null
        };

        if (!$image) {
            return false;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $qualityValue = (int)($quality * 100);
        $qualityValue = max(1, min($qualityValue, 100));

        $outputName = $this->ensureExtension($newName ?? $this->name);
        $outputPath = Path::createTempFile('scaled_', '.' . $this->getFileExtension());

        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $outputPath->__toString(), $qualityValue);
                break;
            case IMAGETYPE_PNG:
                $pngQuality = (int)((1 - $quality) * 9);
                imagepng($newImage, $outputPath->__toString(), $pngQuality);
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $outputPath->__toString());
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
        return Path::of($this->name)->suffix() ?? "bin";
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
