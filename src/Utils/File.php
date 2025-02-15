<?php

namespace Villeon\Utils;

class File
{
    private string $name;
    private string $tmpName;
    private string $type;
    private int $size;
    private int $error;

    private static array $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private static array $videoTypes = ['video/mp4', 'video/avi', 'video/mpeg', 'video/quicktime', 'video/x-ms-wmv'];
    private static array $documentTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation'
    ];

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
    public function resize(float $scale, string $outputPath): bool
    {
        if (!$this->isImage()) {
            return false;
        }

        [$width, $height] = getimagesize($this->tmpName);
        $newWidth = (int)($width * $scale);
        $newHeight = (int)($height * $scale);

        switch ($this->type) {
            case 'image/jpeg':
                $srcImage = imagecreatefromjpeg($this->tmpName);
                break;
            case 'image/png':
                $srcImage = imagecreatefrompng($this->tmpName);
                break;
            case 'image/gif':
                $srcImage = imagecreatefromgif($this->tmpName);
                break;
            case 'image/webp':
                $srcImage = imagecreatefromwebp($this->tmpName);
                break;
            default:
                return false;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and GIF
        if ($this->type === 'image/png' || $this->type === 'image/gif') {
            imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        imagecopyresampled($newImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Ensure destination directory exists
        if (!is_dir($outputPath) && !mkdir($outputPath, 0777, true) && !is_dir($outputPath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $outputPath));
        }

        $outputFile = rtrim($outputPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->name;

        switch ($this->type) {
            case 'image/jpeg':
                imagejpeg($newImage, $outputFile, 90);
                break;
            case 'image/png':
                imagepng($newImage, $outputFile);
                break;
            case 'image/gif':
                imagegif($newImage, $outputFile);
                break;
            case 'image/webp':
                imagewebp($newImage, $outputFile);
                break;
        }

        imagedestroy($srcImage);
        imagedestroy($newImage);

        return true;
    }

    public function isImage(): bool
    {
        return in_array($this->type, self::$imageTypes, true);
    }

    public function isVideo(): bool
    {
        return in_array($this->type, self::$videoTypes, true);
    }

    public function isDocument(): bool
    {
        return in_array($this->type, self::$documentTypes, true);
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
