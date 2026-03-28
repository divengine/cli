<?php

declare(strict_types=1);

namespace divengine\cli;

class Application
{
    private array $commands = [];
    private string $version = '1.0.0';

    public function __construct()
    {
        $this->registerDefaultCommands();
    }

    private function registerDefaultCommands(): void
    {
        $this->register(new \divengine\commands\HelpCommand($this));
        $this->register(new \divengine\commands\VersionCommand($this));
        $this->register(new \divengine\commands\DoctorCommand());
        $this->register(new \divengine\commands\RenderCommand());
        $this->register(new \divengine\commands\TransformCommand());
        $this->register(new \divengine\commands\BuildCommand());
        $this->register(new \divengine\commands\TemplatesCommand());
        $this->register(new \divengine\commands\PackagesCommand());
        $this->register(new \divengine\commands\ResolveCommand());
    }

    public function register(Command $command): void
    {
        $this->commands[$command->getName()] = $command;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function run(array $argv): int
    {
        array_shift($argv);

        if (empty($argv) || $this->isHelpFlag($argv[0])) {
            return $this->runHelp();
        }

        $commandName = $argv[0];

        if (!isset($this->commands[$commandName])) {
            \divengine\utils\Console::error("Unknown command: {$commandName}");
            \divengine\utils\Console::info("Run 'div help' for available commands.");
            return 1;
        }

        $command = $this->commands[$commandName];
        $args = array_slice($argv, 1);

        if ($this->hasHelpFlag($args)) {
            return $command->showHelp();
        }

        try {
            return $command->run($args);
        } catch (\Throwable $e) {
            \divengine\utils\Console::error($e->getMessage());
            return 1;
        }
    }

    private function isHelpFlag(string $arg): bool
    {
        return $arg === '--help' || $arg === '-h';
    }

    private function hasHelpFlag(array $args): bool
    {
        foreach ($args as $arg) {
            if ($arg === '--help' || $arg === '-h') {
                return true;
            }
        }
        return false;
    }

    private function runHelp(): int
    {
        return $this->commands['help']->run([]);
    }
}
