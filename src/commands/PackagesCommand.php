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
use divengine\core\ArgParser;
use divengine\core\PackageInspector;

class PackagesCommand extends Command
{
    protected string $name = 'packages';
    protected string $description = 'List packages in vendor directory';
    protected string $usage = 'div packages [vendor-path]';
    protected string $help = 'List packages detected in the vendor directory.

Inspects the vendor/ directory and displays detected packages
with their names and descriptions.

Arguments:
  [vendor-path]       Path to vendor directory (default: auto-detected)

Options:
  -h, --help          Show this help message';

    public function run(array $args): int
    {
        $parser = new ArgParser($args);
        $vendorPath = $parser->getPositional(0);

        $inspector = new PackageInspector($vendorPath);

        if ($vendorPath && !$inspector->hasVendorDir()) {
            Console::error("Vendor directory not found: {$vendorPath}");
            return 1;
        }

        $packages = $inspector->listPackages();

        if (empty($packages)) {
            Console::warning('No packages found in vendor/ directory.');
            Console::line();
            Console::note('Run "composer install" to install dependencies.');
            return 0;
        }

        Console::header('Detected Packages');
        Console::line();

        foreach ($packages as $package) {
            Console::segments([
                ['text' => '  ' . $package['name'], 'color' => Console::CYAN, 'bold' => true],
            ]);
            if ($package['description']) {
                Console::dim('     ' . $package['description']);
            }
        }

        Console::line();
        Console::note('Found ' . count($packages) . ' package(s)');

        return 0;
    }
}
