#!/usr/bin/env bash
# Recree un historique Git propre (sans fichiers >100 Mo) pour GitHub.
set -euo pipefail

cd "$(dirname "$0")/.."

echo "=== Fichiers locaux >50 Mo (hors .git) ==="
find . -type f -size +50M \
  ! -path './.git/*' \
  ! -path './node_modules/*' \
  ! -path './vendor/*' \
  2>/dev/null | sed 's/^/  /' || true

echo ""
echo "=== Nouveau commit initial sans gros fichiers ==="
git checkout --orphan clean-main
git rm -rf --cached . >/dev/null 2>&1 || true
git add .
git status --short | head -40
echo "..."

if git diff --cached --name-only | grep -E 'dist-electron/|\.tools/|catalogue-produits\.pdf'; then
  echo "Erreur : des fichiers interdits sont encore indexes. Verifiez .gitignore." >&2
  exit 1
fi

git commit -m "Initial commit"
git branch -D main 2>/dev/null || true
git branch -m main

echo ""
echo "=== Push vers GitHub (SSH) ==="
git push -u origin main --force

echo ""
echo "Push termine."
