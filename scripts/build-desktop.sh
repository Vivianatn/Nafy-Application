#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

activer_nvm() {
  export NVM_DIR="${NVM_DIR:-$HOME/.nvm}"
  if [[ -s "$NVM_DIR/nvm.sh" ]]; then
    # shellcheck source=/dev/null
    . "$NVM_DIR/nvm.sh"
    if [[ -f .nvmrc ]]; then
      nvm install >/dev/null 2>&1 || true
      nvm use >/dev/null 2>&1 || true
    fi
  fi
}

nettoyer_dist_electron() {
  if [[ ! -d dist-electron ]]; then
    mkdir -p dist-electron
    return
  fi

  if rm -rf dist-electron 2>/dev/null; then
    mkdir -p dist-electron
    return
  fi

  echo "dist-electron appartient a root (build Docker). Nettoyage sudo..."
  if [[ "$EUID" -ne 0 ]]; then
    sudo rm -rf dist-electron
    sudo mkdir -p dist-electron
    sudo chown -R "$(whoami):$(whoami)" dist-electron
  else
    rm -rf dist-electron
    mkdir -p dist-electron
    chown -R "${SUDO_USER:-$USER}:${SUDO_USER:-$USER}" dist-electron
  fi
}

corriger_node_modules() {
  if [[ -d node_modules ]] && [[ ! -w node_modules ]]; then
    echo "Correction node_modules (sudo)..."
    if [[ "$EUID" -ne 0 ]]; then
      sudo chown -R "$(whoami):$(whoami)" node_modules package-lock.json 2>/dev/null || true
    fi
  fi
}

activer_nvm
corriger_node_modules
nettoyer_dist_electron

echo "=== Kamille Events Manager — build application bureau ==="
echo "Node : $(node -v 2>/dev/null || echo inconnu)"
echo ""

if ! command -v npm >/dev/null 2>&1; then
  echo "Erreur : npm est requis."
  echo "Installez Node 22 : curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh | bash"
  exit 1
fi

NODE_MAJOR="$(node -v | sed 's/v//' | cut -d. -f1)"
if [[ "$NODE_MAJOR" -lt 20 ]]; then
  echo "Avertissement : Node $(node -v) detecte. Node 20+ recommande."
  echo "Dans WSL : nvm install 22 && nvm use 22"
fi

if [[ ! -d node_modules/electron ]]; then
  echo "Installation des dependances npm…"
  npm install
fi

CIBLE="${1:-win}"

case "$CIBLE" in
  win)
    echo "Build Windows (win-unpacked, sans Wine)…"
    npm run electron:build:win:dir
    echo ""
    echo "Application prete :"
    echo "  dist-electron/win-unpacked/Kamille Events Manager.exe"
    echo ""
    echo "Lancez depuis PowerShell Windows :"
    echo "  .\\scripts\\build-desktop.ps1 -RunOnly"
    echo "Ou depuis WSL :"
    echo "  bash scripts/run-desktop.sh"
    ;;
  linux)
    echo "Construction AppImage Linux…"
    npm run electron:build:linux
    echo ""
    echo "Binaire : dist-electron/Kamille Events Manager *.AppImage"
    ;;
  *)
    echo "Usage : bash scripts/build-desktop.sh [win|linux]"
    exit 1
    ;;
esac

echo ""
echo "Avant de lancer l'application :"
echo "  1. Docker buts4 demarre (cd /buts4 && docker compose up -d)"
echo "  2. http://127.0.0.1:8319 accessible dans le navigateur"
