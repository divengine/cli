#!/bin/bash
#
# Divengine CLI - Installer for Unix/Linux/macOS
#
# Usage:
#   ./bin/install.sh          # User installation (~/.local/bin)
#   sudo ./bin/install.sh    # System-wide installation (/usr/local/bin)
#

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

DIV_CLI_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$DIV_CLI_DIR")"
INSTALL_TARGET="${1:-$HOME/.local/bin}"

echo ""
echo -e "${YELLOW}Divengine CLI Installer${NC}"
echo ""

install_div() {
    local target="$1"
    local install_dir
    
    if [ "$target" = "/usr/local/bin" ]; then
        install_dir="$target"
        if [ "$EUID" -ne 0 ]; then
            echo -e "${RED}Error: System-wide installation requires root privileges${NC}"
            echo "Use: sudo $0"
            exit 1
        fi
    else
        install_dir="$target"
        mkdir -p "$install_dir"
    fi
    
    echo "Installing div to $install_dir..."
    
    cat > "$install_dir/div" << 'INSTALLER_EOF'
#!/bin/bash
export DIV_CLI_ROOT="__DIV_CLI_ROOT__"
exec php "$DIV_CLI_ROOT/bin/div" "$@"
INSTALLER_EOF
    
    sed -i "s|__DIV_CLI_ROOT__|$PROJECT_ROOT|g" "$install_dir/div"
    chmod +x "$install_dir/div"
    
    echo ""
    echo -e "${GREEN}Installed successfully!${NC}"
    echo ""
    
    if [ "$install_dir" = "$HOME/.local/bin" ]; then
        echo "Add to your PATH if needed:"
        echo "  export PATH=\"\$HOME/.local/bin:\$PATH\""
        echo ""
        echo "Add this line to your ~/.bashrc or ~/.zshrc to persist it."
    fi
    echo "Then run: div --help"
}

usage() {
    echo "Usage: $0 [install_path]"
    echo ""
    echo "Options:"
    echo "  (no args)        Install to ~/.local/bin"
    echo "  /usr/local/bin   Install system-wide (requires sudo)"
    echo "  --help           Show this help"
    echo ""
}

case "${1:-}" in
    --help)
        usage
        ;;
    "")
        install_div "$HOME/.local/bin"
        ;;
    *)
        install_div "$1"
        ;;
esac
