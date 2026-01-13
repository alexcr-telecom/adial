# A-Dial Quick Start Guide

## Installation (5 minutes)

```bash
cd /var/www/html/adial
chmod +x install-freepbx.sh
./install-freepbx.sh
```

Save the credentials shown at the end!

## First Campaign (10 minutes)

### 1. Create IVR Menu

Navigate to: **IVR Menus** → **Add New**

- Name: "Main Menu"
- Audio File: Upload your greeting (WAV/MP3)
- Timeout: 10 seconds
- Actions:
  - DTMF 1 → Transfer to Extension 100 (PJSIP)
  - DTMF 2 → Transfer to Queue "support"
  - DTMF 9 → Hangup

Click **Save**

### 2. Import Phone Numbers

Create CSV file (numbers.csv):
```csv
phone_number,name
15551234567,John Doe
15559876543,Jane Smith
```

### 3. Create Campaign

Navigate to: **Campaigns** → **Add New**

- Name: "Test Campaign"
- Description: "First test"
- Trunk Type: PJSIP (or SIP)
- Trunk Value: your-trunk-name
- Caller ID: 15555551234
- Agent Destination: IVR
- Agent Value: Main Menu (select from dropdown)
- Concurrent Calls: 2
- Click **Save**

### 4. Import Numbers

On campaign page:
- Click **Import Numbers**
- Upload numbers.csv
- Click **Import**

### 5. Start Campaign

- Click **Start Campaign**
- Monitor in real-time

## Monitoring

### Web Interface
- **Campaigns** → **View** → See active calls, statistics
- **Call Logs** → View CDR records
- **Recordings** → Listen to call recordings

### Command Line
```bash
# Check daemon status
systemctl status adial-ami

# View logs
tail -f /var/www/html/adial/logs/ami-daemon.log

# Check active channels
asterisk -rx "core show channels"

# View dialplan
asterisk -rx "dialplan show dialer-origination"
```

## Common Tasks

### Stop All Campaigns
```bash
# Via web interface
Campaigns → Stop Campaign

# Via command line
systemctl restart adial-ami
```

### View Campaign CDRs
```sql
mysql -u freepbxuser -p asteriskcdrdb
SELECT * FROM cdr WHERE accountcode = 'CAMPAIGN_ID' ORDER BY calldate DESC;
```

### Reset Numbers for Retry
```sql
mysql -u adialer_user -p adialer
UPDATE campaign_numbers
SET status='pending', attempts=0
WHERE campaign_id=CAMPAIGN_ID;
```

### Test Trunk
```bash
# PJSIP
asterisk -rx "pjsip show endpoints"

# SIP
asterisk -rx "sip show peers"
```

## Troubleshooting

### No Calls Originating?

1. Check campaign status: Should show "running"
2. Verify numbers are "pending"
3. Check concurrent calls limit
4. Review daemon logs:
   ```bash
   tail -f /var/www/html/adial/logs/ami-daemon.log
   ```

### IVR Not Working?

1. Verify audio file uploaded:
   ```bash
   ls -lh /var/lib/asterisk/sounds/dialer/
   ```

2. Check dialplan generated:
   ```bash
   cat /etc/asterisk/extensions_dialer.conf | grep "ivr-menu"
   ```

3. Test dialplan:
   ```bash
   asterisk -rx "dialplan show ivr-menu-1"
   ```

### Calls Connect But No Audio?

1. Check codec compatibility
2. Verify NAT settings
3. Test with direct extension first

## Tips

1. **Start Small**: Test with 1-2 concurrent calls first
2. **Monitor Closely**: Watch logs during first campaigns
3. **Test IVR**: Call in manually to test IVR before campaigns
4. **Recording Space**: Monitor disk space in /var/spool/asterisk/monitor/adial/
5. **Database Backups**: Regular backups of adialer database

## Getting Help

Check logs in this order:
1. `/var/www/html/adial/logs/ami-daemon.log` - Daemon issues
2. `/var/log/asterisk/full` - Asterisk issues
3. `/var/log/httpd/error_log` - Web interface issues

Use Asterisk CLI for debugging:
```bash
asterisk -rvvv
```

## Next Steps

- Configure multiple IVR menus for different campaigns
- Set up call recording analysis
- Create reports from CDR data
- Optimize concurrent call settings
- Implement custom dispositions
