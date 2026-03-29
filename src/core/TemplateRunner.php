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
