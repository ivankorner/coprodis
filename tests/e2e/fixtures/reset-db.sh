#!/bin/bash
# Resets coprodis_test database

# Try common XAMPP paths first, fall back to PATH
if [ -x /Applications/XAMPP/xamppfiles/bin/mysql ]; then
  MYSQL="/Applications/XAMPP/xamppfiles/bin/mysql"
elif command -v mysql &>/dev/null; then
  MYSQL="mysql"
else
  echo "ERROR: mysql not found. Install it or set MYSQL env var."
  exit 1
fi

MYSQL_USER="${MYSQL_USER:-root}"
MYSQL_PASS="${MYSQL_PASS:-}"
MYSQL_HOST="${MYSQL_HOST:-127.0.0.1}"
MYSQL_PORT="${MYSQL_PORT:-3306}"

MYSQL_CMD="$MYSQL -u $MYSQL_USER -h $MYSQL_HOST -P $MYSQL_PORT"
if [ -n "$MYSQL_PASS" ]; then
  MYSQL_CMD="$MYSQL_CMD -p$MYSQL_PASS"
fi

echo "Dropping test database..."
$MYSQL_CMD -e "DROP DATABASE IF EXISTS coprodis_test"

echo "Creating test database..."
$MYSQL_CMD -e "CREATE DATABASE coprodis_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

echo "Applying migrations..."
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR/../../.."
for f in "$PROJECT_DIR"/migrations/*.sql; do
  echo "  Running $f..."
  # Strip CREATE DATABASE through USE block (references production DB name)
  sed '/^CREATE DATABASE/,/^USE /d' "$f" | $MYSQL_CMD coprodis_test
done

echo "Applying seed data..."
$MYSQL_CMD coprodis_test < "$SCRIPT_DIR/seed.sql"

echo "Done."
