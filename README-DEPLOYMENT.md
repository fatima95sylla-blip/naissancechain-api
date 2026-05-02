# NaissanceChain API - WAMP64 Deployment Guide

## 🚀 Quick Start

### 1. One-Click Deployment
```bash
# Run the automated deployment script
deploy.bat
```

### 2. Manual Deployment
```bash
# Copy environment file
copy env.production.txt .env

# Generate application key
php artisan key:generate

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Optimize application
php artisan optimize

# Start queue workers
start-queue-workers.bat
```

## 📋 Prerequisites

### WAMP64 Requirements
- Apache 2.4+ with modules: rewrite, headers, deflate, expires, ssl
- PHP 8.1+ with extensions: opcache, mysql, pdo_mysql, curl, gd, zip
- MySQL 8.0+ or MariaDB 10.5+
- Windows 10/11 with administrator privileges

### Directory Structure
```
C:/wamp64/www/naissancechain-api/
├── .env                    # Production environment
├── apache-vhost.conf       # Apache VirtualHost
├── deploy.bat              # Deployment script
├── start-queue-workers.bat # Queue workers
├── mysql-backup.bat       # Database backup
├── php-opcache.ini        # OPcache config
├── wamp64-setup.txt       # WAMP64 setup
└── deployment-checklist.md # Deployment checklist
```

## ⚙️ Configuration Files

### Environment Configuration
- **Source**: `env.production.txt`
- **Target**: `.env`
- **Key Settings**: Production mode, MySQL, CORS, Security

### Apache VirtualHost
- **Source**: `apache-vhost.conf`
- **Target**: `httpd-vhosts.conf`
- **Features**: HTTPS, CORS, Security Headers, Compression

### PHP Optimization
- **Source**: `php-opcache.ini`
- **Target**: `php.ini`
- **Benefits**: 50-80% performance improvement

## 🔧 Setup Commands

### Database Setup
```sql
CREATE DATABASE naissancechain_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Hosts Configuration
```bash
# Add to C:\Windows\System32\drivers\etc\hosts
127.0.0.1 naissancechain.local
```

### Queue Management
```bash
# Start workers
start-queue-workers.bat

# Monitor queue
php artisan queue:monitor

# Clear failed jobs
php artisan queue:flush
```

## 🚨 Security Configuration

### Apache Security Headers
```apache
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set X-XSS-Protection "1; mode=block"
Header always set Content-Security-Policy "default-src 'self'..."
```

### Laravel Security
```env
APP_DEBUG=false
APP_ENV=production
SANCTUM_STATEFUL_DOMAINS=http://naissancechain.local
```

### File Protection
```apache
<Files ".env*">
    Require all denied
</Files>
```

## 📊 Performance Optimization

### Laravel Caching
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### OPcache Settings
```ini
opcache.enable=1
opcache.memory_consumption=512
opcache.max_accelerated_files=4000
opcache.revalidate_freq=0
```

### Apache Compression
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
</IfModule>
```

## 🔄 Maintenance Procedures

### Daily Tasks
```bash
# MySQL backup
mysql-backup.bat

# Clear logs
php artisan log:clear

# Cache cleanup
php artisan cache:clear
```

### Weekly Tasks
```bash
# Security audit
composer audit

# Update dependencies
composer update

# Restart services
# Apache + MySQL
```

## 📱 Flutter Integration

### CORS Configuration
```env
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://flutter.local
CORS_ALLOWED_METHODS=GET,POST,PUT,PATCH,DELETE,OPTIONS
CORS_ALLOW_CREDENTIALS=true
```

### API Endpoints
```
Base URL: http://naissancechain.local/api/v1

Authentication:
POST /register
POST /login
POST /logout
GET  /me

Naissance:
GET    /naissances
POST   /naissances
GET    /naissances/{id}
PUT    /naissances/{id}
DELETE /naissances/{id}

Blockchain:
POST /blockchain/verify/{id}
GET  /blockchain/stats
GET  /blockchain/records
```

## 🔍 Testing & Monitoring

### Health Check
```bash
curl http://naissancechain.local/api/v1/health
```

### Postman Collection
- Import `naissancechain-api.postman_collection.json`
- Set base URL: `http://naissancechain.local/api/v1`
- Test authentication flow

### Performance Monitoring
```bash
# Laravel metrics
php artisan about

# Queue status
php artisan queue:monitor

# Cache statistics
php artisan cache:stats
```

## 🚨 Troubleshooting

### Common Issues

#### 500 Internal Server Error
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check Apache logs
tail -f C:/wamp64/logs/apache_error.log

# Clear caches
php artisan cache:clear
```

#### Database Connection Failed
```bash
# Verify MySQL service
sc query wampmysql

# Test connection
php artisan tinker
DB::connection()->getPdo()
```

#### Queue Workers Not Running
```bash
# Restart workers
start-queue-workers.bat

# Check failed jobs
php artisan queue:failed
```

#### Permission Denied
```bash
# Set permissions
icacls storage /grant "IUSR:(OI)(CI)(F)" /T
icacls public /grant "IUSR:(OI)(CI)(F)" /T
```

### Emergency Recovery
```bash
# Restore database
mysql -u root -p naissancechain_prod < backup.sql

# Restore environment
copy .env.backup .env

# Restart services
# Apache + Queue Workers
```

## 📞 Support

### Log Locations
- Laravel: `storage/logs/laravel.log`
- Apache: `C:/wamp64/logs/`
- Queue: `storage/logs/queue-workers.log`
- MySQL: `C:/wamp64/logs/mysql.log`

### Backup Locations
- Database: `C:/wamp64/backups/mysql/`
- Environment: `C:/wamp64/backups/naissancechain/`

### Contact Information
- API Documentation: `/api/documentation`
- Health Check: `/api/v1/health`
- Support: `support@naissancechain.local`

---

## 🎯 Success Metrics

After deployment, verify:
- [ ] API responds within 200ms
- [ ] Memory usage < 512MB
- [ ] Cache hit rate > 80%
- [ ] Zero security vulnerabilities
- [ ] All automated tests passing
- [ ] Queue workers processing jobs
- [ ] Daily backups completing successfully

**Deployment Time**: ~5 minutes
**Expected Uptime**: 99.9%
**Performance Gain**: 50-80% vs development
