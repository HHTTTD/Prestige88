# Prestige88 - Deployment Guide

## 🚀 การ Deploy ระบบสำหรับการใช้งานจริง

### 📋 Prerequisites

#### 1. Server Requirements
- **PHP**: 7.4 หรือสูงกว่า
- **Web Server**: Apache 2.4+ หรือ Nginx 1.18+
- **SSL Certificate**: สำหรับ HTTPS
- **Storage**: อย่างน้อย 1GB สำหรับไฟล์และ logs
- **Memory**: อย่างน้อย 512MB RAM

#### 2. PHP Extensions ที่จำเป็น
```bash
# ตรวจสอบ PHP extensions
php -m | grep -E "(json|curl|mbstring|openssl|session|fileinfo|gd)"
```

Extensions ที่ต้องมี:
- `json` - สำหรับจัดการ JSON data
- `curl` - สำหรับ SMS API calls
- `mbstring` - สำหรับ string handling
- `openssl` - สำหรับ security functions
- `session` - สำหรับ session management
- `fileinfo` - สำหรับ file upload validation
- `gd` - สำหรับ image processing (optional)

### 🔧 การตั้งค่า Server

#### 1. Apache Configuration
สร้างไฟล์ `.htaccess` ใน root directory:

```apache
# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Protect sensitive files
<Files "*.json">
    Order allow,deny
    Deny from all
</Files>

<Files "*.log">
    Order allow,deny
    Deny from all
</Files>

# PHP Settings
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value memory_limit 256M

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Cache static files
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
</IfModule>
```

#### 2. Nginx Configuration
สร้างไฟล์ `nginx.conf`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;
    
    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # Security Headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    
    root /var/www/prestige88;
    index index.php;
    
    # Handle PHP files
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Protect sensitive files
    location ~ \.(json|log)$ {
        deny all;
    }
    
    # Cache static files
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|webp)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### 🔐 การตั้งค่าความปลอดภัย

#### 1. ตั้งค่า SMS Gateway
แก้ไขไฟล์ `config/sms_gateway.php`:

```php
// สำหรับ Twilio
const TWILIO_ACCOUNT_SID = 'your_actual_account_sid';
const TWILIO_AUTH_TOKEN = 'your_actual_auth_token';
const TWILIO_FROM_NUMBER = '+1234567890';

// หรือสำหรับ Thai SMS Gateway
const THAI_SMS_USERNAME = 'your_actual_username';
const THAI_SMS_PASSWORD = 'your_actual_password';
const THAI_SMS_SENDER = 'PRESTIGE88';

// เปลี่ยนเป็น provider จริง
const SMS_PROVIDER = 'twilio'; // หรือ 'thai_sms'
```

#### 2. ตั้งค่า Environment
แก้ไขไฟล์ `config/environment.php`:

```php
// เปลี่ยนเป็น production
const ENV_PRODUCTION = 'production';
```

#### 3. ตั้งค่า File Permissions
```bash
# Set proper permissions
chmod 755 /var/www/prestige88
chmod 644 /var/www/prestige88/*.php
chmod 755 /var/www/prestige88/config/
chmod 755 /var/www/prestige88/controllers/
chmod 755 /var/www/prestige88/models/
chmod 755 /var/www/prestige88/views/
chmod 755 /var/www/prestige88/utils/

# Create and set permissions for data directories
mkdir -p /var/www/prestige88/data
mkdir -p /var/www/prestige88/storage/logs
mkdir -p /var/www/prestige88/storage/cache
mkdir -p /var/www/prestige88/uploads
mkdir -p /var/www/prestige88/qr_images

chmod 755 /var/www/prestige88/data
chmod 755 /var/www/prestige88/storage
chmod 755 /var/www/prestige88/storage/logs
chmod 755 /var/www/prestige88/storage/cache
chmod 755 /var/www/prestige88/uploads
chmod 755 /var/www/prestige88/qr_images

# Set ownership to web server user
chown -R www-data:www-data /var/www/prestige88
```

### 📊 การตั้งค่า Database (Optional)

#### สำหรับ MySQL Database
หากต้องการใช้ MySQL แทน JSON files:

1. **สร้าง Database**:
```sql
CREATE DATABASE prestige88_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'prestige88_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON prestige88_db.* TO 'prestige88_user'@'localhost';
FLUSH PRIVILEGES;
```

2. **สร้าง Tables**:
```sql
USE prestige88_db;

CREATE TABLE users (
    id VARCHAR(255) PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(255),
    role ENUM('admin', 'client') DEFAULT 'client',
    status ENUM('active', 'inactive') DEFAULT 'active',
    membership_tier ENUM('silver', 'gold', 'platinum') DEFAULT 'silver',
    points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE jets (
    id VARCHAR(255) PRIMARY KEY,
    model VARCHAR(255) NOT NULL,
    image TEXT,
    capacity INT NOT NULL,
    max_speed INT NOT NULL,
    range_km INT NOT NULL,
    price_per_hour DECIMAL(10,2) NOT NULL,
    amenities JSON,
    available_slots JSON,
    status ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE bookings (
    id VARCHAR(255) PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    jet_id VARCHAR(255) NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    jet_model VARCHAR(255) NOT NULL,
    departure_location VARCHAR(255) NOT NULL,
    arrival_location VARCHAR(255) NOT NULL,
    departure_date DATE NOT NULL,
    departure_time TIME NOT NULL,
    passengers INT NOT NULL,
    flight_hours INT NOT NULL,
    special_requests TEXT,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    price_per_hour DECIMAL(10,2) NOT NULL,
    membership_discount DECIMAL(5,2) DEFAULT 0,
    base_total DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    total_cost DECIMAL(10,2) NOT NULL,
    seat VARCHAR(10),
    bus_number VARCHAR(20),
    boarding_gate VARCHAR(20),
    qr_code VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (jet_id) REFERENCES jets(id)
);

CREATE TABLE otp_verifications (
    id VARCHAR(255) PRIMARY KEY,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    user_data JSON,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE notifications (
    id VARCHAR(255) PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### 🔄 การตั้งค่า Cron Jobs

#### 1. Cleanup Expired OTPs
```bash
# Add to crontab (crontab -e)
# Cleanup expired OTPs every hour
0 * * * * php /var/www/prestige88/cron/cleanup_otp.php

# Cleanup old logs daily
0 2 * * * php /var/www/prestige88/cron/cleanup_logs.php

# Send booking reminders daily
0 8 * * * php /var/www/prestige88/cron/send_reminders.php
```

#### 2. สร้างไฟล์ Cron Scripts
สร้างไฟล์ `cron/cleanup_otp.php`:
```php
<?php
require_once '../config/environment.php';
require_once '../models/OTP.php';

// Cleanup expired OTPs
OTP::cleanupExpiredOTPs();
echo "OTP cleanup completed at " . date('Y-m-d H:i:s') . "\n";
```

### 📈 การ Monitor และ Logging

#### 1. ตั้งค่า Log Rotation
สร้างไฟล์ `/etc/logrotate.d/prestige88`:
```
/var/www/prestige88/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload apache2
    endscript
}
```

#### 2. ตั้งค่า Monitoring
- ใช้ tools เช่น Nagios, Zabbix, หรือ New Relic
- Monitor disk space, memory usage, response time
- Set up alerts for errors and performance issues

### 🚀 การ Deploy

#### 1. Upload Files
```bash
# Upload files to server
scp -r prestige88/ user@your-server:/var/www/
```

#### 2. Final Configuration
```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/prestige88
sudo chmod -R 755 /var/www/prestige88
sudo chmod -R 644 /var/www/prestige88/*.php

# Restart web server
sudo systemctl restart apache2
# หรือ
sudo systemctl restart nginx
```

#### 3. Test the System
1. เข้าสู่ระบบด้วย admin account
2. ทดสอบการสมัครสมาชิกใหม่
3. ทดสอบการจองเครื่องบิน
4. ทดสอบระบบ OTP
5. ตรวจสอบ logs

### 🔧 การบำรุงรักษา

#### 1. Regular Backups
```bash
#!/bin/bash
# backup.sh
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/prestige88"
mkdir -p $BACKUP_DIR

# Backup data files
tar -czf $BACKUP_DIR/data_$DATE.tar.gz /var/www/prestige88/data/
tar -czf $BACKUP_DIR/logs_$DATE.tar.gz /var/www/prestige88/storage/logs/

# Keep only last 7 days
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

#### 2. Security Updates
- Update PHP และ extensions เป็นประจำ
- Monitor security advisories
- Keep SSL certificates updated
- Regular security audits

### 📞 Support

หากมีปัญหาหรือต้องการความช่วยเหลือ:
- ตรวจสอบ logs ใน `storage/logs/`
- ตรวจสอบ error logs ของ web server
- ติดต่อ support team

---

**หมายเหตุ**: ระบบนี้พร้อมใช้งานจริงแล้ว แต่ควรทดสอบใน staging environment ก่อน deploy ไป production 