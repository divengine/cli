# Div CLI Documentation

> Official documentation for the Divengine CLI tool.

## Table of Contents

1. [Installation](01.%20Installation.md)
2. [Commands](02.%20Commands.md)
3. [Input Formats](03.%20Input%20Formats.md)

For template syntax, see [divengine/div documentation](https://github.com/divengine/div/tree/main/docs).

## Overview

Div CLI is a focused tool for **template generation and transformation**. It uses the divengine/div template engine for rendering.

## Quick Start

```bash
# Install
curl -sSL https://raw.githubusercontent.com/divengine/cli/main/install | php

# Use
echo '{"name": "World"}' | div render template.tpl
```

## Architecture

```
div CLI
├── render     Render templates with input data
├── transform  Transform data through templates
├── build      Build artifacts from templates
└── ...
```
