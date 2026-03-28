<?php

declare(strict_types=1);

namespace divengine\utils;

class Console
{
    public const RESET = "\033[0m";
    public const BOLD = "\033[1m";
    public const DIM = "\033[2m";
    public const RED = "\033[31m";
    public const GREEN = "\033[32m";
    public const YELLOW = "\033[33m";
    public const BLUE = "\033[34m";
    public const MAGENTA = "\033[35m";
    public const CYAN = "\033[36m";
    public const WHITE = "\033[37m";
    public const GRAY = "\033[90m";

    private static bool $forceNoColor = false;
    private static bool $forceColor = false;

    public static function enableColors(bool $enabled): void
    {
        self::$forceColor = $enabled;
        self::$forceNoColor = !$enabled;
    }

    public static function colorsEnabled(): bool
    {
        return self::supportsColor();
    }

    private static function supportsColor(): bool
    {
        if (self::$forceColor) {
            return true;
        }
        if (self::$forceNoColor) {
            return false;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            return self::windowsSupportsColor();
        }

        if (!function_exists('posix_isatty')) {
            return true;
        }

        return @posix_isatty(STDOUT);
    }

    private static function windowsSupportsColor(): bool
    {
        if (getenv('ANSICON') !== false) {
            return true;
        }
        if (getenv('ConEmuANSI') === 'ON') {
            return true;
        }
        if (getenv('WT_SESSION') !== false) {
            return true;
        }
        if (getenv('TERM') === 'xterm' || getenv('TERM') === 'xterm-256color') {
            return true;
        }

        return true;
    }

    private static function color(string $text, ?string $color = null, bool $bold = false): string
    {
        if (!self::supportsColor() || $color === null) {
            return $text;
        }

        $code = $color;
        if ($bold) {
            $code = self::BOLD . $code;
        }

        return $code . $text . self::RESET;
    }

    public static function line(string $text = ''): void
    {
        echo $text . PHP_EOL;
    }

    public static function write(string $text): void
    {
        echo $text;
    }

    public static function segments(array $segments, bool $newline = true): void
    {
        $output = '';

        foreach ($segments as $segment) {
            $text = $segment['text'] ?? '';
            $color = $segment['color'] ?? null;
            $bold = $segment['bold'] ?? false;

            $output .= self::color($text, $color, $bold);
        }

        if ($newline) {
            self::line($output);
        } else {
            self::write($output);
        }
    }

    public static function error(string $text): void
    {
        self::line(self::color('Error', self::RED, true) . ': ' . $text);
    }

    public static function warning(string $text): void
    {
        self::line(self::color('Warning', self::YELLOW, true) . ': ' . $text);
    }

    public static function success(string $text): void
    {
        self::line(self::color($text, self::GREEN));
    }

    public static function info(string $text): void
    {
        self::line(self::color($text, self::CYAN));
    }

    public static function note(string $text): void
    {
        self::line(self::color('Note:', self::GRAY, true) . ' ' . $text);
    }

    public static function header(string $text): void
    {
        self::line();
        self::line(self::color($text, self::CYAN, true));
        self::line(self::color(str_repeat('-', strlen($text)), self::GRAY));
    }

    public static function dim(string $text): void
    {
        self::line(self::color($text, self::GRAY));
    }

    public static function label(string $text): void
    {
        self::line(self::color($text, self::BLUE, true));
    }

    public static function kv(string $key, string $value): void
    {
        $width = 18;
        $padded = str_pad($key, $width);
        self::line(self::color($padded, self::CYAN) . $value);
    }

    public static function path(string $path): string
    {
        return self::color($path, self::YELLOW);
    }

    public static function ok(string $text = 'OK'): string
    {
        return self::color($text, self::GREEN, true);
    }

    public static function fail(string $text = 'FAIL'): string
    {
        return self::color($text, self::RED, true);
    }

    public static function progress(string $text): void
    {
        if (!self::supportsColor()) {
            echo $text;
            flush();
            return;
        }

        echo $text;
        flush();
    }

    public static function clearProgress(): void
    {
        if (!self::supportsColor()) {
            echo PHP_EOL;
            return;
        }

        echo PHP_EOL;
    }

    public static function spinner(int $frame = 0): string
    {
        $frames = ['|', '/', '-', '\\'];
        return $frames[$frame % 4];
    }

    public static function bullet(string $text, ?string $color = null): void
    {
        $bullet = self::color('*', $color ?? self::CYAN, true);
        self::line($bullet . ' ' . $text);
    }

    public static function status(string $status, string $message): void
    {
        $color = match (strtolower($status)) {
            'ok', 'success', 'done' => self::GREEN,
            'fail', 'error', 'failed' => self::RED,
            'warn', 'warning' => self::YELLOW,
            'info' => self::CYAN,
            default => self::WHITE,
        };

        self::segments([
            ['text' => '[' . str_pad($status, 5) . ']', 'color' => $color, 'bold' => true],
            ['text' => ' ' . $message],
        ]);
    }

    public static function table(array $rows, array $headers = []): void
    {
        if (empty($rows) && empty($headers)) {
            return;
        }

        $allRows = array_merge([$headers], $rows);
        $widths = [];

        foreach ($allRows as $row) {
            foreach ($row as $col => $value) {
                $len = strlen((string) $value);
                $widths[$col] = max($widths[$col] ?? 0, $len);
            }
        }

        $separator = '+';
        foreach ($widths as $width) {
            $separator .= str_repeat('-', $width + 2) . '+';
        }

        self::line($separator);

        if (!empty($headers)) {
            $cells = [];
            foreach ($headers as $col => $header) {
                $cells[] = ['text' => ' ' . str_pad($header, $widths[$col]) . ' ', 'color' => self::CYAN, 'bold' => true];
            }
            self::segments($cells);
            self::line($separator);
        }

        foreach ($rows as $row) {
            $cells = [];
            foreach ($row as $col => $value) {
                $cells[] = ['text' => ' ' . str_pad((string) $value, $widths[$col]) . ' '];
            }
            self::segments($cells);
        }

        self::line($separator);
    }
}
