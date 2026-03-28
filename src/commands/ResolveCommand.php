<?php

declare(strict_types=1);

namespace divengine\commands;

use divengine\cli\Command;
use divengine\utils\Console;
use divengine\core\ArgParser;
use divengine\core\Filesystem;

class ResolveCommand extends Command
{
    protected string $name = 'resolve';
    protected string $description = 'Resolve a template identifier to a physical path';
    protected string $usage = 'div resolve <template>';
    protected string $help = 'Resolve a template identifier or path to its physical location.

This command normalizes and resolves template paths. In future versions,
it will support logical package references like "org/package/template".

Arguments:
  <template>          Template name or path to resolve

Options:
  -h, --help          Show this help message';

    public function run(array $args): int
    {
        $parser = new ArgParser($args);
        $template = $parser->getPositional(0);

        if (empty($template)) {
            Console::error('Template identifier is required.');
            Console::note('Run "div resolve --help" for usage information.');
            return 1;
        }

        $absolutePath = Filesystem::absolutePath($template);

        if (Filesystem::exists($absolutePath)) {
            Console::segments([
                ['text' => '[resolve] ', 'color' => Console::CYAN, 'bold' => true],
                ['text' => $template, 'color' => Console::YELLOW],
                ['text' => ' -> ', 'color' => Console::GRAY],
                ['text' => $absolutePath, 'color' => Console::GREEN],
            ]);
            return 0;
        }

        if (Filesystem::exists($template)) {
            Console::line(Filesystem::absolutePath($template));
            return 0;
        }

        Console::error("Template not found: {$template}");
        return 1;
    }
}
