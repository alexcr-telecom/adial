#!/bin/bash

###############################################################################
# ARI Dialer - FreePBX Installation Script
# For FreePBX/Sangoma Linux systems ONLY
# Uses existing Apache, PHP, MariaDB, and Asterisk from FreePBX
################################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
INSTALL_DIR="/var/www/html/adial"
DB_NAME="adialer"
DB_USER="adialer_user"
DB_PASS=""
MYSQL_ROOT_PASS=""
ARI_USER="dialer"
ARI_PASS=""

# Progress tracking
CURRENT_STEP=0
TOTAL_STEPS=8

# Helper functions
print_step_header() {
    CURRENT_STEP=$((CURRENT_STEP + 1))
    echo ""
    echo -e "${BLUE}========================================================================"
    echo "STEP ${CURRENT_STEP}/${TOTAL_STEPS}: $1"
    echo "========================================================================${NC}"
}

print_step_complete() {
    echo -e "${GREEN}✓ STEP ${CURRENT_STEP}/${TOTAL_STEPS} COMPLETED: $1${NC}"
    echo ""
}

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ $1${NC}"; }

check_root() {
    if [ "$EUID" -ne 0 ]; then
        print_error "This script must be run as root"
        exit 1
    fi
}

detect_freepbx() {
    if [[ ! -d "/var/www/html/admin" ]]; then
        print_error "FreePBX not detected!"
        print_error "This script is for FreePBX systems only"
        print_error "For standalone systems, use install.sh instead"
        exit 1
    fi
    print_success "FreePBX detected"
}

generate_password() {
    openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 16
}

prompt_mysql_password() {
    print_step_header "MySQL Root Password"
    
    if mysql -u root -e "SELECT 1;" &>/dev/null; then
        print_success "MySQL root has no password set"
        MYSQL_ROOT_PASS=""
        return 0
    fi
    
    print_info "MySQL root password required"
    local attempts=0
    while [ $attempts -lt 3 ]; do
        read -sp "Enter MySQL root password: " MYSQL_ROOT_PASS
        echo ""
        if mysql -u root --password="$MYSQL_ROOT_PASS" -e "SELECT 1;" &>/dev/null; then
            print_success "Password verified"
            print_step_complete "MySQL Root Password"
            return 0
        fi
        attempts=$((attempts + 1))
        [ $attempts -lt 3 ] && print_error "Invalid password (Attempt $attempts/3)"
    done
    print_error "Failed after 3 attempts"
    exit 1
}

mysql_cmd() {
    if [ -z "$MYSQL_ROOT_PASS" ]; then
        mysql -u root --connect-timeout=10 "$@"
    else
        mysql -u root --password="$MYSQL_ROOT_PASS" --connect-timeout=10 "$@"
    fi
}

install_nodejs() {
    print_step_header "Installing Node.js"
    
    if command -v node &> /dev/null; then
        print_success "Node.js already installed: $(node --version)"
    else
        print_info "Installing Node.js..."
        curl -fsSL https://rpm.nodesource.com/setup_16.x | bash -
        yum install -y nodejs
        print_success "Node.js installed: $(node --version)"
    fi
    
    print_step_complete "Installing Node.js"
}

setup_database() {
    print_step_header "Setting Up Database"
    
    # Generate password
    if [ -z "$DB_PASS" ]; then
        DB_PASS=$(generate_password)
        print_info "Generated database password: $DB_PASS"
    fi
    
    # Create database
    print_info "Creating database '$DB_NAME'..."
    if ! mysql_cmd -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;" 2>&1; then
        print_error "Failed to create database"
        exit 1
    fi
    print_success "Database '${DB_NAME}' created"
    
    # Check if user exists
    print_info "Checking if user exists..."
    if mysql_cmd -e "SELECT User FROM mysql.user WHERE User='${DB_USER}' AND Host='localhost';" 2>/dev/null | grep -q "${DB_USER}"; then
        print_info "User exists, dropping..."
        mysql_cmd -e "DROP USER '${DB_USER}'@'localhost';" 2>&1
    fi
    
    # Create user
    print_info "Creating user '${DB_USER}'..."
    CREATE_USER_OUTPUT=$(mysql_cmd -e "CREATE USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';" 2>&1)
    if [ $? -ne 0 ]; then
        print_error "Failed to create user"
        echo "Error: $CREATE_USER_OUTPUT"
        exit 1
    fi
    print_success "User created"
    
    # Grant privileges
    print_info "Granting privileges..."
    mysql_cmd -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';" 2>&1
    mysql_cmd -e "FLUSH PRIVILEGES;" 2>&1
    print_success "Privileges granted"
    
    # Import schema
    if [ -f "${INSTALL_DIR}/database_schema.sql" ]; then
        print_info "Importing schema..."
        mysql_cmd "$DB_NAME" < "${INSTALL_DIR}/database_schema.sql" 2>&1
        print_success "Schema imported"
    fi
    
    # Update config
    if [ -f "${INSTALL_DIR}/application/config/database.php" ]; then
        sed -i "s/'hostname' => '[^']*'/'hostname' => 'localhost'/g" "${INSTALL_DIR}/application/config/database.php"
        sed -i "s/'username' => '[^']*'/'username' => '${DB_USER}'/g" "${INSTALL_DIR}/application/config/database.php"
        sed -i "s/'password' => '[^']*'/'password' => '${DB_PASS}'/g" "${INSTALL_DIR}/application/config/database.php"
        sed -i "s/'database' => '[^']*'/'database' => '${DB_NAME}'/g" "${INSTALL_DIR}/application/config/database.php"
        print_success "Config updated"
    fi
    
    print_step_complete "Setting Up Database"
}

setup_directories() {
    print_step_header "Setting Up Directories"
    
    mkdir -p "${INSTALL_DIR}/logs"
    mkdir -p "${INSTALL_DIR}/recordings"
    mkdir -p "${INSTALL_DIR}/uploads"
    mkdir -p "/var/lib/asterisk/sounds/dialer"
    mkdir -p "/var/spool/asterisk/monitor"
    
    chown -R asterisk:asterisk "${INSTALL_DIR}"
    chmod -R 755 "${INSTALL_DIR}"
    chmod -R 777 "${INSTALL_DIR}/logs"
    chmod -R 777 "${INSTALL_DIR}/recordings"
    chmod -R 777 "${INSTALL_DIR}/uploads"
    
    print_success "Directories created"
    print_step_complete "Setting Up Directories"
}

configure_freepbx_apache() {
    print_step_header "Configuring Apache for FreePBX"
    
    # Create AllowOverride config for adial directory
    if [ ! -f /etc/httpd/conf.d/adial-allowoverride.conf ]; then
        cat > /etc/httpd/conf.d/adial-allowoverride.conf << 'EOF'
<Directory /var/www/html/adial>
    AllowOverride All
</Directory>
EOF
        print_success "Created AllowOverride config"
    else
        print_success "AllowOverride already configured"
    fi
    
    systemctl restart httpd 2>/dev/null || true
    print_success "Apache configured"
    print_step_complete "Configuring Apache for FreePBX"
}

configure_ari() {
    print_step_header "Configuring Asterisk ARI"
    
    # Generate ARI password
    if [ -z "$ARI_PASS" ]; then
        ARI_PASS=$(generate_password)
        print_info "Generated ARI password: $ARI_PASS"
    fi
    
    # Create .env file for stasis-app
    if [ -d "${INSTALL_DIR}/stasis-app" ]; then
        print_info "Creating stasis-app .env file..."
        cat > "${INSTALL_DIR}/stasis-app/.env" << EOF
# Asterisk ARI Configuration
ARI_HOST=127.0.0.1
ARI_PORT=8088
ARI_USERNAME=${ARI_USER}
ARI_PASSWORD=${ARI_PASS}
ARI_APP_NAME=dialer

# MySQL Database Configuration
DB_HOST=127.0.0.1
DB_USER=${DB_USER}
DB_PASSWORD=${DB_PASS}
DB_NAME=${DB_NAME}

# Application Settings
DEBUG_MODE=true
LOG_LEVEL=debug
RECORDINGS_PATH=/var/spool/asterisk/monitor/adial
SOUNDS_PATH=/var/lib/asterisk/sounds/dialer
EOF
        chmod 600 "${INSTALL_DIR}/stasis-app/.env"
        print_success "✓✓✓ IMPORTANT: .env file created at ${INSTALL_DIR}/stasis-app/.env ✓✓✓"
    fi
    
    # Update application config
    if [ -f "${INSTALL_DIR}/application/config/ari.php" ]; then
        sed -i "s/\$config\['ari_username'\] = '[^']*';/\$config['ari_username'] = '${ARI_USER}';/g" "${INSTALL_DIR}/application/config/ari.php"
        sed -i "s/\$config\['ari_password'\] = '[^']*';/\$config['ari_password'] = '${ARI_PASS}';/g" "${INSTALL_DIR}/application/config/ari.php"
        print_success "Application config updated"
    fi
    
    # Configure ARI in FreePBX-safe way using ari_additional_custom.conf
    print_info "Configuring ARI in ari_additional_custom.conf..."
    
    if [ -f /etc/asterisk/ari_additional_custom.conf ] && grep -q "^\[${ARI_USER}\]" /etc/asterisk/ari_additional_custom.conf; then
        print_info "Updating existing ARI user..."
        sed -i "/^\[${ARI_USER}\]/,/^\[/ s/^password = .*/password = ${ARI_PASS}/" /etc/asterisk/ari_additional_custom.conf
    else
        print_info "Adding new ARI user..."
        cat >> /etc/asterisk/ari_additional_custom.conf << EOF

; ARI Dialer User Configuration
[${ARI_USER}]
type = user
read_only = no
password = ${ARI_PASS}
EOF
    fi
    
    asterisk -rx "module reload res_ari.so" 2>/dev/null || asterisk -rx "core reload" 2>/dev/null || true
    print_success "ARI configured in ari_additional_custom.conf"
    print_warning "IMPORTANT: Verify ARI is enabled in FreePBX GUI:"
    print_warning "  Settings -> Asterisk REST Interface (ARI)"
    
    print_step_complete "Configuring Asterisk ARI"
}

setup_nodejs_app() {
    print_step_header "Setting Up Node.js Application"
    
    if [ -d "${INSTALL_DIR}/stasis-app" ]; then
        cd "${INSTALL_DIR}/stasis-app"
        print_info "Installing Node.js dependencies..."
        npm install --production
        print_success "Dependencies installed"
    fi
    
    print_step_complete "Setting Up Node.js Application"
}

setup_systemd() {
    print_step_header "Setting Up Systemd Service"
    
    cat > /etc/systemd/system/ari-dialer.service << EOF
[Unit]
Description=Asterisk ARI Dialer - Stasis Application
After=network.target asterisk.service mariadb.service
Requires=asterisk.service mariadb.service

[Service]
Type=simple
User=root
WorkingDirectory=${INSTALL_DIR}/stasis-app
ExecStart=/usr/bin/node app.js
Restart=always
RestartSec=10
StandardOutput=append:${INSTALL_DIR}/logs/stasis-combined.log
StandardError=append:${INSTALL_DIR}/logs/stasis-combined.log

[Install]
WantedBy=multi-user.target
EOF
    
    systemctl daemon-reload
    systemctl enable ari-dialer
    print_success "Service created and enabled"
    print_step_complete "Setting Up Systemd Service"
}

start_service() {
    print_step_header "Starting ARI Dialer Service"
    
    systemctl start ari-dialer
    sleep 3
    
    if systemctl is-active --quiet ari-dialer; then
        print_success "ARI Dialer started successfully"
    else
        print_error "Service failed to start"
        print_info "Check logs: journalctl -u ari-dialer -n 50"
    fi
    
    print_step_complete "Starting ARI Dialer Service"
}

print_summary() {
    SERVER_IP=$(hostname -I | awk '{print $1}')
    
    echo ""
    echo "========================================================================"
    echo "                    Installation Complete!"
    echo "========================================================================"
    echo ""
    echo "System Type: FreePBX/Sangoma Linux"
    echo ""
    echo "Web Interfaces:"
    echo "  ARI Dialer:  http://${SERVER_IP}/adial"
    echo "  FreePBX GUI: http://${SERVER_IP}/admin"
    echo ""
    echo "ARI Dialer Login:"
    echo "  Username: admin"
    echo "  Password: admin"
    echo "  ⚠️  CHANGE DEFAULT PASSWORD IMMEDIATELY!"
    echo ""
    echo "Database:"
    echo "  Database: ${DB_NAME}"
    echo "  Username: ${DB_USER}"
    echo "  Password: ${DB_PASS}"
    echo ""
    echo "Asterisk ARI:"
    echo "  Username: ${ARI_USER}"
    echo "  Password: ${ARI_PASS}"
    echo "  Config:   /etc/asterisk/ari_additional_custom.conf"
    echo ""
    echo "⚠️  FreePBX Note:"
    echo "  • ARI user configured in ari_additional_custom.conf (FreePBX-safe)"
    echo "  • Verify ARI is enabled: Settings -> Asterisk REST Interface"
    echo "  • FreePBX configs were NOT modified"
    echo ""
    echo "Services:"
    echo "  • ARI Dialer: $(systemctl is-active ari-dialer 2>/dev/null || echo 'inactive')"
    echo ""
    echo "Management:"
    echo "  • Start:   systemctl start ari-dialer"
    echo "  • Stop:    systemctl stop ari-dialer"
    echo "  • Restart: systemctl restart ari-dialer"
    echo "  • Logs:    journalctl -u ari-dialer -f"
    echo ""
    echo "========================================================================"
    
    # Save credentials
    cat > "${INSTALL_DIR}/.credentials" << EOF
ARI Dialer Installation Credentials (FreePBX)
Generated: $(date)

Web Interface:
  URL: http://${SERVER_IP}/adial
  Username: admin
  Password: admin

Database:
  Database: ${DB_NAME}
  Username: ${DB_USER}
  Password: ${DB_PASS}

Asterisk ARI:
  Username: ${ARI_USER}
  Password: ${ARI_PASS}
EOF
    chmod 600 "${INSTALL_DIR}/.credentials"
    print_info "Credentials saved to: ${INSTALL_DIR}/.credentials"
}

# Main installation
main() {
    echo -e "${BLUE}========================================================================"
    echo "ARI Dialer Installation Script - FreePBX Systems"
    echo "========================================================================${NC}"
    
    check_root
    detect_freepbx
    
    echo ""
    read -p "Proceed with installation? (y/n): " -n 1 -r
    echo ""
    [[ ! $REPLY =~ ^[Yy]$ ]] && exit 0
    
    install_nodejs
    prompt_mysql_password
    setup_database
    setup_directories
    configure_freepbx_apache
    configure_ari
    setup_nodejs_app
    setup_systemd
    start_service
    print_summary
    
    echo -e "${GREEN}Installation completed successfully!${NC}"
}

main "$@"
