#!/bin/bash
# A-Dial AMI Daemon Stop Script

DAEMON_DIR="/var/www/html/adial/ami-daemon"
PID_FILE="$DAEMON_DIR/daemon.pid"

# Check if running
if [ ! -f "$PID_FILE" ]; then
    echo "Daemon is not running (no PID file found)"
    exit 0
fi

PID=$(cat "$PID_FILE")

if ! ps -p $PID > /dev/null 2>&1; then
    echo "Daemon is not running (process $PID not found)"
    rm -f "$PID_FILE"
    exit 0
fi

# Send SIGTERM for graceful shutdown
echo "Stopping A-Dial AMI Daemon (PID: $PID)..."
kill -TERM $PID

# Wait up to 10 seconds for graceful shutdown
for i in {1..10}; do
    if ! ps -p $PID > /dev/null 2>&1; then
        echo "Daemon stopped successfully"
        rm -f "$PID_FILE"
        exit 0
    fi
    sleep 1
done

# Force kill if still running
if ps -p $PID > /dev/null 2>&1; then
    echo "Daemon did not stop gracefully, forcing kill..."
    kill -KILL $PID
    sleep 1
fi

# Verify stopped
if ps -p $PID > /dev/null 2>&1; then
    echo "Failed to stop daemon"
    exit 1
else
    echo "Daemon stopped (forced)"
    rm -f "$PID_FILE"
    exit 0
fi
