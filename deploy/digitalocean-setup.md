# 🌊 DigitalOcean Deployment Guide

## Why DigitalOcean is Perfect for Your Game:
- ✅ **Native PHP + MySQL** support
- ✅ **Easy scaling** from $5 to $100+ servers
- ✅ **Managed databases** with automatic backups
- ✅ **Load balancers** for high traffic
- ✅ **CDN integration** for global performance

## 🎯 Deployment Options

### Option 1: App Platform (Recommended - Easiest)
**Cost:** $5-12/month | **Setup Time:** 5 minutes

### Option 2: Droplet + Database (Most Control)
**Cost:** $10-20/month | **Setup Time:** 15 minutes

---

## 🚀 Option 1: App Platform Setup

### Step 1: Create App
1. Go to [DigitalOcean App Platform](https://cloud.digitalocean.com/apps)
2. Click **"Create App"**
3. Connect your GitHub: `johan-becker/WW`
4. Select **main/master** branch

### Step 2: Configure Build
- **Source Directory:** `/` (root)
- **Build Command:** `echo "PHP app ready"`
- **Run Command:** `apache2-foreground`
- **Environment:** `PHP 8.2`

### Step 3: Add Database
- Click **"Add Resource"** → **"Database"**
- Choose **MySQL 8.0**
- Plan: **Basic ($15/month)**

### Step 4: Environment Variables
```bash
DB_HOST=${db.HOSTNAME}
DB_USER=${db.USERNAME}
DB_PASSWORD=${db.PASSWORD}
DB_NAME=${db.DATABASE}
PORT=8080
```

### Step 5: Deploy
- Click **"Create Resources"**
- Wait 5-10 minutes for deployment
- Get your URL: `https://your-app-name.ondigitalocean.app`

---

## 🔧 Option 2: Droplet Setup (More Control)

### Step 1: Create Droplet
```bash
# Choose:
- Ubuntu 22.04 LTS
- Regular Intel - $6/month (1 GB RAM)
- Add managed MySQL database ($15/month)
```

### Step 2: Initial Server Setup
```bash
# Connect via SSH
ssh root@your-droplet-ip

# Update system
apt update && apt upgrade -y

# Install LAMP stack
apt install apache2 php8.1 php8.1-mysql php8.1-cli php8.1-curl php8.1-json php8.1-mbstring -y

# Enable Apache modules
a2enmod rewrite
systemctl restart apache2
```

### Step 3: Deploy Your Code
```bash
# Remove default Apache page
rm -rf /var/www/html/*

# Clone your repository
cd /var/www/html
git clone https://github.com/johan-becker/WW.git .

# Set permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
```

### Step 4: Configure Database
```bash
# Create database connection file
nano /var/www/html/includes/includes.php

# Add your DigitalOcean database credentials:
# DB_HOST = your-db-host
# DB_USER = your-db-user  
# DB_PASSWORD = your-db-password
# DB_NAME = werwolf
```

### Step 5: Apache Configuration
```bash
# Create virtual host
nano /etc/apache2/sites-available/werewolf.conf
```

---

## 📊 Scaling Plan for Future Growth

### Phase 1: Single Server (0-1000 users)
- **1 Droplet:** $6/month
- **1 Database:** $15/month
- **Total:** $21/month

### Phase 2: Load Balanced (1000-10000 users)
- **2 Droplets:** $12/month
- **1 Load Balancer:** $12/month
- **1 Database:** $25/month (larger)
- **Redis Cache:** $15/month
- **Total:** $64/month

### Phase 3: Multi-Region (10000+ users)
- **4+ Droplets:** $24+/month
- **CDN:** $5/month
- **Database Cluster:** $50/month
- **Load Balancers:** $24/month
- **Total:** $103+/month

---

## 🎮 Game-Specific Optimizations

### Database Optimization
```sql
-- Add these indexes for better performance:
CREATE INDEX idx_game_phase ON games(id, phase);
CREATE INDEX idx_players_alive ON players(game_id, is_alive);
CREATE INDEX idx_votes ON players(game_id, vote_target);
```

### Apache Optimization
```apache
# Add to .htaccess for better performance:
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 6 months"
</IfModule>
```

### PHP Optimization
```php
// Add to php.ini:
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
```