#!/bin/bash
#
# Build script for div.phar on Linux/macOS
#
# Usage:
#   ./build/build-phar.sh
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

echo ""
echo "========================================"
echo "  div CLI - Building PHAR"
echo "========================================"
echo ""

cd "$PROJECT_ROOT"

if [ ! -f "composer.json" ]; then
    echo "ERROR: composer.json not found. Run this from the project root."
    exit 1
fi

if [ ! -d "vendor" ]; then
    echo "Installing dependencies..."
    composer install --no-interaction
fi

echo ""
echo "Building PHAR..."
echo ""

php -d phar.readonly=0 "$SCRIPT_DIR/build-phar.php"

if [ -f "$SCRIPT_DIR/div.phar" ]; then
    chmod +x "$SCRIPT_DIR/div.phar"
    echo ""
    echo "Making executable..."
    echo ""
fi

echo "Done!"
