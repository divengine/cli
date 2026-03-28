<?php

declare(strict_types=1);

namespace divengine\core;

class TemplateRunner
{
    public function render(string $templatePath, array $data = []): string
    {
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template file not found: {$templatePath}");
        }

        $previousLevel = error_reporting(E_ERROR | E_PARSE);
        
        $engine = new \divengine\div($templatePath, $data);
        $result = (string) $engine;

        error_reporting($previousLevel);

        return $result;
    }

    public function renderFromString(string $template, array $data): string
    {
        $previousLevel = error_reporting(E_ERROR | E_PARSE);
        
        $engine = new \divengine\div($template, $data);
        $result = (string) $engine;

        error_reporting($previousLevel);

        return $result;
    }
}
