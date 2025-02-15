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

    public function save(string $destination): bool
    {
        return move_uploaded_file($this->tmpPath, $destination . DIRECTORY_SEPARATOR . $this->name);
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

    public function scale(float $factor, float $quality = 1.0): bool
    {
        if (!$this->isImage()) {
            return false; // Only images can be resized
        }

        [$width, $height, $type] = getimagesize($this->tmpPath);

        $newWidth = (int)($width * $factor);
        $newHeight = (int)($height * $factor);

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

        // Convert quality from float (1.0 = max, <1 reduces)
        $qualityValue = (int)($quality * 100);
        if ($qualityValue < 1) {
            $qualityValue = 1; // Prevent 0 or negative quality
        } elseif ($qualityValue > 100) {
            $qualityValue = 100; // Ensure it doesn't exceed 100
        }

        $outputPath = $this->tmpPath; // Overwrite the existing image

        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $outputPath, $qualityValue);
                break;
            case IMAGETYPE_PNG:
                $pngQuality = (int)((1 - $quality) * 9); // PNG uses 0-9 where 9 is worst quality
                imagepng($newImage, $outputPath, $pngQuality);
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $outputPath);
                break;
        }

        imagedestroy($image);
        imagedestroy($newImage);

        return true;
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

