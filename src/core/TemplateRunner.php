<?php

declare(strict_types=1);

namespace divengine\core;

class TemplateRunner
{
    private bool $engineAvailable = false;

    public function __construct()
    {
        $this->checkEngine();
    }

    private function checkEngine(): void
    {
        $this->engineAvailable = class_exists('\divengine\div');
    }

    public function isEngineAvailable(): bool
    {
        return $this->engineAvailable;
    }

    public function render(string $templatePath, array $data = []): string
    {
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template file not found: {$templatePath}");
        }

        return $this->renderFallback($templatePath, $data);
    }

    private function renderWithEngine(string $templatePath, array $data): string
    {
        $previousLevel = error_reporting(E_ERROR | E_PARSE);
        
        $engine = new \divengine\div();
        $engine->loadTemplate($templatePath);

        foreach ($data as $key => $value) {
            $engine->setItem($key, $value);
        }

        $engine->parse();
        $result = (string) $engine;

        error_reporting($previousLevel);

        return $result;
    }

    private function renderFallback(string $templatePath, array $data): string
    {
        $content = file_get_contents($templatePath);
        
        if ($content === false) {
            throw new \RuntimeException("Could not read template: {$templatePath}");
        }

        foreach ($data as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $content = str_replace('{{' . $key . '}}', (string) $value, $content);
            }
        }

        return $content;
    }

    public function renderFromString(string $template, array $data): string
    {
        if ($this->engineAvailable) {
            $engine = new \divengine\div();
            $engine->loadTemplate($template);

            foreach ($data as $key => $value) {
                $engine->setItem($key, $value);
            }

            $engine->parse();

            return (string) $engine;
        }

        $content = $template;
        foreach ($data as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $content = str_replace('{{' . $key . '}}', (string) $value, $content);
            }
        }

        return $content;
    }
}
