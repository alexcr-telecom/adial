#!/bin/bash
# A-Dial AMI Daemon Start Script

DAEMON_DIR="/var/www/html/adial/ami-daemon"
PID_FILE="$DAEMON_DIR/daemon.pid"
LOG_FILE="/var/www/html/adial/logs/ami-daemon.log"

# Check if already running
if [ -f "$PID_FILE" ]; then
    PID=$(cat "$PID_FILE")
    if ps -p $PID > /dev/null 2>&1; then
        echo "Daemon is already running (PID: $PID)"
        exit 1
    else
        echo "Removing stale PID file"
        rm -f "$PID_FILE"
    fi
fi

# Start daemon
echo "Starting A-Dial AMI Daemon..."
cd "$DAEMON_DIR"
nohup php daemon.php >> "$LOG_FILE" 2>&1 &

# Wait a moment and check if it started
sleep 2

if [ -f "$PID_FILE" ]; then
    PID=$(cat "$PID_FILE")
    if ps -p $PID > /dev/null 2>&1; then
        echo "Daemon started successfully (PID: $PID)"
        exit 0
    else
        echo "Daemon failed to start - check log file: $LOG_FILE"
        exit 1
    fi
else
    echo "Daemon failed to start - PID file not created"
    exit 1
fi
