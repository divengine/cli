<?php

declare(strict_types=1);

namespace divengine\commands;

use divengine\cli\Command;
use divengine\cli\Application;
use divengine\utils\Console;

class VersionCommand extends Command
{
    protected string $name = 'version';
    protected string $description = 'Show the CLI version';
    protected string $usage = 'div version';
    protected string $help = 'Displays the current version of the Divengine CLI.';

    public function __construct(private Application $app) {}

    public function run(array $args): int
    {
        Console::segments([
            ['text' => 'div ', 'color' => Console::CYAN, 'bold' => true],
            ['text' => 'v' . $this->app->getVersion(), 'color' => Console::YELLOW, 'bold' => true],
        ]);
        return 0;
    }
}
