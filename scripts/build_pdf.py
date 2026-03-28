#!/usr/bin/env python3
"""
Build PDF documentation from Markdown files.

Usage:
    python scripts/build_pdf.py --output div-documentation.pdf

Requirements:
    - pandoc
    - texlive (xetex, latex-recommended, latex-extra, fonts-recommended)
"""

import argparse
import subprocess
import sys
from pathlib import Path


def find_markdown_files(docs_dir: Path) -> list[Path]:
    """Find all markdown files in docs directory, sorted."""
    md_files = sorted(docs_dir.glob("*.md"))
    return [f for f in md_files if f.name != "README.md"]


def build_pdf(markdown_files: list[Path], output: Path, title: str = "Div CLI Documentation") -> None:
    """Build PDF from markdown files using pandoc."""
    
    cmd = [
        "pandoc",
        "--pdf-engine=xelatex",
        "-f", "markdown",
        "-o", str(output),
        "--standalone",
        f"--title={title}",
        "--toc",
        "--toc-depth=2",
        "-V", "geometry:margin=1in",
        "-V", "fontsize=11pt",
        "-V", "linestretch=1.2",
    ]
    
    cmd.extend([str(f) for f in markdown_files])
    
    print(f"Building PDF: {output}")
    print(f"Source files: {len(markdown_files)}")
    
    result = subprocess.run(cmd, capture_output=True, text=True)
    
    if result.returncode != 0:
        print(f"Error: {result.stderr}", file=sys.stderr)
        sys.exit(1)
    
    print(f"PDF created: {output} ({output.stat().st_size / 1024:.1f} KB)")


def main():
    parser = argparse.ArgumentParser(description="Build PDF from Markdown documentation")
    parser.add_argument("--output", "-o", required=True, help="Output PDF file")
    parser.add_argument("--title", "-t", default="Div CLI Documentation", help="Document title")
    parser.add_argument("--docs", "-d", default="docs", help="Documentation directory")
    
    args = parser.parse_args()
    
    docs_dir = Path(args.docs)
    if not docs_dir.exists():
        print(f"Error: Documentation directory not found: {docs_dir}", file=sys.stderr)
        sys.exit(1)
    
    markdown_files = find_markdown_files(docs_dir)
    
    if not markdown_files:
        print(f"Error: No markdown files found in {docs_dir}", file=sys.stderr)
        sys.exit(1)
    
    output = Path(args.output)
    
    build_pdf(markdown_files, output, args.title)


if __name__ == "__main__":
    main()
