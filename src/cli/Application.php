<?php

/**
 * [[]] Div CLI - Official runtime for the Divengine template ecosystem
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program as the file LICENSE.txt; if not, please see
 * https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package divengine/cli
 * @author  Rafa Rodriguez @rafageist [https://rafageist.com]
 * @version 1.0.0
 *
 * @link    https://github.com/divengine/cli
 */

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
        $this->register(new \divengine\commands\RenderCommand());
        $this->register(new \divengine\commands\BuildCommand());
        $this->register(new \divengine\commands\TemplatesCommand());
        $this->register(new \divengine\commands\PackagesCommand());
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
