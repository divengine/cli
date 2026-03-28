<?php

declare(strict_types=1);

namespace divengine\core;

class ArgParser
{
    public string $command = '';
    public array $flags = [];
    public array $positional = [];
    private array $options = [];

    public function __construct(array $args)
    {
        $this->parse($args);
    }

    private function parse(array $args): void
    {
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--')) {
                $this->parseOption($arg);
            } elseif (str_starts_with($arg, '-')) {
                $this->flags[] = substr($arg, 1);
            } else {
                $this->positional[] = $arg;
            }
        }
    }

    private function parseOption(string $arg): void
    {
        $option = substr($arg, 2);

        if (str_contains($option, '=')) {
            [$key, $value] = explode('=', $option, 2);
            $this->options[$key] = $value;
        } else {
            $this->options[$option] = true;
        }
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    public function hasOption(string $key): bool
    {
        return isset($this->options[$key]);
    }

    public function hasFlag(string $flag): bool
    {
        return in_array($flag, $this->flags, true);
    }

    public function getPositional(int $index, mixed $default = null): mixed
    {
        return $this->positional[$index] ?? $default;
    }

    public function getPositionalAll(): array
    {
        return $this->positional;
    }

    public function getOptionOrArg(string $optionKey, int $positionalIndex, mixed $default = null): mixed
    {
        if ($this->hasOption($optionKey)) {
            return $this->getOption($optionKey);
        }
        return $this->getPositional($positionalIndex, $default);
    }
}
