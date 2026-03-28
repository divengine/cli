<?php

declare(strict_types=1);

namespace divengine\commands;

use divengine\cli\Command;
use divengine\cli\Application;
use divengine\utils\Console;
use divengine\core\ArgParser;

class HelpCommand extends Command
{
    protected string $name = 'help';
    protected string $description = 'Show available commands and usage information';
    protected string $usage = 'div help [command]';
    protected string $help = 'Displays help information about available commands.

Without arguments, shows all available commands.
With a command argument, shows detailed help for that specific command.

Arguments:
  [command]          Show help for a specific command

Options:
  -h, --help         Show this help message';

    public function __construct(private Application $app) {}

    public function run(array $args): int
    {
        $parser = new ArgParser($args);
        $commandName = $parser->getPositional(0);

        if ($commandName) {
            return $this->showCommandHelp($commandName);
        }

        return $this->showAllCommands();
    }

    private function showCommandHelp(string $commandName): int
    {
        $commands = $this->app->getCommands();

        if (!isset($commands[$commandName])) {
            Console::error("Unknown command: {$commandName}");
            Console::line();
            Console::info("Run 'div help' to see all available commands.");
            return 1;
        }

        $command = $commands[$commandName];
        $command->showHelp();

        return 0;
    }

    private function showAllCommands(): int
    {
        Console::header('Divengine CLI');
        Console::line();

        $commands = $this->app->getCommands();
        foreach ($commands as $command) {
            Console::segments([
                ['text' => '  ' . str_pad($command->getName(), 12), 'color' => Console::CYAN, 'bold' => true],
                ['text' => $command->getDescription()],
            ]);
        }

        Console::line();
        Console::note("Run 'div help <command>' or 'div <command> --help' for details.");

        return 0;
    }
}
