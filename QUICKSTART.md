# ARI Dialer - Quick Start Guide

## 🚀 Quick Installation (5 Minutes)

### One-Command Install

```bash
cd /var/www/html/adial
sudo chmod +x install.sh
sudo ./install.sh
```

The script will automatically:
- ✅ Install all dependencies
- ✅ Configure database
- ✅ Set up Asterisk ARI
- ✅ Configure web server
- ✅ Start all services

**That's it!** The installer will display your credentials and access URL at the end.

---

## 📋 Prerequisites

- Fresh CentOS 7/8 or Ubuntu 18.04+ server
- Root/sudo access
- Internet connection
- 2GB RAM minimum
- 10GB disk space

---

## 🔑 After Installation

### 1. Access Web Interface

Open in your browser:
```
http://YOUR_SERVER_IP/adial
```

### 2. Find Your Credentials

Credentials are saved in:
```bash
cat /var/www/html/adial/.credentials
```

### 3. Manage Services

```bash
# Check status
sudo systemctl status ari-dialer

# Start/Stop/Restart
sudo systemctl start ari-dialer
sudo systemctl stop ari-dialer
sudo systemctl restart ari-dialer

# View logs
sudo journalctl -u ari-dialer -f
```

### 4. Run Startup Script

```bash
sudo /var/www/html/adial/start-dialer.sh
```

This checks all services and displays status.

---

## 🎯 Create Your First Campaign

1. **Access Web Interface** → Navigate to "Campaigns"

2. **Click "New Campaign"**

3. **Fill Campaign Details:**
   - Name: My First Campaign
   - Trunk Type: PJSIP (or your trunk type)
   - Trunk Value: Your trunk name
   - Agent Destination: Extension or number
   - Concurrent Calls: 1 (for testing)

4. **Upload Phone Numbers:**
   - Prepare CSV file with phone numbers
   - Upload via "Add Numbers" section

5. **Start Campaign:**
   - Click "Start" button
   - Monitor in real-time

---

## 📞 Configure Asterisk Extension (Important!)

Before campaigns will work, configure your Asterisk dialplan:

### Edit `/etc/asterisk/extensions.conf`:

```ini
[default]
exten => _X.,1,NoOp(Incoming call)
 same => n,Answer()
 same => n,Stasis(dialer,${EXTEN})
 same => n,Hangup()
```

### Reload Asterisk:
```bash
sudo asterisk -rx "dialplan reload"
```

---

## 🔧 Common Issues & Fixes

### Web page not loading
```bash
sudo systemctl restart httpd  # or apache2 for Ubuntu
```

### Database connection error
```bash
# Check MariaDB is running
sudo systemctl status mariadb

# View credentials
cat /var/www/html/adial/.credentials
```

### ARI connection failed
```bash
# Restart Asterisk
sudo systemctl restart asterisk

# Test ARI (replace password)
curl -u dialer:PASSWORD http://localhost:8088/ari/asterisk/info
```

### Stasis app not starting
```bash
# View logs
sudo journalctl -u ari-dialer -n 50

# Restart service
sudo systemctl restart ari-dialer
```

---

## 📚 Need More Help?

- **Full Installation Guide:** See `INSTALL.md`
- **Features Documentation:** See `FEATURES.md`
- **Authentication:** See `AUTHENTICATION.md`
- **Logs:** `/var/www/html/adial/logs/`

---

## ⚙️ Important Directories

```
/var/www/html/adial/              # Application root
/var/www/html/adial/logs/         # Application logs
/var/lib/asterisk/sounds/dialer/  # IVR audio files
/var/spool/asterisk/monitor/      # Call recordings
```

---

## 🎓 Next Steps

1. ✅ Configure your SIP/PJSIP trunks in Asterisk
2. ✅ Set up agent extensions
3. ✅ Create IVR menus (optional)
4. ✅ Test with small campaigns first
5. ✅ Monitor real-time dashboard
6. ✅ Review CDR reports

---

## 💡 Pro Tips

- **Start small:** Test with 1-2 concurrent calls first
- **Monitor logs:** Keep logs open during first campaigns
- **Test audio:** Ensure IVR audio is in correct format (8000Hz WAV)
- **Backup credentials:** Save `.credentials` file securely
- **Regular backups:** Backup database regularly

---

## 🔐 Security Recommendations

```bash
# Change default passwords immediately
mysql -u root -p
ALTER USER 'adialer_user'@'localhost' IDENTIFIED BY 'NEW_STRONG_PASSWORD';

# Update config files with new password
nano /var/www/html/adial/application/config/database.php
nano /var/www/html/adial/stasis-app/.env

# Restart services
sudo systemctl restart ari-dialer
```

---

**Happy Dialing! 📞**
