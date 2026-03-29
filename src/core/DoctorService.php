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

namespace divengine\core;

class DoctorService
{
    private array $checks = [];

    public function run(): array
    {
        $this->checks = [];

        $this->checkPhpVersion();
        $this->checkAutoload();
        $this->checkDivEngine();
        $this->checkVendorDir();
        $this->checkReadWrite();
        $this->checkWorkingDirectory();

        return $this->checks;
    }

    private function addCheck(string $name, bool $passed, string $message): void
    {
        $this->checks[] = [
            'name' => $name,
            'passed' => $passed,
            'message' => $message,
        ];
    }

    private function checkPhpVersion(): void
    {
        $version = PHP_VERSION;
        $required = '8.1.0';
        $passed = version_compare($version, $required, '>=');
        
        $this->addCheck(
            'PHP Version',
            $passed,
            $passed 
                ? "PHP {$version} (>= {$required} required)" 
                : "PHP {$version} - requires >= {$required}"
        );
    }

    private function checkAutoload(): void
    {
        $autoloadPath = self::findAutoload();
        $passed = $autoloadPath !== null;
        
        $this->addCheck(
            'Composer Autoload',
            $passed,
            $passed 
                ? "Found: " . basename(dirname($autoloadPath))
                : 'Not found - run "composer install"'
        );
    }

    private function checkDivEngine(): void
    {
        $passed = class_exists('\divengine\div');
        
        $this->addCheck(
            'divengine/div',
            $passed,
            $passed 
                ? 'divengine/div is available'
                : 'divengine/div is required but not installed'
        );
    }

    private function checkVendorDir(): void
    {
        $vendorPath = PackageInspector::findVendorPath();
        $passed = is_dir($vendorPath);
        
        $this->addCheck(
            'vendor/ directory',
            $passed,
            $passed 
                ? "Found: {$vendorPath}"
                : 'vendor/ not found in project'
        );
    }

    private function checkReadWrite(): void
    {
        $cwd = getcwd();
        $canRead = is_readable($cwd);
        $canWrite = is_writable($cwd);
        $passed = $canRead && $canWrite;
        
        $status = [];
        if ($canRead) $status[] = 'read';
        if ($canWrite) $status[] = 'write';
        
        $this->addCheck(
            'Directory Permissions',
            $passed,
            $passed 
                ? "Current dir is {$passed}: " . implode(' & ', $status)
                : "Current dir: " . ($canRead ? 'read' : 'not readable')
        );
    }

    private function checkWorkingDirectory(): void
    {
        $cwd = getcwd();
        $passed = !empty($cwd);
        
        $this->addCheck(
            'Working Directory',
            $passed,
            $passed ? "{$cwd}" : 'Could not determine'
        );
    }

    private static function findAutoload(): ?string
    {
        $searchDir = getcwd();
        
        while ($searchDir !== dirname($searchDir)) {
            $autoload = $searchDir . '/vendor/autoload.php';
            if (file_exists($autoload)) {
                return $autoload;
            }
            $searchDir = dirname($searchDir);
        }
        
        return null;
    }

    public function getSummary(): array
    {
        $total = count($this->checks);
        $passed = count(array_filter($this->checks, fn($c) => $c['passed']));
        
        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $total - $passed,
            'all_passed' => $total === $passed,
        ];
    }
}
