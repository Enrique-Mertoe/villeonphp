<?php

namespace Villeon\Utils\PathLib;

class Path
{
    private string $path;
    private string $realPath;

    public function __construct(string $path = "")
    {
        $this->path = $path;
        $this->realPath = realpath($path) ?: $this->path;

    }

    public function __toString(): string
    {
        return $this->path;
    }

    public function resolve(): self
    {
        return new self($this->realPath);
    }

    public function exists(): bool
    {
        return file_exists($this->path);
    }

    public function isFile(): bool
    {
        return is_file($this->path);
    }

    public function isDir(): bool
    {
        return is_dir($this->path);
    }

    public function mkdir(int $permissions = 0755, bool $recursive = true): bool
    {
        return mkdir($this->path, $permissions, $recursive);
    }

    public function unlink(): bool
    {
        return $this->isFile() && unlink($this->path);
    }

    public function rmdir(): bool
    {
        return $this->isDir() && rmdir($this->path);
    }

    public function name(): string
    {
        return basename($this->path);
    }

    public function stem(): string
    {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    public function suffix(): string
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    public function parent(): Path
    {
        return new Path(dirname($this->path));
    }

    public function absolute(): Path
    {
        return new Path(realpath($this->path) ?: $this->path);
    }

    public function join(string $subPath): Path
    {
        return new Path($this->path . DIRECTORY_SEPARATOR . $subPath);
    }

    public function rename(string $newName): bool
    {
        $newPath = $this->parent()->join($newName);
        if (rename($this->realPath ?? $this->path, (string)$newPath)) {
            $this->path = (string)$newPath;
            $this->realPath = realpath($this->path) ?: null;
            return true;
        }
        return false;
    }

    public function copy(string $destination): bool
    {
        return copy($this->path, $destination);
    }

    public function move(string $destination): bool
    {
        if (rename($this->path, $destination)) {
            $this->path = $destination;
            return true;
        }
        return false;
    }

    public function readText(): ?string
    {
        return $this->isFile() ? file_get_contents($this->path) : null;
    }

    public function writeText(string $content): bool
    {
        return file_put_contents($this->path, $content) !== false;
    }

    public function appendText(string $content): bool
    {
        return file_put_contents($this->path, $content, FILE_APPEND) !== false;
    }

    public static function cwd(): Path
    {
        return new Path(getcwd());
    }

    public static function home(): Path
    {
        return new Path($_SERVER['HOME'] ?? $_SERVER['HOMEPATH'] ?? '/');
    }

    public function isAbsolute(): bool
    {
        return preg_match('/^(\/|[a-zA-Z]:\\\\)/', $this->path) === 1;
    }

    public function writeAtomic(string $content): bool
    {
        $tempFile = tempnam(dirname($this->path), 'tmp_');
        if (file_put_contents($tempFile, $content) !== false) {
            return rename($tempFile, $this->path);
        }
        return false;
    }

    public function setPermissions(int $mode, bool $recursive = false): bool
    {
        if (!chmod($this->realPath, $mode)) {
            return false;
        }

        if ($recursive && $this->isDir()) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->realPath, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                chmod($item->getPathname(), $mode);
            }
        }

        return true;
    }

    // Create a symbolic link
    public function createSymlink(string $target): bool
    {
        return symlink($target, $this->path);
    }

    public function createHardlink(string $target): bool
    {
        return link($target, $this->path);
    }

    // Check if the path is a symlink
    public function isSymlink(): bool
    {
        return is_link($this->path);
    }

    // Read a symlink target
    public function readSymlink(): ?string
    {
        return $this->isSymlink() ? readlink($this->path) : null;
    }

    public function mirrorTo(string $targetDir): bool
    {
        if (!is_dir($this->path)) {
            return false;
        }

        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $targetDir));
        }

        foreach (scandir($this->path) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $src = $this->path . DIRECTORY_SEPARATOR . $file;
            $dst = $targetDir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($src)) {
                (new self($src))->mirrorTo($dst);
            } else {
                copy($src, $dst);
            }
        }

        return true;
    }

    public static function createTempFile(string $prefix = 'tmp_', string $suffix = ''): Path
    {
        $temp = tempnam(sys_get_temp_dir(), $prefix);
        if ($suffix) {
            $newPath = $temp . $suffix;
            rename($temp, $newPath);
            $temp = $newPath;
        }
        return new self($temp);
    }

    public static function of(string $string): static
    {
        return new static($string);
    }
}
