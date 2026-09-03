#!/usr/bin/env python3
"""Upload a file edited by the Cursor agent to the remote SFTP server."""

from __future__ import annotations

import json
import os
import posixpath
import sys
from datetime import datetime, timezone
from pathlib import Path

try:
    import paramiko
except ImportError:
    print("paramiko not installed; run: pip3 install --user paramiko", file=sys.stderr)
    sys.exit(1)

IGNORE_PREFIXES = (
    ".git/",
    ".vscode/",
    ".cursor/",
    "node_modules/",
)
IGNORE_NAMES = {".DS_Store", ".gitignore"}


def log(msg: str) -> None:
    ts = datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")
    print(f"[{ts}] {msg}")


def load_sftp_config(root: Path) -> dict:
    cfg_path = root / ".vscode" / "sftp.json"
    if not cfg_path.is_file():
        raise FileNotFoundError(f"Missing SFTP config: {cfg_path}")
    return json.loads(cfg_path.read_text(encoding="utf-8"))


def should_skip(rel: str) -> bool:
    name = Path(rel).name
    if name in IGNORE_NAMES:
        return True
    norm = rel.replace("\\", "/").lstrip("./")
    return any(norm == p.rstrip("/") or norm.startswith(p) for p in IGNORE_PREFIXES)


def resolve_local_path(root: Path, file_path: str) -> Path:
    p = Path(file_path)
    if p.is_absolute():
        return p
    return (root / p).resolve()


def main() -> int:
    root = Path(os.environ.get("PROJECT_ROOT", ".")).resolve()
    raw = os.environ.get("HOOK_INPUT") or sys.stdin.read()
    if not raw.strip():
        log("no hook input")
        return 0

    payload = json.loads(raw)
    file_path = payload.get("file_path") or ""
    if not file_path:
        log("missing file_path")
        return 0

    local = resolve_local_path(root, file_path)
    try:
        rel = str(local.relative_to(root)).replace("\\", "/")
    except ValueError:
        log(f"skip outside workspace: {local}")
        return 0

    if should_skip(rel):
        log(f"skip ignored: {rel}")
        return 0

    if not local.is_file():
        log(f"skip missing file: {local}")
        return 0

    cfg = load_sftp_config(root)
    remote_root = cfg["remotePath"].rstrip("/")
    remote_path = posixpath.join(remote_root, rel)
    remote_dir = posixpath.dirname(remote_path)

    host = cfg["host"]
    port = int(cfg.get("port") or 22)
    username = cfg["username"]
    password = cfg.get("password") or ""

    transport = paramiko.Transport((host, port))
    try:
        transport.connect(username=username, password=password)
        sftp = paramiko.SFTPClient.from_transport(transport)
        assert sftp is not None

        # Ensure remote directories exist
        parts = remote_dir.strip("/").split("/")
        cur = ""
        for part in parts:
            cur = f"{cur}/{part}"
            try:
                sftp.stat(cur)
            except IOError:
                try:
                    sftp.mkdir(cur)
                except IOError:
                    pass

        sftp.put(str(local), remote_path)
        sftp.close()
        log(f"uploaded {rel} -> {remote_path}")
    finally:
        transport.close()

    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except Exception as exc:  # noqa: BLE001
        log(f"ERROR: {exc}")
        raise SystemExit(1)
