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
