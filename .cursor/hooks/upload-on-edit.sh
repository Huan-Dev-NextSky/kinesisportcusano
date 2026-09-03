#!/bin/bash
# Auto-upload files edited by the Cursor agent to the remote SFTP server.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
LOG_DIR="$ROOT/.cursor/hooks"
LOG_FILE="$LOG_DIR/upload.log"

input="$(cat)"
export HOOK_INPUT="$input"
export PROJECT_ROOT="$ROOT"

/usr/bin/python3 "$ROOT/.cursor/hooks/upload_sftp.py" >>"$LOG_FILE" 2>&1 || {
  echo "[upload-on-edit] upload failed — see $LOG_FILE" >&2
  exit 0
}

exit 0
