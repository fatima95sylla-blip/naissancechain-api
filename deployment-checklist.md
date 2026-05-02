# NaissanceChain API Deployment Checklist
## WAMP64 Production Setup

### ✅ Pre-Deployment Checks
- [ ] WAMP64 installed and running
- [ ] Apache modules enabled: rewrite, headers, deflate, expires, ssl
- [ ] PHP extensions enabled: opcache, mysql, pdo_mysql, curl, gd, zip, mbstring
- [ ] MySQL database created: `naissancechain_prod`
- [ ] Database user configured with proper permissions
- [ ] Windows hosts file updated with naissancechain.local

### ✅ Configuration Files
- [ ] Copy `env.production.txt` to `.env`
- [ ] Update database credentials in `.env`
- [ ] Generate new APP_KEY: `php artisan key:generate`
- [ ] Configure VirtualHost in Apache
- [ ] Set up OPcache configuration
- [ ] Configure CORS for Flutter

### ✅ Deployment Commands
```bash
# Execute deploy.bat or run manually:
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### ✅ Security Configuration
- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] Secure headers configured in Apache
- [ ] File permissions set for storage/
- [ ] .env file protected from web access
- [ ] SSL certificate configured (if using HTTPS)

### ✅ Performance Optimization
- [ ] OPcache enabled and configured
- [ ] Apache compression enabled
- [ ] Cache headers configured
- [ ] KeepAlive enabled
- [ ] Laravel optimization commands executed

### ✅ Queue Workers
- [ ] Queue table created
- [ ] `start-queue-workers.bat` configured
- [ ] Workers running in background
- [ ] Log rotation configured for queue logs

### ✅ Testing & Validation
- [ ] API accessible at http://naissancechain.local
- [ ] Health check endpoint responding
- [ ] Authentication endpoints working
- [ ] Database connections successful
- [ ] File uploads working
- [ ] Queue jobs processing

### ✅ Monitoring & Logging
- [ ] Laravel logs configured
- [ ] Apache access/error logs configured
- [ ] MySQL backup script scheduled
- [ ] Log rotation set up
- [ ] Error monitoring configured

### ✅ Backup & Recovery
- [ ] MySQL backup script tested
- [ ] Automated backups scheduled
- [ ] Recovery procedures documented
- [ ] Backup retention policy configured

### ✅ Documentation
- [ ] API documentation updated
- [ ] Deployment guide created
- [ ] Troubleshooting guide prepared
- [ ] Team training completed

### 🚨 Critical Security Items
- [ ] Change default passwords
- [ ] Configure firewall rules
- [ ] Enable HTTPS in production
- [ ] Regular security audits scheduled
- [ ] Update dependencies regularly

### 📊 Performance Metrics
- [ ] Response time < 200ms
- [ ] Memory usage < 512MB
- [ ] CPU usage < 50%
- [ ] Database query optimization
- [ ] Cache hit rate > 80%

### 🔄 Maintenance Schedule
- [ ] Daily: MySQL backups
- [ ] Weekly: Log rotation
- [ ] Monthly: Security updates
- [ ] Quarterly: Performance review
- [ ] Annually: Full audit

---

## Emergency Contacts & Procedures
- **Database Issues**: Check MySQL service, run backup restore
- **Apache Issues**: Check error logs, restart service
- **Queue Issues**: Restart workers with `start-queue-workers.bat`
- **Performance**: Clear caches, check OPcache status

## Rollback Procedure
1. Restore database from latest backup
2. Revert `.env` file from backup
3. Clear all Laravel caches
4. Restart Apache and queue workers
5. Verify functionality
