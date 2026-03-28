<?php

declare(strict_types=1);

namespace divengine\commands;

use divengine\cli\Command;
use divengine\utils\Console;
use divengine\core\DoctorService;

class DoctorCommand extends Command
{
    protected string $name = 'doctor';
    protected string $description = 'Run diagnostics on the CLI environment';
    protected string $usage = 'div doctor';
    protected string $help = 'Check the CLI environment and report any issues.

Verifies:
  - PHP version compatibility
  - Composer autoload availability
  - divengine/div package presence
  - vendor/ directory existence
  - Directory read/write permissions
  - Working directory validity

Options:
  -h, --help          Show this help message';

    public function run(array $args): int
    {
        $service = new DoctorService();
        $checks = $service->run();
        $summary = $service->getSummary();

        Console::header('Divengine CLI Diagnostics');
        Console::line();

        foreach ($checks as $check) {
            $status = $check['passed'] ? 'OK' : 'FAIL';
            $color = $check['passed'] ? Console::GREEN : Console::RED;
            
            Console::segments([
                ['text' => '  [', 'color' => Console::GRAY],
                ['text' => str_pad($status, 5), 'color' => $color, 'bold' => true],
                ['text' => '] ', 'color' => Console::GRAY],
                ['text' => $check['name'], 'color' => Console::CYAN, 'bold' => true],
                ['text' => ' - ' . $check['message']],
            ]);
        }

        Console::line();
        Console::segments([
            ['text' => 'Summary: ', 'color' => Console::CYAN, 'bold' => true],
            ['text' => $summary['passed'] . '/' . $summary['total'] . ' checks passed'],
        ]);

        if ($summary['all_passed']) {
            Console::line();
            Console::success('All checks passed!');
            return 0;
        }

        Console::line();
        Console::warning('Some checks failed. Review the output above.');
        return 1;
    }
}
