#!/bin/bash
# Session start hook for Sacci Brand Hub
# Runs at the start of every Claude Code web session.
# Sets up:
#   1. Composer dependencies (PHP)
#   2. SSH key + config for SiteGround (sacci-sg alias)
set -euo pipefail

# Only run in remote (Claude Code on the web) environments
if [ "${CLAUDE_CODE_REMOTE:-}" != "true" ]; then
  exit 0
fi

# ── 1. PHP / Composer ────────────────────────────────────────────────────────
APP_DIR="${CLAUDE_PROJECT_DIR}/sacci_brand_hub"
if [ -f "${APP_DIR}/composer.json" ]; then
  echo "[session-start] Installing Composer dependencies..."
  cd "${APP_DIR}"
  composer install --no-interaction --prefer-dist --no-progress 2>&1
  echo "[session-start] Composer done."
fi

# ── 2. SSH key for SiteGround ────────────────────────────────────────────────
if [ -n "${SACCI_SSH_PRIVATE_KEY:-}" ]; then
  echo "[session-start] Setting up SiteGround SSH key..."

  mkdir -p ~/.ssh
  chmod 700 ~/.ssh

  # Write the private key
  printf '%s\n' "${SACCI_SSH_PRIVATE_KEY}" > ~/.ssh/sacci_siteground_rsa
  chmod 600 ~/.ssh/sacci_siteground_rsa

  # Write SSH client config (append only if the block isn't already there)
  if ! grep -q "Host sacci-sg" ~/.ssh/config 2>/dev/null; then
    cat >> ~/.ssh/config << 'SSH_CONFIG'

Host sacci-sg
  HostName ssh.sacci.space
  User u2520-3v1nc5i4btry
  Port 18765
  IdentityFile ~/.ssh/sacci_siteground_rsa
  StrictHostKeyChecking accept-new
SSH_CONFIG
    chmod 600 ~/.ssh/config
  fi

  echo "[session-start] SSH key configured. Test with: ssh sacci-sg"
else
  echo "[session-start] WARNING: SACCI_SSH_PRIVATE_KEY not set — SSH to SiteGround will not work."
  echo "               Add the private key as a project secret named SACCI_SSH_PRIVATE_KEY."
fi
