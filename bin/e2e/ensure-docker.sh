#!/usr/bin/env bash
set -euo pipefail

# 1. If Docker daemon is already responsive, use it as-is.
if docker info &>/dev/null; then
  exit 0
fi

echo "Docker daemon is offline. Attempting to start daemon..."

# 2. Check for OrbStack first (fastest daemon startup)
if command -v orb &>/dev/null; then
  echo "Starting OrbStack via 'orb start'..."
  orb start >/dev/null 2>&1 || true

  # Wait for docker API to be ready (up to 15s)
  for _ in {1..15}; do
    if docker info &>/dev/null; then
      echo "OrbStack is online and ready."
      exit 0
    fi
    sleep 1
  done
fi

# 3. Fallback to Docker Desktop if installed
if [[ -d "/Applications/Docker.app" ]]; then
  echo "Starting Docker Desktop via 'open -a Docker'..."
  open -a Docker >/dev/null 2>&1 || true

  # Wait for docker API to be ready (up to 30s)
  for _ in {1..30}; do
    if docker info &>/dev/null; then
      echo "Docker Desktop is online and ready."
      exit 0
    fi
    sleep 1
  done
fi

echo "Error: Docker daemon is offline and could not be started automatically." >&2
echo "Please start OrbStack or Docker Desktop manually." >&2
exit 1
