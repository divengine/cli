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

class ArgParser
{
    public string $command = '';
    public array $flags = [];
    public array $positional = [];
    private array $options = [];

    public function __construct(array $args)
    {
        $this->parse($args);
    }

    private function parse(array $args): void
    {
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--')) {
                $this->parseOption($arg);
            } elseif (str_starts_with($arg, '-')) {
                $this->flags[] = substr($arg, 1);
            } else {
                $this->positional[] = $arg;
            }
        }
    }

    private function parseOption(string $arg): void
    {
        $option = substr($arg, 2);

        if (str_contains($option, '=')) {
            [$key, $value] = explode('=', $option, 2);
            $this->options[$key] = $value;
        } else {
            $this->options[$option] = true;
        }
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    public function hasOption(string $key): bool
    {
        return isset($this->options[$key]);
    }

    public function hasFlag(string $flag): bool
    {
        return in_array($flag, $this->flags, true);
    }

    public function getPositional(int $index, mixed $default = null): mixed
    {
        return $this->positional[$index] ?? $default;
    }

    public function getPositionalAll(): array
    {
        return $this->positional;
    }

    public function getOptionOrArg(string $optionKey, int $positionalIndex, mixed $default = null): mixed
    {
        if ($this->hasOption($optionKey)) {
            return $this->getOption($optionKey);
        }
        return $this->getPositional($positionalIndex, $default);
    }
}
