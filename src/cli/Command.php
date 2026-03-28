<?php

declare(strict_types=1);

namespace divengine\cli;

use divengine\utils\Console;

abstract class Command
{
    protected string $name;
    protected string $description = '';
    protected string $usage = '';
    protected string $help = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUsage(): string
    {
        return $this->usage ?: "div {$this->name}";
    }

    public function getHelp(): string
    {
        return $this->help;
    }

    public function showHelp(): int
    {
        Console::line();
        
        Console::segments([
            ['text' => 'Usage: ', 'color' => Console::CYAN, 'bold' => true],
            ['text' => $this->getUsage()],
        ]);
        
        Console::line();

        if ($this->description) {
            Console::line('  ' . $this->description);
            Console::line();
        }

        if ($this->help) {
            Console::dim(str_repeat('-', 50));
            Console::line($this->help);
            Console::dim(str_repeat('-', 50));
            Console::line();
        }

        Console::note("Run 'div help' for available commands.");

        return 0;
    }

    abstract public function run(array $args): int;

    protected function getOption(string $key, array $args, mixed $default = null): mixed
    {
        foreach ($args as $arg) {
            if (str_starts_with($arg, "--{$key}=")) {
                return substr($arg, strlen($key) + 3);
            }
        }
        return $default;
    }

    protected function hasFlag(string $flag, array $args): bool
    {
        return in_array("--{$flag}", $args, true);
    }
}
