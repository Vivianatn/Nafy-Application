#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

UTILISATEUR="${SUDO_USER:-$USER}"

if [[ "$EUID" -ne 0 ]]; then
  echo "Ce script corrige des fichiers crees par root (Docker)."
  echo "Relancez avec sudo :"
  echo "  sudo bash scripts/fix-host-permissions.sh"
  exit 1
fi

echo "Correction des droits pour : $UTILISATEUR"
echo "Dossier : $ROOT"
echo ""

rm -rf "$ROOT/dist-electron"
mkdir -p "$ROOT/dist-electron"

chown -R "$UTILISATEUR:$UTILISATEUR" \
  "$ROOT/node_modules" \
  "$ROOT/dist-electron" \
  "$ROOT/package-lock.json" \
  2>/dev/null || true

echo "Termine. Relancez :"
echo "  bash scripts/build-desktop.sh win"
echo "  ou .\\scripts\\build-desktop.ps1"
