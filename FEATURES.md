# Asterisk ARI Dialer - Complete Features List

## System Overview

A comprehensive auto-dialer system built with PHP (CodeIgniter), MySQL, Node.js, and Asterisk ARI.

---

## 📱 **Web Interface Features**

### 1. Dashboard
- ✅ Real-time system status (Asterisk, MySQL, ARI)
- ✅ Active campaigns overview
- ✅ Active channels count
- ✅ Today's call statistics
- ✅ Quick access to all modules

### 2. Campaign Management

#### Campaign Configuration
- ✅ **Create/Edit/Delete** campaigns
- ✅ **Trunk Configuration:**
  - Custom dial string (e.g., `Local/${EXTEN}@from-internal`)
  - Select from PJSIP endpoints
  - Select from SIP endpoints
  - Auto-discovery via ARI `/endpoints`
- ✅ **Caller ID** configuration
- ✅ **Agent Destination Types:**
  - Custom: Any dial string
  - Extension: Select from available extensions
  - IVR: Route to interactive menu
- ✅ **Call Recording:** Enable/disable with automatic mixing
- ✅ **Concurrent Calls:** Limit simultaneous calls
- ✅ **Retry Logic:** Configure retry attempts and delays

#### Campaign Control
- ✅ **Start** campaign
- ✅ **Stop** campaign
- ✅ **Pause** campaign
- ✅ Real-time status updates

#### Number Management
- ✅ **Bulk Add Numbers:**
  - Textarea input for multiple numbers
  - Format: `number,name` (name optional)
  - Example: `1234567890,John Doe`
  - Add hundreds of numbers at once

- ✅ **CSV Upload:**
  - Upload CSV files with numbers and names
  - Format: `number,name`
  - Auto-detect headers
  - Example:
    ```csv
    number,name
    1234567890,John Doe
    9876543210,Jane Smith
    ```

- ✅ **Number Display:**
  - Phone number
  - Contact name (if provided)
  - Status (pending/calling/answered/failed/completed)
  - Attempt count
  - Last attempt timestamp
  - Delete option

#### Campaign Statistics
- ✅ Total numbers
- ✅ Pending numbers
- ✅ Completed calls
- ✅ Failed calls
- ✅ Real-time progress tracking

### 3. Call Detail Records (CDR)

#### CDR Features
- ✅ **Advanced Filtering:**
  - By campaign
  - By disposition (answered/failed/no answer/busy)
  - By date range
  - Clear filters option

- ✅ **Display Information:**
  - Campaign reference
  - Caller ID
  - Destination number
  - **Destination name** (from contact data)
  - Agent information
  - Start/answer/end times
  - Duration and billable seconds
  - Disposition status
  - Recording controls

- ✅ **Recording Playback:**
  - Play recordings directly in browser
  - Download MP3 recordings
  - Both legs mixed in stereo

- ✅ **Export to CSV:**
  - Includes all fields
  - **Includes contact names**
  - Filtered by current selection
  - Timestamped filename

- ✅ **Pagination:** 50 records per page

### 4. Real-time Monitoring

#### Live Statistics
- ✅ Total calls today
- ✅ Answered calls count
- ✅ Answer rate percentage
- ✅ Average talk time
- ✅ Auto-refresh every 3 seconds

#### Active Campaigns
- ✅ Campaign status
- ✅ Concurrent call settings
- ✅ Direct links to campaign details

#### Active Channels
- ✅ Channel IDs
- ✅ Channel states
- ✅ Caller information
- ✅ Connected parties
- ✅ Live channel count

#### Campaign Progress
- ✅ Total numbers
- ✅ Pending/calling/answered/completed/failed counts
- ✅ Progress bar with percentage
- ✅ Visual status indicators

### 5. IVR Menu System

#### IVR Configuration
- ✅ **Create/Edit/Delete** IVR menus
- ✅ **Associate with campaigns**
- ✅ **Audio File Upload:**
  - Support for WAV and MP3
  - Automatic conversion to Asterisk format (8000Hz, mono)
  - Storage in `/var/lib/asterisk/sounds/dialer/`

#### DTMF Actions
- ✅ **Configure multiple actions per menu**
- ✅ **Action Types:**
  - Call Extension
  - Queue
  - Hangup
  - Playback
  - Go to IVR
- ✅ **Flexible digit mapping** (0-9, *, #)
- ✅ **Timeout configuration**
- ✅ **Max digits setting**

#### IVR Management
- ✅ View all IVR menus
- ✅ See action count
- ✅ Edit existing menus
- ✅ Delete unused menus
- ✅ Audio file management

---

## 🔧 **Node.js Stasis Application**

### Core Functionality
- ✅ **WebSocket connection** to Asterisk ARI
- ✅ **Database integration** with MySQL
- ✅ **Campaign polling** every 10 seconds
- ✅ **Automatic call origination**
- ✅ **Bridge management**
- ✅ **Call state tracking**

### Call Flow
1. ✅ Poll database for active campaigns
2. ✅ Check available call slots (concurrent limit)
3. ✅ Originate calls to pending numbers
4. ✅ Answer incoming channels
5. ✅ Connect to agent/IVR
6. ✅ Handle call progression
7. ✅ Update CDR records
8. ✅ Clean up on hangup

### Recording System
- ✅ **Channel snooping** for both legs
- ✅ **Separate recordings** (customer + agent)
- ✅ **Automatic mixing** into stereo
- ✅ **MP3 conversion** after call completion
- ✅ **Storage management**

### IVR Handling
- ✅ **Play audio files**
- ✅ **DTMF detection**
- ✅ **Action execution** based on input
- ✅ **Dynamic routing**

### Logging
- ✅ **Winston logger** with multiple transports
- ✅ **File logging** (error, combined)
- ✅ **Console logging** with colors
- ✅ **Database logging** for ARI calls (when debug enabled)
- ✅ **Systemd journal integration**

---

## 🗄️ **Database Schema**

### Tables

#### `campaigns`
- Campaign configuration
- Trunk settings
- Agent destinations
- Recording preferences
- Status tracking

#### `campaign_numbers`
- Phone numbers to dial
- **Contact names** (stored in JSON `data` field)
- Status per number
- Attempt tracking
- Last attempt timestamp

#### `ivr_menus`
- IVR menu definitions
- Audio file paths
- Timeout settings
- Campaign association

#### `ivr_actions`
- DTMF digit mappings
- Action types and values
- Menu associations

#### `cdr`
- Complete call records
- Campaign references
- Caller/destination info
- Agent information
- Timestamps
- Duration calculations
- Recording file paths
- Disposition status

#### `active_channels`
- Real-time channel tracking
- Campaign associations
- Channel states

#### `settings`
- System configuration
- Debug mode
- Feature flags

---

## 🎯 **Data Format Support**

### Number Import Formats

#### Bulk Text Input
```
1234567890,John Doe
9876543210,Jane Smith
5555555555
7777777777,Company ABC
```

#### CSV File
```csv
number,name
1234567890,John Doe
9876543210,Jane Smith
5555555555
7777777777,Company ABC
```

Or without header:
```csv
1234567890,John Doe
9876543210,Jane Smith
5555555555
```

#### CSV Export Format
```csv
ID,Campaign,Caller ID,Destination,Destination Name,Agent,Start Time,Answer Time,End Time,Duration,Billsec,Disposition
1,5,1234567890,9876543210,John Doe,PJSIP/100,2025-11-13 10:00:00,2025-11-13 10:00:05,2025-11-13 10:05:30,330,325,answered
```

---

## 🔐 **System Management**

### Service Control

#### Systemd Service
```bash
# Status
systemctl status ari-dialer

# Start/Stop/Restart
systemctl start ari-dialer
systemctl stop ari-dialer
systemctl restart ari-dialer

# Enable auto-start on boot
systemctl enable ari-dialer

# View logs
journalctl -u ari-dialer -f
```

#### Helper Scripts
```bash
# Start all services
/var/www/html/adial/start-dialer.sh

# Stop stasis app
/var/www/html/adial/stop-dialer.sh
```

### Configuration Files

#### Node.js Configuration
`/var/www/html/adial/stasis-app/.env`
- ARI credentials
- Database connection
- Debug settings
- Path configurations

#### PHP Configuration
`/var/www/html/adial/application/config/ari.php`
- ARI endpoint URL
- Credentials
- Stasis app name
- Sounds directory
- Recording settings

### Log Files
- **Stasis App:** `/var/www/html/adial/logs/stasis-combined.log`
- **Stasis Errors:** `/var/www/html/adial/logs/stasis-error.log`
- **Systemd Journal:** `journalctl -u ari-dialer`
- **Web Logs:** `/var/www/html/adial/logs/`
- **Apache:** `/var/log/httpd/` or `/var/log/apache2/`

---

## 📊 **Performance Features**

- ✅ **Concurrent call limiting** per campaign
- ✅ **Connection pooling** for database
- ✅ **Efficient polling** mechanism
- ✅ **Batch database operations**
- ✅ **Auto-restart** on failures (systemd)
- ✅ **Resource cleanup** on hangup

---

## 🔍 **Monitoring & Debugging**

### Debug Mode
When enabled in settings:
- ✅ All ARI requests logged to database
- ✅ Request/response data captured
- ✅ Status codes recorded
- ✅ Error messages stored

### Real-time Monitoring
- ✅ WebSocket connection status
- ✅ Active channel count
- ✅ Campaign progress bars
- ✅ Call statistics
- ✅ System health indicators

---

## 🎨 **User Interface**

- ✅ **Responsive design** (Bootstrap 4)
- ✅ **Dark sidebar** navigation
- ✅ **Color-coded** status badges
- ✅ **DataTables** for sorting/searching
- ✅ **AJAX operations** for smooth UX
- ✅ **Real-time updates** via polling
- ✅ **Modal dialogs** for media playback
- ✅ **Form validation**
- ✅ **Flash messages** for feedback

---

## 📁 **Directory Structure**

```
/var/www/html/adial/
├── application/              # CodeIgniter application
│   ├── controllers/         # Campaign, CDR, IVR, Monitoring
│   ├── models/              # Database models
│   ├── views/               # HTML templates
│   ├── libraries/           # ARI client library
│   └── config/              # Configuration files
├── stasis-app/              # Node.js Stasis application
│   ├── app.js               # Main application
│   ├── package.json         # Dependencies
│   └── .env                 # Environment config
├── recordings/              # Call recordings (MP3)
├── uploads/                 # Temporary uploads
├── logs/                    # Application logs
├── public/                  # Public assets
├── start-dialer.sh          # Startup script
├── stop-dialer.sh           # Stop script
└── README.md                # Documentation

/var/lib/asterisk/sounds/dialer/  # IVR audio files
/etc/systemd/system/ari-dialer.service  # Systemd service
```

---

## 🚀 **Getting Started**

1. **Start System:**
   ```bash
   /var/www/html/adial/start-dialer.sh
   ```

2. **Access Web Interface:**
   ```
   http://your-server-ip/adial
   ```

3. **Create Campaign:**
   - Navigate to Campaigns → New Campaign
   - Configure trunk and agent
   - Add numbers (bulk or CSV)
   - Start campaign

4. **Monitor Calls:**
   - Dashboard for overview
   - Monitoring for real-time
   - CDR for completed calls

---

## ✨ **Recent Enhancements**

### Version 1.1 Features
- ✅ **Bulk number adding** with names
- ✅ **Name field** throughout system
- ✅ **CSV import/export** with names
- ✅ **Enhanced CDR** with contact names
- ✅ **Systemd service** integration
- ✅ **Auto-start** on boot capability
- ✅ **Improved logging** with Winston

---

## 📞 **Support**

For issues or questions:
- Check logs: `journalctl -u ari-dialer -n 100`
- Review CDR for call details
- Enable debug mode for detailed ARI logging
- Check Asterisk logs: `asterisk -rx "core show channels"`

---

## 📝 **License**

Proprietary - All rights reserved
