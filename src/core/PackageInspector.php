<?php

declare(strict_types=1);

namespace divengine\core;

class PackageInspector
{
    private string $vendorPath;

    public function __construct(?string $vendorPath = null)
    {
        $this->vendorPath = $vendorPath ?? self::findVendorPath();
    }

    public static function findVendorPath(): string
    {
        $searchDir = getcwd();
        
        while ($searchDir !== dirname($searchDir)) {
            $vendorPath = $searchDir . '/vendor';
            if (is_dir($vendorPath)) {
                return $vendorPath;
            }
            $searchDir = dirname($searchDir);
        }

        return getcwd() . '/vendor';
    }

    public function getVendorPath(): string
    {
        return $this->vendorPath;
    }

    public function hasVendorDir(): bool
    {
        return is_dir($this->vendorPath);
    }

    public function listPackages(): array
    {
        $packages = [];

        if (!$this->hasVendorDir()) {
            return $packages;
        }

        $vendorDir = $this->vendorPath;
        
        if (!is_dir($vendorDir)) {
            return $packages;
        }

        foreach (scandir($vendorDir) as $vendorEntry) {
            if ($vendorEntry === '.' || $vendorEntry === '..') {
                continue;
            }

            $vendorFullPath = $vendorDir . DIRECTORY_SEPARATOR . $vendorEntry;
            
            if (!is_dir($vendorFullPath)) {
                continue;
            }

            foreach (scandir($vendorFullPath) as $packageEntry) {
                if ($packageEntry === '.' || $packageEntry === '..') {
                    continue;
                }

                $packagePath = $vendorFullPath . DIRECTORY_SEPARATOR . $packageEntry;
                
                if (!is_dir($packagePath)) {
                    continue;
                }

                $composerJson = $packagePath . DIRECTORY_SEPARATOR . 'composer.json';
                
                if (file_exists($composerJson)) {
                    $info = $this->parsePackageInfo($composerJson, $vendorEntry, $packageEntry);
                    if ($info !== null) {
                        $packages[] = $info;
                    }
                }
            }
        }

        return $packages;
    }

    private function parsePackageInfo(string $composerJson, string $vendor, string $package): ?array
    {
        $content = @file_get_contents($composerJson);
        
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        
        if (!is_array($data)) {
            return [
                'name' => "{$vendor}/{$package}",
                'description' => null,
                'path' => dirname($composerJson),
            ];
        }

        return [
            'name' => $data['name'] ?? "{$vendor}/{$package}",
            'description' => $data['description'] ?? null,
            'path' => dirname($composerJson),
        ];
    }

    public function findPackage(string $name): ?array
    {
        $packages = $this->listPackages();
        
        foreach ($packages as $package) {
            if ($package['name'] === $name) {
                return $package;
            }
        }

        return null;
    }

    public function getPackageCount(): int
    {
        return count($this->listPackages());
    }
}
