#!/bin/bash

# A-Dial AMI Dialer - Startup Script

echo "================================"
echo "A-Dial AMI Dialer Startup"
echo "================================"
echo ""

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    echo "Running as root..."
else
    echo "Note: Some operations may require root privileges"
fi

# Check MySQL
echo "Checking MySQL..."
if systemctl is-active --quiet mariadb || systemctl is-active --quiet mysql; then
    echo "✓ MySQL is running"
else
    echo "✗ MySQL is not running. Starting..."
    systemctl start mariadb || systemctl start mysql
fi

# Check Asterisk
echo ""
echo "Checking Asterisk..."
if systemctl is-active --quiet asterisk; then
    echo "✓ Asterisk is running"
else
    echo "✗ Asterisk is not running. Starting..."
    systemctl start asterisk
    sleep 2
fi

# Check Apache/Httpd
echo ""
echo "Checking Web Server..."
if systemctl is-active --quiet httpd || systemctl is-active --quiet apache2; then
    echo "✓ Web server is running"
else
    echo "✗ Web server is not running. Starting..."
    systemctl start httpd || systemctl start apache2
fi

# Check PHP
echo ""
echo "Checking PHP..."
if command -v php &> /dev/null; then
    echo "✓ PHP is installed ($(php -v | head -n 1 | cut -d ' ' -f 2))"
else
    echo "✗ PHP is not installed"
    exit 1
fi

# Check AMI connection
echo ""
echo "Checking AMI..."
if asterisk -rx "manager show connected" &> /dev/null; then
    echo "✓ AMI is accessible"
else
    echo "✗ AMI is not accessible"
    exit 1
fi

# Start AMI Daemon
echo ""
echo "Checking AMI Daemon..."
if systemctl is-active --quiet adial-ami; then
    echo "✓ AMI daemon is already running"
    echo "  To restart: systemctl restart adial-ami"
else
    echo "✗ AMI daemon is not running. Starting..."

    # Check if daemon files exist
    if [ ! -f "/var/www/html/adial/ami-daemon/daemon.php" ]; then
        echo "✗ Daemon files not found. Run install-freepbx.sh first"
        exit 1
    fi

    # Start daemon
    systemctl start adial-ami
    sleep 2

    # Check if it's running
    if systemctl is-active --quiet adial-ami; then
        echo "✓ AMI daemon started successfully"
    else
        echo "✗ AMI daemon failed to start. Check logs:"
        echo "  systemctl status adial-ami"
        echo "  tail -f /var/www/html/adial/logs/ami-daemon.log"
        exit 1
    fi
fi

# Check dialplan
echo ""
echo "Checking Dialplan..."
if [ -f "/etc/asterisk/extensions_dialer.conf" ]; then
    echo "✓ Dialplan file exists"

    # Check if loaded in Asterisk
    if asterisk -rx "dialplan show dialer-origination" | grep -q "NoOp"; then
        echo "✓ Dialplan loaded in Asterisk"
    else
        echo "⚠ Dialplan not loaded, reloading..."
        asterisk -rx "dialplan reload" > /dev/null
        sleep 1
        echo "✓ Dialplan reloaded"
    fi
else
    echo "⚠ Dialplan not generated yet"
    echo "  Run: php /var/www/html/adial/test-dialplan-generator.php"
fi

# Check permissions
echo ""
echo "Checking directory permissions..."
chmod -R 755 /var/www/html/adial/logs 2>/dev/null
chmod -R 755 /var/www/html/adial/uploads 2>/dev/null
chown -R apache:apache /var/www/html/adial/logs 2>/dev/null
chown -R apache:apache /var/www/html/adial/uploads 2>/dev/null
chown -R asterisk:asterisk /var/lib/asterisk/sounds/dialer 2>/dev/null
chown -R asterisk:asterisk /var/spool/asterisk/monitor/adial 2>/dev/null
echo "✓ Permissions set"

# Display status summary
echo ""
echo "================================"
echo "Status Summary"
echo "================================"
echo ""
echo "Services:"
echo "  • MySQL:       $(systemctl is-active mariadb mysql 2>/dev/null | grep active | head -1 || echo 'inactive')"
echo "  • Asterisk:    $(systemctl is-active asterisk 2>/dev/null || echo 'inactive')"
echo "  • Web Server:  $(systemctl is-active httpd apache2 2>/dev/null | grep active | head -1 || echo 'inactive')"
echo "  • AMI Daemon:  $(systemctl is-active adial-ami 2>/dev/null || echo 'inactive')"
echo ""
echo "AMI Connections:"
asterisk -rx "manager show connected" | grep -E "Username|dialer" | head -5
echo ""
echo "Active Campaigns:"
CAMPAIGN_COUNT=$(mysql -u adialer_user -piCyrq0ghonj2sWzD adialer -sN -e "SELECT COUNT(*) FROM campaigns WHERE status='running'" 2>/dev/null || echo "?")
echo "  • Running: $CAMPAIGN_COUNT"
echo ""
echo "Web Interface:"
echo "  URL: http://$(hostname -I | awk '{print $1}')/adial"
echo "  or   http://localhost/adial"
echo ""
echo "Logs:"
echo "  AMI Daemon: /var/www/html/adial/logs/ami-daemon.log"
echo "              tail -f /var/www/html/adial/logs/ami-daemon.log"
echo "  Asterisk:   /var/log/asterisk/full"
echo "              asterisk -rvvv"
echo ""
echo "To manage AMI daemon:"
echo "  systemctl status adial-ami"
echo "  systemctl stop adial-ami"
echo "  systemctl restart adial-ami"
echo ""
echo "================================"
echo "System is ready!"
echo "================================"
