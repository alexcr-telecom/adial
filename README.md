# A-Dial - AMI Predictive Dialer for FreePBX

A powerful, open-source predictive dialer for FreePBX/Asterisk using AMI (Asterisk Manager Interface) with dialplan-based IVR routing.

## Features

- **Campaign Management**: Create and manage multiple concurrent campaigns
- **IVR Support**: Interactive voice response menus with DTMF routing
- **Queue Integration**: Transfer calls to FreePBX queues
- **Extension Transfer**: Direct transfer to SIP/PJSIP extensions
- **Call Recording**: Automatic recording of all calls
- **Concurrent Call Control**: Limit simultaneous calls per campaign
- **Retry Logic**: Automatic retry for failed/unanswered calls
- **CDR Integration**: Native Asterisk CDR with campaign filtering
- **Real-time Monitoring**: Live campaign status and call tracking
- **Web Interface**: Easy-to-use web-based management

## Architecture

### AMI-Based Design
- **PHP AMI Daemon**: Manages campaign processing and call origination
- **Dialplan Generation**: Auto-generates Asterisk dialplan from database
- **Event-Driven**: Uses AMI events for real-time call tracking
- **Native CDR**: Integrates with Asterisk's built-in CDR system

### Technology Stack
- PHP 7.4+
- MySQL/MariaDB 5.7+
- Asterisk 13+ with AMI
- FreePBX 14+
- CodeIgniter 3.x

## Requirements

### System Requirements
- CentOS 7/8 or Rocky Linux 8
- FreePBX 14+ installed and configured
- Asterisk 13+ (included with FreePBX)
- MySQL/MariaDB 5.7+
- PHP 7.4+ with extensions: mysqli, pdo, mbstring, json
- 2GB RAM minimum
- 20GB disk space

### FreePBX Requirements
- Active SIP/PJSIP trunks configured
- AMI access enabled
- Extensions/Queues configured (for IVR routing)

## Installation

### Automatic Installation (Recommended)

```bash
# Navigate to installation directory
cd /var/www/html/adial

# Make installer executable
chmod +x install-freepbx.sh

# Run installer as root
./install-freepbx.sh
```

The installer will:
1. Check system requirements
2. Create database and user
3. Configure AMI access
4. Set up dialplan includes
5. Create directories and set permissions
6. Generate configuration files
7. Install systemd service
8. Start the AMI daemon

### Manual Installation

If you prefer manual installation, see [MANUAL_INSTALL.md](MANUAL_INSTALL.md)

## Quick Start

### 1. Access Web Interface

After installation, access the web interface:

```
http://your-server-ip/adial
```

Default credentials (if configured):
- Username: admin
- Password: (set during installation)

### 2. Configure Trunk

1. Go to **Settings** → **Trunks**
2. Add your FreePBX trunk details:
   - Trunk Type: SIP or PJSIP
   - Trunk Name: (your FreePBX trunk name, e.g., "trunk1")

### 3. Create IVR Menu (Optional)

1. Go to **IVR Menus** → **Add New**
2. Upload audio file (WAV or MP3)
3. Configure DTMF actions:
   - Press 1: Transfer to Extension 100
   - Press 2: Transfer to Queue "support"
   - Press 9: Hangup

### 4. Create Campaign

1. Go to **Campaigns** → **Add New**
2. Configure campaign:
   - Name: "Test Campaign"
   - Trunk: Select configured trunk
   - Caller ID: Your outbound caller ID
   - Agent Destination: IVR Menu or Queue
   - Concurrent Calls: 5
3. Import phone numbers (CSV format)
4. Start campaign

### 5. Monitor Campaign

1. Go to **Campaigns** → **View**
2. Monitor real-time statistics:
   - Active calls
   - Answered/Failed/Pending
   - Call recordings

## Configuration

### Campaign Settings

- **Concurrent Calls**: Maximum simultaneous calls
- **Retry Times**: Number of retry attempts
- **Retry Delay**: Seconds between retries
- **Dial Timeout**: Maximum ring time

### IVR Actions

- **Extension**: Transfer to SIP/PJSIP extension
- **Queue**: Transfer to FreePBX queue
- **Goto IVR**: Jump to another IVR menu
- **Hangup**: End call

### CDR Filtering

Query dialer calls in Asterisk CDR:

```sql
-- Get all calls for campaign 12
SELECT * FROM asteriskcdrdb.cdr
WHERE accountcode = '12'
ORDER BY calldate DESC;

-- Campaign statistics
SELECT
    accountcode as campaign_id,
    COUNT(*) as total_calls,
    SUM(CASE WHEN disposition='ANSWERED' THEN 1 ELSE 0 END) as answered,
    AVG(duration) as avg_duration
FROM asteriskcdrdb.cdr
WHERE accountcode != ''
GROUP BY accountcode;
```

## Management

### Daemon Control

```bash
# Start daemon
systemctl start adial-ami

# Stop daemon
systemctl stop adial-ami

# Restart daemon
systemctl restart adial-ami

# Check status
systemctl status adial-ami

# View logs
tail -f /var/www/html/adial/logs/ami-daemon.log
```

### Dialplan Management

Dialplan is auto-generated when IVR menus are created/updated/deleted.

Manual regeneration:
```bash
cd /var/www/html/adial
php test-dialplan-generator.php
```

View generated dialplan:
```bash
cat /etc/asterisk/extensions_dialer.conf
```

Validate dialplan:
```bash
asterisk -rx "dialplan show dialer-origination"
asterisk -rx "dialplan show ivr-menu-1"
```

### Database Backup

```bash
# Backup database
mysqldump -u adialer_user -p adialer > backup.sql

# Restore database
mysql -u adialer_user -p adialer < backup.sql
```

## Troubleshooting

### Daemon Won't Start

Check logs:
```bash
tail -f /var/www/html/adial/logs/ami-daemon.log
```

Verify AMI connection:
```bash
asterisk -rx "manager show connected"
```

### Calls Not Originating

1. Check campaign status: Should be "running"
2. Verify pending numbers exist
3. Check concurrent call limits
4. Review daemon logs for errors
5. Test trunk: `asterisk -rx "pjsip show endpoints"` or `sip show peers`

### IVR Not Playing

1. Check audio file exists: `ls /var/lib/asterisk/sounds/dialer/`
2. Verify dialplan generated: `cat /etc/asterisk/extensions_dialer.conf`
3. Test dialplan: `asterisk -rx "dialplan show ivr-menu-X"`
4. Check audio format: Should be 8kHz, mono WAV

### CDR Not Recording

1. Verify Asterisk CDR enabled: `asterisk -rx "cdr show status"`
2. Check ODBC connection: `asterisk -rx "odbc show asteriskcdrdb"`
3. Review accountcode setting in dialplan

### Permissions Issues

```bash
# Fix file permissions
chown -R apache:apache /var/www/html/adial
chown -R asterisk:asterisk /var/www/html/adial/ami-daemon
chown -R asterisk:asterisk /var/lib/asterisk/sounds/dialer
chown -R asterisk:asterisk /var/spool/asterisk/monitor/adial
```

## File Structure

```
/var/www/html/adial/
├── ami-daemon/              # AMI daemon
│   ├── daemon.php           # Main daemon process
│   ├── AmiClient.php        # AMI client library
│   ├── Logger.php           # Logging utility
│   ├── config.php           # Configuration
│   ├── start-daemon.sh      # Start script
│   └── stop-daemon.sh       # Stop script
├── application/             # CodeIgniter application
│   ├── controllers/         # Web controllers
│   ├── models/              # Database models
│   ├── views/               # Web views
│   ├── libraries/           # Custom libraries
│   │   └── Dialplan_generator.php
│   └── config/              # Configuration files
├── logs/                    # Application logs
├── uploads/                 # Uploaded files
└── start-dialer.sh          # Main start script
└── stop-dialer.sh           # Main stop script

/etc/asterisk/
├── extensions_dialer.conf   # Auto-generated dialplan
├── extensions_custom.conf   # Dialplan include
└── manager_custom.conf      # AMI user

/var/lib/asterisk/sounds/dialer/  # IVR audio files
/var/spool/asterisk/monitor/adial/ # Call recordings
```

## Upgrading

1. Stop daemon: `systemctl stop adial-ami`
2. Backup database and files
3. Pull latest changes
4. Run migrations (if any)
5. Regenerate dialplan: `php test-dialplan-generator.php`
6. Start daemon: `systemctl start adial-ami`

## Security Considerations

1. **Database**: Use strong passwords, restrict access to localhost
2. **AMI**: Use strong password, restrict to 127.0.0.1
3. **Web Interface**: Use HTTPS, implement authentication
4. **File Permissions**: Restrict access to configuration files
5. **Firewall**: Block AMI port (5038) from external access
6. **SELinux**: Keep enabled with proper contexts

## Performance Tuning

### High Call Volume

1. Increase concurrent calls per campaign
2. Optimize MySQL queries with indexes
3. Increase PHP memory limit
4. Use SSD for recordings
5. Monitor system resources

### Database Optimization

```sql
-- Add indexes for performance
ALTER TABLE campaign_numbers ADD INDEX idx_status (campaign_id, status);
ALTER TABLE cdr ADD INDEX idx_campaign (campaign_id, calldate);
```

## Support

### Log Files
- AMI Daemon: `/var/www/html/adial/logs/ami-daemon.log`
- Asterisk: `/var/log/asterisk/full`
- PHP: `/var/log/httpd/error_log`

### Asterisk CLI
```bash
asterisk -rvvv
```

Useful commands:
- `manager show connected` - Show AMI connections
- `dialplan show dialer-origination` - Show dialplan
- `channel show all` - Show active channels
- `cdr show status` - Show CDR status

## License

This project is open source. See LICENSE file for details.

## Credits

Developed for FreePBX/Asterisk systems using AMI and dialplan-based routing.

## Changelog

### Version 2.0.0 (AMI)
- Complete migration from ARI to AMI
- Dialplan-based IVR routing
- Native Asterisk CDR integration
- Account code filtering
- Improved stability and performance
