#!/bin/bash
# 🚀 Automated DigitalOcean Deployment Script

set -e  # Exit on any error

echo "🐺 Deploying Werewolf Game to DigitalOcean..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
REPO_URL="https://github.com/johan-becker/WW.git"
WEB_DIR="/var/www/html"
BACKUP_DIR="/var/backups/werewolf"

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

print_status "Starting deployment process..."

# Update system
print_status "Updating system packages..."
apt update && apt upgrade -y

# Install required packages
print_status "Installing LAMP stack..."
apt install -y apache2 php8.1 php8.1-mysql php8.1-cli php8.1-curl php8.1-json php8.1-mbstring php8.1-xml php8.1-zip git htop unzip

# Enable Apache modules
print_status "Configuring Apache..."
a2enmod rewrite
a2enmod headers
a2enmod expires
a2enmod deflate

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup existing files if they exist
if [ -d "$WEB_DIR" ] && [ "$(ls -A $WEB_DIR 2>/dev/null)" ]; then
    print_status "Backing up existing files..."
    tar -czf "$BACKUP_DIR/backup-$(date +%Y%m%d-%H%M%S).tar.gz" -C "$WEB_DIR" .
fi

# Remove default Apache files
print_status "Preparing web directory..."
rm -rf $WEB_DIR/*

# Clone the repository
print_status "Cloning werewolf game repository..."
git clone $REPO_URL $WEB_DIR

# Set proper permissions
print_status "Setting file permissions..."
chown -R www-data:www-data $WEB_DIR
chmod -R 755 $WEB_DIR

# Create log directory
mkdir -p $WEB_DIR/log
chown www-data:www-data $WEB_DIR/log
chmod 775 $WEB_DIR/log

# Copy Apache configuration
if [ -f "$WEB_DIR/deploy/apache-config.conf" ]; then
    print_status "Installing Apache virtual host..."
    cp "$WEB_DIR/deploy/apache-config.conf" /etc/apache2/sites-available/werewolf.conf
    a2ensite werewolf.conf
    a2dissite 000-default.conf
fi

# Configure PHP
print_status "Optimizing PHP configuration..."
PHP_INI=$(php --ini | grep "Loaded Configuration File" | cut -d: -f2 | tr -d ' ')

# PHP optimizations
cat >> $PHP_INI << EOF

; Werewolf Game Optimizations
output_buffering = 4096
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 60

; OPcache settings
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
EOF

# Test Apache configuration
print_status "Testing Apache configuration..."
apache2ctl configtest

# Restart services
print_status "Restarting services..."
systemctl restart apache2
systemctl restart php8.1-fpm 2>/dev/null || true

# Check service status
if systemctl is-active --quiet apache2; then
    print_status "Apache is running successfully!"
else
    print_error "Apache failed to start!"
    exit 1
fi

# Create database configuration template
print_status "Creating database configuration template..."
cat > $WEB_DIR/includes/includes.example.php << 'EOF'
<?php
// DigitalOcean Database Configuration
// Copy this file to includes.php and fill in your database details

$id = "your_db_user";        // Database username
$pw = "your_db_password";    // Database password  
$host = "your_db_host";      // Database host
$db = "werwolf";             // Database name

$mysqli = new MySQLi($host, $id, $pw, $db);

if (mysqli_connect_errno()) {
    printf("Can't connect to MySQL Server. Errorcode: %s\n", mysqli_connect_error());
    exit;
}
?>
EOF

# Display completion message
print_status "🎉 Deployment completed successfully!"
echo ""
echo "📋 Next steps:"
echo "1. Configure your database connection in: $WEB_DIR/includes/includes.php"
echo "2. Set up your domain name in Apache configuration"
echo "3. Install SSL certificate (Let's Encrypt recommended)"
echo "4. Test your game at: http://your-server-ip"
echo ""
echo "📊 Useful commands:"
echo "- Check Apache status: systemctl status apache2"
echo "- View Apache logs: tail -f /var/log/apache2/werewolf_error.log"
echo "- Update game: cd $WEB_DIR && git pull"
echo ""
print_status "Your werewolf game is ready to play! 🐺"