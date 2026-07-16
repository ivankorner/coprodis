#!/bin/bash
# Wrapper to run E2E tests with test database configuration
# Switches .env to .env.test, runs tests, then restores original .env

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR/../.."

ENV_FILE="$PROJECT_DIR/.env"
ENV_TEST="$PROJECT_DIR/.env.test"
ENV_BAK="$PROJECT_DIR/.env.bak"
ENV_RESTORE=false

cleanup() {
  if [ "$ENV_RESTORE" = true ]; then
    echo "Restoring .env..."
    if [ -f "$ENV_BAK" ]; then
      cp "$ENV_BAK" "$ENV_FILE"
      rm -f "$ENV_BAK"
    fi
    echo "Done."
  fi
}
trap cleanup EXIT

# Backup current .env and use test env
if [ -f "$ENV_FILE" ]; then
  echo "Backing up .env -> .env.bak"
  cp "$ENV_FILE" "$ENV_BAK"
fi

echo "Switching to .env.test"
cp "$ENV_TEST" "$ENV_FILE"
ENV_RESTORE=true

# Run with args passed to script, default to "npx playwright test"
if [ $# -eq 0 ]; then
  bash "$SCRIPT_DIR/fixtures/reset-db.sh"
  npx playwright test
else
  "$@"
fi
