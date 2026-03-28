#!/bin/bash
#
# Test script for div.phar
#
# Usage:
#   ./build/test-phar.sh [command]
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHAR_PATH="$SCRIPT_DIR/div.phar"

echo ""
echo "Testing div.phar..."
echo ""

if [ ! -f "$PHAR_PATH" ]; then
    echo "ERROR: div.phar not found at: $PHAR_PATH"
    echo ""
    echo "Run ./build/build-phar.sh first to build it."
    exit 1
fi

COMMAND="${1:-version}"

echo "Running: div.phar $COMMAND"
echo ""

php "$PHAR_PATH" $COMMAND

echo ""
echo "Test complete!"
