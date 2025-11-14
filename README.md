# Asterisk ARI Dialer

A comprehensive Asterisk ARI-based auto-dialer system with web interface, real-time monitoring, IVR support, and call recording.

## Features

- **Web Interface** (PHP/MySQL/CodeIgniter)
  - Dashboard with system status and statistics
  - Campaign management (Create, Edit, Delete, Start, Stop, Pause)
  - Call Detail Records (CDR) with filtering and export
  - Real-time monitoring of active campaigns and channels
  - IVR menu management with DTMF actions
  - Audio file upload with automatic conversion

- **Stasis Application** (Node.js)
  - WebSocket connection to Asterisk ARI
  - Campaign processing and call origination
  - Automatic call bridging to agents
  - IVR menu handling with DTMF detection
  - Dual-channel recording with stereo mixing
  - Real-time call tracking and CDR updates

- **Call Recording**
  - Channel snooping for recording both legs
  - Automatic mixing into stereo MP3 files
  - Playback and download from web interface

## System Requirements

- Asterisk 16+ with ARI enabled
- PHP 7.4+
- MySQL 5.7+
- Node.js 18+
- FFmpeg or SoX (for audio conversion)

## Installation

### 1. Database Setup

The database has already been created. Verify it exists:

```bash
mysql -u root -pmahapharata -e "SHOW DATABASES;"
```

### 2. Asterisk Configuration

Create ARI user in `/etc/asterisk/ari.conf`:

```ini
[dialer]
type=user
password=76e6d233237c5323b9bb71860e322b61
read_only=no
```

Reload Asterisk:

```bash
asterisk -rx "module reload res_ari.so"
```

### 3. Start the Stasis Application

```bash
cd /var/www/html/adial/stasis-app
npm install  # Already done
node app.js
```

Or use PM2 for production:

```bash
npm install -g pm2
pm2 start app.js --name "ari-dialer"
pm2 save
pm2 startup
```

### 4. Configure Web Server

For Apache, the `.htaccess` file is already configured. Ensure `mod_rewrite` is enabled:

```bash
a2enmod rewrite
systemctl restart httpd
```

### 5. Set Permissions

```bash
chmod -R 777 /var/www/html/adial/uploads
chmod -R 777 /var/www/html/adial/logs
chmod -R 777 /var/www/html/adial/recordings
chmod -R 777 /var/lib/asterisk/sounds/dialer
```

## Usage

### Accessing the Web Interface

Open your browser and navigate to:
```
http://your-server-ip/adial
```

### Creating a Campaign

1. Go to **Campaigns** → **New Campaign**
2. Fill in campaign details:
   - **Name**: Campaign identifier
   - **Description**: Optional description
   - **Trunk Configuration**:
     - Custom: `Local/${EXTEN}@from-internal`
     - PJSIP: Select from available trunks
     - SIP: Select from available trunks
   - **Caller ID**: Outbound caller ID
   - **Agent Destination**:
     - Custom: `PJSIP/100` or `Local/100@from-internal`
     - Extension: Select from endpoints
     - IVR: Configure IVR menu separately
   - **Recording**: Enable to record both channels
   - **Concurrent Calls**: Max simultaneous calls
   - **Retry Settings**: Configure retry attempts and delay

3. Click **Create Campaign**

### Adding Numbers to Campaign

1. View the campaign details
2. Upload CSV file with phone numbers (one per line)
3. Or add numbers manually

### Starting a Campaign

1. Go to **Campaigns**
2. Click the **Play** button to start the campaign
3. Monitor progress in real-time on the **Monitoring** page

### Creating IVR Menus

1. Go to **IVR** → **New IVR Menu**
2. Select campaign
3. Upload audio file (WAV or MP3) - will be auto-converted
4. Configure DTMF actions:
   - **Press 1**: Call Extension (PJSIP/100)
   - **Press 2**: Add to Queue (sales)
   - **Press 3**: Hangup
   - **Press 0**: Playback message

### Viewing Call Records

1. Go to **CDR**
2. Filter by campaign, date, or disposition
3. Play or download recordings
4. Export to CSV

### Real-time Monitoring

1. Go to **Monitoring**
2. View:
   - Today's call statistics
   - Active campaigns with progress
   - Active channels
   - Answer rates and average talk time

## Configuration

### ARI Settings

Edit `/var/www/html/adial/application/config/ari.php`:

```php
$config['ari_host'] = 'localhost';
$config['ari_port'] = '8088';
$config['ari_username'] = 'dialer';
$config['ari_password'] = '76e6d233237c5323b9bb71860e322b61';
$config['ari_stasis_app'] = 'dialer';
```

### Stasis App Settings

Edit `/var/www/html/adial/stasis-app/.env`:

```env
ARI_HOST=localhost
ARI_PORT=8088
ARI_USERNAME=asterisk
ARI_PASSWORD=asterisk
ARI_APP_NAME=dialer

DB_HOST=localhost
DB_USER=root
DB_PASSWORD=mahapharata
DB_NAME=adialer

DEBUG_MODE=true
```

## Directory Structure

```
/var/www/html/adial/
├── application/          # CodeIgniter application
│   ├── controllers/      # Web controllers
│   ├── models/          # Database models
│   ├── views/           # HTML views
│   ├── libraries/       # ARI client library
│   └── config/          # Configuration files
├── stasis-app/          # Node.js Stasis application
│   ├── app.js           # Main application
│   ├── package.json     # Node dependencies
│   └── .env             # Environment configuration
├── recordings/          # Call recordings (MP3)
├── uploads/             # Temporary file uploads
├── logs/                # Application logs
└── public/              # Public assets

/var/lib/asterisk/sounds/dialer/  # IVR audio files
```

## API Endpoints

The system provides AJAX endpoints for real-time updates:

- `GET /dashboard/get_status` - System status
- `GET /dashboard/get_channels` - Active channels
- `POST /campaigns/control/{id}/{action}` - Control campaigns
- `GET /monitoring/get_realtime_data` - Real-time monitoring data
- `GET /cdr/stats` - CDR statistics

## Troubleshooting

### Stasis App Not Connecting

1. Check Asterisk ARI configuration:
   ```bash
   asterisk -rx "ari show users"
   ```

2. Verify ARI is listening:
   ```bash
   netstat -tulpn | grep 8088
   ```

3. Check Stasis app logs:
   ```bash
   tail -f /var/www/html/adial/logs/stasis-combined.log
   ```

### Calls Not Originating

1. Check trunk configuration in campaign
2. Verify endpoint is registered:
   ```bash
   asterisk -rx "pjsip show endpoints"
   ```
3. Check ARI logs in database:
   ```sql
   SELECT * FROM ari_logs ORDER BY created_at DESC LIMIT 10;
   ```

### Recordings Not Working

1. Verify recording path permissions:
   ```bash
   ls -la /var/www/html/adial/recordings
   ```

2. Check if sox/ffmpeg is installed:
   ```bash
   which sox
   which ffmpeg
   ```

3. Check Stasis app logs for recording errors

### IVR Audio Not Playing

1. Verify audio file format:
   ```bash
   file /var/lib/asterisk/sounds/dialer/*.wav
   ```

2. Should be: `RIFF (little-endian) data, WAVE audio, 8000 Hz, mono`

3. Manually convert if needed:
   ```bash
   sox input.wav -r 8000 -c 1 output.wav
   ```

## Security Notes

- Change default database password
- Update ARI credentials
- Restrict web access with authentication
- Use HTTPS in production
- Set proper file permissions
- Enable firewall rules

## Support

For issues and questions:
- Check logs in `/var/www/html/adial/logs/`
- Review ARI logs in database
- Check Asterisk logs: `/var/log/asterisk/full`

## License

Proprietary - All rights reserved
