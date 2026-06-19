#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

EXE_NAME="Kamille Events Manager.exe"
SOURCE="$ROOT/dist-electron/win-unpacked"
LOCAL_WIN="${LOCALAPPDATA_WIN:-/mnt/c/Users/$USER/AppData/Local/Kamille Events Manager}"

if [[ ! -f "$SOURCE/$EXE_NAME" ]]; then
  echo "Executable introuvable : $SOURCE/$EXE_NAME"
  echo "Lancez d'abord : bash scripts/build-desktop.sh win"
  exit 1
fi

# Utilisateur Windows depuis WSL (vivian si connecte en root via sudo)
WIN_USER="${WIN_USER:-${SUDO_USER:-$USER}}"
if [[ "$WIN_USER" == "root" ]]; then
  WIN_USER="vivian"
fi
LOCAL_WIN="/mnt/c/Users/$WIN_USER/AppData/Local/Kamille Events Manager"

echo "Copie vers Windows : $LOCAL_WIN"
rm -rf "$LOCAL_WIN"
mkdir -p "$LOCAL_WIN"
cp -a "$SOURCE/." "$LOCAL_WIN/"

WIN_EXE="C:\\Users\\$WIN_USER\\AppData\\Local\\Kamille Events Manager\\$EXE_NAME"
echo "Lancement : $WIN_EXE"

# Reinitialiser config Electron (ancienne URL 127.0.0.1 incorrecte)
CONFIG_WIN="/mnt/c/Users/$WIN_USER/AppData/Roaming/Kamille Events Manager/config.json"
rm -f "$CONFIG_WIN" 2>/dev/null || true

if command -v powershell.exe >/dev/null 2>&1; then
  powershell.exe -NoProfile -Command "Start-Process -FilePath '$WIN_EXE'"
elif command -v cmd.exe >/dev/null 2>&1; then
  cmd.exe /c start "" "$WIN_EXE"
else
  echo "Lancez manuellement depuis l'Explorateur Windows :"
  echo "  $LOCAL_WIN/$EXE_NAME"
fi
