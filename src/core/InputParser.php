<?php

declare(strict_types=1);

namespace divengine\core;

class InputParser
{
    private const SUPPORTED_FORMATS = ['json', 'yaml', 'yml', 'xml', 'php'];

    public static function parse(string $input, ?string $format = null): array
    {
        if ($format === null) {
            $format = self::detectFormat($input);
        }

        return match (strtolower($format)) {
            'json' => self::parseJson($input),
            'yaml', 'yml' => self::parseYaml($input),
            'xml' => self::parseXml($input),
            'php' => self::parsePhp($input),
            default => throw new \InvalidArgumentException(
                "Unsupported format: {$format}. Supported: " . implode(', ', self::SUPPORTED_FORMATS)
            ),
        };
    }

    public static function parseFile(string $filePath, ?string $format = null): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Input file not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        
        if ($format === null) {
            $format = self::detectFormatFromPath($filePath);
        }

        return self::parse($content, $format);
    }

    private static ?string $stdinCache = null;

    public static function hasStdinData(): bool
    {
        if (self::$stdinCache !== null) {
            return self::$stdinCache !== '';
        }

        if (feof(STDIN)) {
            self::$stdinCache = '';
            return false;
        }

        $stat = fstat(STDIN);
        if ($stat && ($stat['mode'] & 0170000) === 0 && $stat['size'] === 0) {
            return false;
        }

        $content = fgets(STDIN);
        if ($content === false) {
            self::$stdinCache = '';
            return false;
        }

        self::$stdinCache = $content;
        return true;
    }

    public static function parseStdin(?string $format = null): array
    {
        if (self::$stdinCache !== null) {
            $content = self::$stdinCache;
            self::$stdinCache = null;
        } else {
            $content = '';
            while (!feof(STDIN)) {
                $chunk = fgets(STDIN);
                if ($chunk === false) {
                    break;
                }
                $content .= $chunk;
            }
        }
        
        if (trim($content) === '') {
            throw new \RuntimeException("No data available on stdin");
        }

        if ($format === null) {
            $format = self::detectFormat($content);
        }

        return self::parse($content, $format);
    }

    public static function supportedFormats(): array
    {
        return self::SUPPORTED_FORMATS;
    }

    private static function detectFormat(string $input): string
    {
        $input = trim($input);

        if (str_starts_with($input, '<?php')) {
            return 'php';
        }

        if (str_starts_with($input, '{') || str_starts_with($input, '[')) {
            return 'json';
        }

        if (str_starts_with($input, '<')) {
            return 'xml';
        }

        return 'yaml';
    }

    private static function detectFormatFromPath(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        return match ($ext) {
            'json' => 'json',
            'yaml', 'yml' => 'yaml',
            'xml' => 'xml',
            'php' => 'php',
            default => self::detectFormat(file_get_contents($path)),
        };
    }

    private static function parseJson(string $input): array
    {
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("JSON parse error: " . json_last_error_msg());
        }

        return $data ?? [];
    }

    private static function parseYaml(string $input): array
    {
        if (!function_exists('yaml_parse')) {
            if (!class_exists(\Symfony\Component\Yaml\Yaml::class)) {
                return self::parseYamlSimple($input);
            }
            return \Symfony\Component\Yaml\Yaml::parse($input) ?? [];
        }

        $data = yaml_parse($input);
        
        if ($data === false) {
            return self::parseYamlSimple($input);
        }

        return $data;
    }

    private static function parseYamlSimple(string $input): array
    {
        $lines = explode("\n", $input);
        $result = [];
        $stack = [&$result];
        $indentStack = [-1];

        foreach ($lines as $line) {
            if (trim($line) === '' || str_starts_with(trim($line), '#')) {
                continue;
            }

            preg_match('/^(\s*)(.*)$/', $line, $matches);
            $indent = strlen($matches[1]);
            $content = trim($matches[2]);

            while ($indent <= end($indentStack) && count($stack) > 1) {
                array_pop($stack);
                array_pop($indentStack);
            }

            if (str_contains($content, ':')) {
                $colonPos = strpos($content, ':');
                $key = trim(substr($content, 0, $colonPos));
                $value = trim(substr($content, $colonPos + 1));

                if ($value === '' || $value === '|' || $value === '>') {
                    $newArray = [];
                    $stack[count($stack) - 1][$key] = &$newArray;
                    $stack[] = &$newArray;
                    $indentStack[] = $indent;
                } else {
                    $stack[count($stack) - 1][$key] = self::parseYamlValue($value);
                }
            } else {
                $stack[count($stack) - 1][] = self::parseYamlValue($content);
            }
        }

        return $result;
    }

    private static function parseYamlValue(string $value): mixed
    {
        $value = trim($value);

        if ($value === 'true' || $value === 'false') {
            return $value === 'true';
        }

        if ($value === 'null' || $value === '~') {
            return null;
        }

        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^\[(.*)\]$/', $value, $matches)) {
            $items = array_map('trim', explode(',', $matches[1]));
            return array_map([self::class, 'parseYamlValue'], $items);
        }

        return $value;
    }

    private static function parseXml(string $input): array
    {
        $input = trim($input);
        
        if (str_starts_with($input, '<?xml')) {
            $input = preg_replace('/<\?xml[^?]*\?>/', '', $input);
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($input);
        
        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $errorMsg = !empty($errors) ? $errors[0]->message : 'Invalid XML';
            throw new \RuntimeException("XML parse error: " . trim($errorMsg));
        }

        $rootName = $xml->getName();
        $rootData = self::xmlToArray($xml);

        if ($xml->count() === 0) {
            return [$rootName => $rootData];
        }

        return [$rootName => $rootData];
    }

    private static function parsePhp(string $input): array
    {
        if (str_starts_with(trim($input), '<?php')) {
            $tempFile = tempnam(sys_get_temp_dir(), 'div_data_');
            if ($tempFile === false) {
                throw new \RuntimeException("Failed to create temp file for PHP evaluation");
            }
            
            try {
                file_put_contents($tempFile, $input);
                $data = require $tempFile;
            } finally {
                unlink($tempFile);
            }
        } else {
            $data = require $input;
        }

        if (!is_array($data)) {
            throw new \RuntimeException("PHP file must return an array, got " . gettype($data));
        }

        return $data;
    }

    private static function xmlToArray(\SimpleXMLElement $element): mixed
    {
        $result = [];
        $hasAttributes = false;
        $attributes = [];

        foreach ($element->attributes() as $name => $value) {
            $hasAttributes = true;
            $attributes['@' . $name] = self::parseXmlText((string) $value);
        }

        if ($element->count() === 0) {
            $text = (string) $element;
            if ($text !== '') {
                $result = self::parseXmlText($text);
            }
            if ($hasAttributes) {
                $result = array_merge($attributes, is_array($result) ? $result : []);
            }
            return !empty($result) || $hasAttributes ? $result : null;
        }

        foreach ($element->children() as $child) {
            $childName = $child->getName();
            $childValue = self::xmlToArray($child);

            if (isset($result[$childName])) {
                if (!is_array($result[$childName]) || (isset($result[$childName][0]) && !is_array($result[$childName][0]))) {
                    $result[$childName] = [$result[$childName]];
                }
                if (is_array($childValue)) {
                    $result[$childName][] = $childValue;
                }
            } else {
                $result[$childName] = $childValue;
            }
        }

        if ($hasAttributes) {
            $result = array_merge($attributes, $result);
        }

        return $result;
    }

    private static function parseXmlText(string $text): mixed
    {
        $text = trim($text);

        if ($text === 'true') {
            return true;
        }
        if ($text === 'false') {
            return false;
        }
        if ($text === 'null') {
            return null;
        }
        if (is_numeric($text)) {
            return str_contains($text, '.') ? (float) $text : (int) $text;
        }

        return $text;
    }
}
