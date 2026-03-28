<?php

declare(strict_types=1);

namespace divengine\core;

class Filesystem
{
    public static function normalizePath(string $path): string
    {
        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        return $path;
    }

    public static function absolutePath(string $path): string
    {
        if (self::isAbsolute($path)) {
            return self::normalizePath($path);
        }
        return self::normalizePath(getcwd() . DIRECTORY_SEPARATOR . $path);
    }

    public static function isAbsolute(string $path): bool
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return preg_match('/^[A-Z]:/i', $path) === 1;
        }
        return str_starts_with($path, '/');
    }

    public static function readFile(string $path): string
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("File not found: {$path}");
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException("Could not read file: {$path}");
        }

        return $content;
    }

    public static function writeFile(string $path, string $content): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $result = file_put_contents($path, $content);
        if ($result === false) {
            throw new \RuntimeException("Could not write file: {$path}");
        }
    }

    public static function ensureDir(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    public static function listTemplates(string $rootPath): array
    {
        $templates = [];

        if (!is_dir($rootPath)) {
            return $templates;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'tpl') {
                $relativePath = self::relativePath($rootPath, $file->getPathname());
                $templates[] = [
                    'path' => $relativePath,
                    'full_path' => $file->getPathname(),
                    'name' => $file->getBasename('.tpl'),
                ];
            }
        }

        return $templates;
    }

    public static function relativePath(string $from, string $to): string
    {
        $from = self::absolutePath($from);
        $to = self::absolutePath($to);

        $fromParts = explode(DIRECTORY_SEPARATOR, rtrim($from, DIRECTORY_SEPARATOR));
        $toParts = explode(DIRECTORY_SEPARATOR, rtrim($to, DIRECTORY_SEPARATOR));

        $commonLength = 0;
        $minLength = min(count($fromParts), count($toParts));

        for ($i = 0; $i < $minLength; $i++) {
            if ($fromParts[$i] === $toParts[$i]) {
                $commonLength++;
            } else {
                break;
            }
        }

        $upCount = count($fromParts) - $commonLength;
        $downParts = array_slice($toParts, $commonLength);

        $relative = [];
        for ($i = 0; $i < $upCount; $i++) {
            $relative[] = '..';
        }
        $relative = array_merge($relative, $downParts);

        return implode(DIRECTORY_SEPARATOR, $relative);
    }

    public static function canRead(string $path): bool
    {
        return is_readable($path);
    }

    public static function canWrite(string $path): bool
    {
        if (file_exists($path)) {
            return is_writable($path);
        }
        $dir = dirname($path);
        return is_dir($dir) && is_writable($dir);
    }

    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    public static function isFile(string $path): bool
    {
        return is_file($path);
    }

    public static function isDir(string $path): bool
    {
        return is_dir($path);
    }
}
