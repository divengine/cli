#!/usr/bin/env php
<?php
/**
 * Build script for div.phar
 * 
 * This script packages the div CLI into a single PHAR file.
 * 
 * Requirements:
 * - PHP >= 8.1
 * - Phar extension enabled
 * - phar.readonly must be set to 0 (use -d phar.readonly=0)
 * 
 * Usage:
 *   php -d phar.readonly=0 build/build-phar.php
 */

declare(strict_types=1);

$buildDir = __DIR__;
$pharPath = $buildDir . '/div.phar';
$srcDir = dirname($buildDir);

echo "\n";
echo "========================================\n";
echo "  div CLI - PHAR Build Script\n";
echo "========================================\n";
echo "\n";

if (!extension_loaded('phar')) {
    echo "ERROR: Phar extension is not loaded.\n";
    echo "       PHP cannot create PHAR archives.\n";
    exit(1);
}

if (Phar::running() !== '') {
    echo "ERROR: Cannot build PHAR from within another PHAR.\n";
    echo "       Run this script from the project root directory.\n";
    exit(1);
}

if (file_exists($pharPath)) {
    echo "Removing existing PHAR: {$pharPath}\n";
    unlink($pharPath);
}

echo "Creating PHAR: {$pharPath}\n";
echo "\n";

try {
    $phar = new Phar($pharPath, 0, 'div.phar');
    $phar->startBuffering();

    $phar->addFile($srcDir . '/vendor/autoload.php', 'vendor/autoload.php');

    $vendorDir = $srcDir . '/vendor';
    if (is_dir($vendorDir)) {
        echo "Adding vendor files (excluding dev dependencies)...\n";
        
        $skipDirs = ['phpunit', 'phpdocumentor', 'phpspec', 'sebastian', 'theseer', 'myclabs', 'phar-io', 'nikic'];
        
        $vendorFiles = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($vendorDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        $vendorCount = 0;
        $skipped = 0;
        $lastDot = 0;
        
        foreach ($vendorFiles as $file) {
            if ($file->isDir()) {
                continue;
            }
            
            $relativePath = substr($file->getPathname(), strlen($vendorDir) + 1);
            $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
            $firstDir = $parts[0] ?? '';
            
            if (in_array($firstDir, $skipDirs)) {
                $skipped++;
                continue;
            }
            
            $phar->addFile($file->getPathname(), 'vendor/' . $relativePath);
            $vendorCount++;
            
            if ($vendorCount - $lastDot >= 100) {
                echo ".";
                $lastDot = $vendorCount;
            }
        }
        
        echo " ({$vendorCount} files, {$skipped} skipped)\n";
    } else {
        echo "Note: vendor directory not found, skipping.\n";
    }

    $srcFiles = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($srcDir . '/src', RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $srcCount = 0;
    foreach ($srcFiles as $file) {
        if ($file->isFile()) {
            $relativePath = substr($file->getPathname(), strlen($srcDir) + 1);
            $phar->addFile($file->getPathname(), $relativePath);
            $srcCount++;
        }
    }
    echo "Adding source files... ({$srcCount} files)\n";

    $pharAutoload = <<<'AUTOLOAD'
<?php
define('PHAR_ROOT', Phar::running() ?: __DIR__);

spl_autoload_register(function ($class) {
    $dirs = [
        'divengine\\cli\\' => 'src/cli/',
        'divengine\\commands\\' => 'src/commands/',
        'divengine\\core\\' => 'src/core/',
        'divengine\\utils\\' => 'src/utils/',
        'divengine\\' => 'vendor/divengine/',
    ];
    
    foreach ($dirs as $prefix => $path) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        
        $relativeClass = substr($class, $len);
        $file = PHAR_ROOT . '/' . $path . str_replace('\\', '/', $relativeClass) . '.php';
        
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    
    return false;
});

require PHAR_ROOT . '/vendor/divengine/functions/src/functions.php';
require PHAR_ROOT . '/vendor/divengine/div/src/div.php';
AUTOLOAD;

    $phar->addFromString('vendor/autoload.php', $pharAutoload);

    $stub = <<<'STUB'
#!/usr/bin/env php
<?php
Phar::mapPhar('div.phar');
require 'phar://div.phar/vendor/autoload.php';
$app = new divengine\cli\Application();
exit($app->run($argv));
__HALT_COMPILER();
STUB;

    $phar->addFromString('vendor/autoload.php', $pharAutoload);

    $phar->setStub($stub);
    $phar->stopBuffering();

    $size = filesize($pharPath);
    $sizeFormatted = number_format($size / 1024 / 1024, 2) . ' MB';

    echo "\n";
    echo "========================================\n";
    echo "  Build Complete!\n";
    echo "========================================\n";
    echo "\n";
    echo "  Output: {$pharPath}\n";
    echo "  Size:   {$sizeFormatted}\n";
    echo "\n";
    echo "  Usage:\n";
    echo "    php {$pharPath} --help\n";
    echo "\n";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    exit(1);
}
