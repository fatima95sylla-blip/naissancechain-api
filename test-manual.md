# 🧪 Tests Manuels NaissanceChain API

## 🚀 Tests Rapides

### 1. Test API Access
```bash
curl http://naissancechain.local/api/v1/
```
**Attendu**: JSON avec status 200

### 2. Test Inscription
```bash
curl -X POST http://naissancechain.local/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "role": "AGENT",
    "telephone": "1234567890",
    "prefecture": "Abidjan",
    "zone": "Zone 1"
  }'
```
**Attendu**: Status 201 avec token

### 3. Test Connexion
```bash
curl -X POST http://naissancechain.local/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "Password123!"
  }'
```
**Attendu**: Status 200 avec token

### 4. Test Profil (remplacer TOKEN)
```bash
curl -X GET http://naissancechain.local/api/v1/me \
  -H "Authorization: Bearer VOTRE_TOKEN"
```
**Attendu**: Status 200 avec infos utilisateur

## 🌐 Tests Navigateur

### 1. URL de base
```
http://naissancechain.local
```

### 2. Documentation API
```
http://naissancechain.local/api/v1/
```

### 3. Health Check
```
http://naissancechain.local/api/v1/health
```

## 📱 Tests Postman

### 1. Importer Collection
- Ouvrir Postman
- File → Import
- Sélectionner `naissancechain-api.postman_collection.json`

### 2. Configurer Environnement
- Variables:
  - `baseUrl`: `http://naissancechain.local/api/v1`
  - `email`: `test@example.com`
  - `password`: `Password123!`

### 3. Tests à Exécuter
1. **Register** → Créer utilisateur
2. **Login** → Obtenir token
3. **Profile** → Vérifier profil
4. **Naissances** → CRUD naissances
5. **Blockchain** → Vérifier enregistrements

## 🔍 Tests Base de Données

### 1. Vérifier Tables
```sql
-- Connecter à MySQL
mysql -u root -p

-- Vérifier base
USE naissancechain_prod;
SHOW TABLES;

-- Vérifier données
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM naissances;
SELECT COUNT(*) FROM jobs;
```

### 2. Test Laravel
```bash
php artisan tinker
```
```php
// Test connexion DB
DB::connection()->getPdo();

// Test models
$user = \App\Models\User::first();
$naissance = \App\Models\Naissance::first();

// Test service
$service = app(\App\Services\NaissanceService::class);
```

## 📊 Tests Performance

### 1. Temps de Réponse
```bash
# Test temps de réponse
time curl -s http://naissancechain.local/api/v1/ > /dev/null
```

### 2. Charge Simple
```bash
# 10 requêtes simultanées
for i in {1..10}; do
  curl -s http://naissancechain.local/api/v1/ > /dev/null &
done
wait
```

### 3. Mémoire
```bash
# Vérifier mémoire PHP
php -i | grep memory_limit
php artisan about --only=cache,database
```

## 🚨 Tests d'Erreur

### 1. Erreur 404
```bash
curl http://naissancechain.local/api/v1/nonexistent
```
**Attendu**: Status 404

### 2. Erreur 401
```bash
curl http://naissancechain.local/api/v1/me
```
**Attendu**: Status 401

### 3. Erreur 422
```bash
curl -X POST http://naissancechain.local/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email": "invalid"}'
```
**Attendu**: Status 422

## 📋 Tests Upload

### 1. Test Upload Fichier
```bash
curl -X POST http://naissancechain.local/api/v1/upload \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -F "file=@test.pdf"
```

### 2. Test Upload Logo
```bash
curl -X POST http://naissancechain.local/api/v1/upload/logo \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -F "logo=@logo.png"
```

## 🔧 Tests Queue Workers

### 1. Vérifier Workers
```bash
php artisan queue:monitor
```

### 2. Test Job
```bash
php artisan tinker
```
```php
// Créer un job test
dispatch(new \App\Jobs\ProcessNaissance(['test' => true]));
```

### 3. Vérifier Jobs
```bash
php artisan queue:failed
php artisan queue:retry all
```

## 📝 Tests Logs

### 1. Vérifier Logs Laravel
```bash
tail -f storage/logs/laravel.log
```

### 2. Vérifier Logs Apache
```bash
tail -f C:/wamp64/logs/apache_error.log
```

### 3. Vérifier Logs MySQL
```bash
tail -f C:/wamp64/logs/mysql.log
```

## ✅ Checklist Validation

### ✅ API Access
- [ ] API accessible via navigateur
- [ ] Health check retourne 200
- [ ] Documentation visible

### ✅ Authentification
- [ ] Inscription fonctionne
- [ ] Connexion fonctionne
- [ ] Token généré
- [ ] Profil accessible avec token

### ✅ CRUD Naissances
- [ ] Lister naissances
- [ ] Créer naissance
- [ ] Mettre à jour naissance
- [ ] Supprimer naissance

### ✅ Blockchain
- [ ] Vérifier enregistrement
- [ ] Statistiques accessibles
- [ ] Historique disponible

### ✅ Upload
- [ ] Upload fichiers fonctionne
- [ ] Types MIME validés
- [ ] Taille limitée

### ✅ Performance
- [ ] Temps réponse < 500ms
- [ ] Mémoire < 512MB
- [ ] Workers actifs

### ✅ Sécurité
- [ ] CORS configuré
- [ ] Headers sécurité présents
- [ ] Erreurs gérées

## 🚨 Dépannage

### Erreur "Connection refused"
```bash
# Vérifier Apache
sc query wampapache

# Vérifier MySQL
sc query wampmysql

# Redémarrer services
net stop wampapache
net start wampapache
```

### Erreur "Database connection failed"
```bash
# Vérifier .env
type .env | findstr DB_

# Tester connexion
php artisan tinker
DB::connection()->getPdo()
```

### Erreur "404 Not Found"
```bash
# Vérifier VirtualHost
type C:/wamp64/bin/apache/apache2.4.54/conf/extra/httpd-vhosts.conf | findstr naissancechain

# Vérifier hosts
type C:/Windows/System32/drivers/etc/hosts | findstr naissancechain
```

### Erreur "500 Internal Server"
```bash
# Vérifier logs
tail -f storage/logs/laravel.log
tail -f C:/wamp64/logs/apache_error.log

# Vérifier permissions
icacls storage /grant "IUSR:(OI)(CI)(F)" /T
```

---

## 🎯 Test Automatisé Complet

**Exécuter le script de test complet:**
```bash
test-deployment.bat
```

Ce script testera automatiquement:
1. Accessibilité API
2. Inscription utilisateur
3. Connexion et token
4. Accès profil
5. Endpoints naissances
6. Endpoints blockchain
7. Base de données
8. Workers queue

**Si tous les tests passent ✓, votre API est prête !**
