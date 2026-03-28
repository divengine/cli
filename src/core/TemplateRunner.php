<?php

declare(strict_types=1);

namespace divengine\core;

class TemplateRunner
{
    private ?object $engine = null;
    private bool $engineAvailable = false;

    public function __construct()
    {
        $this->checkEngine();
    }

    private function checkEngine(): void
    {
        if (class_exists('\divengine\div\Engine')) {
            $this->engineAvailable = true;
        }
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

        if ($this->engineAvailable) {
            return $this->renderWithEngine($templatePath, $data);
        }

        return $this->renderFallback($templatePath, $data);
    }

    private function renderWithEngine(string $templatePath, array $data): string
    {
        $engine = new \divengine\div\Engine();
        return $engine->render($templatePath, $data);
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
            $engine = new \divengine\div\Engine();
            return $engine->renderString($template, $data);
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
