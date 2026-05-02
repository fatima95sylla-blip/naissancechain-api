# 🚀 Test Rapide NaissanceChain API

## ⚡ Test en 1 Minute

### 1. Test API Access
```bash
curl http://naissancechain.local/api/v1/
```
✅ Si vous voyez du JSON → API fonctionne

### 2. Test Navigateur
Ouvrir: http://naissancechain.local
✅ Si page se charge → Apache fonctionne

### 3. Test Automatisé
```bash
test-deployment.bat
```
✅ Si tous les tests passent → Déploiement OK

---

## 📋 Test Complet (5 minutes)

### Étape 1: Vérifier Services
```bash
# Apache actif ?
sc query wampapache

# MySQL actif ?
sc query wampmysql
```

### Étape 2: Test API
```bash
# Health check
curl http://naissancechain.local/api/v1/

# Inscription
curl -X POST http://naissancechain.local/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@example.com","password":"Password123!","password_confirmation":"Password123!","role":"AGENT","telephone":"1234567890","prefecture":"Abidjan","zone":"Zone 1"}'
```

### Étape 3: Test Base de Données
```bash
php artisan tinker
DB::connection()->getPdo();
```

### Étape 4: Test Workers
```bash
php artisan queue:monitor
```

---

## 🎯 Résultats Attendus

| Test | Résultat Attendu |
|------|-----------------|
| API Access | JSON avec status 200 |
| Inscription | Token généré |
| Connexion | Status 200 |
| Profil | Données utilisateur |
| Naissances | Liste vide ou données |
| Database | Connexion réussie |
| Workers | Actifs |

---

## 🚨 Si ça ne marche pas

### Erreur "Connection refused"
```bash
# Démarrer WAMP64
net start wampapache
net start wampmysql
```

### Erreur "404 Not Found"
```bash
# Vérifier hosts
echo 127.0.0.1 naissancechain.local >> C:\Windows\System32\drivers\etc\hosts
```

### Erreur "Database connection"
```bash
# Vérifier .env
type .env | findstr DB_
```

### Erreur "500 Internal"
```bash
# Vérifier logs
tail -f storage/logs/laravel.log
```

---

## ✅ Validation Finale

**✓ API accessible via navigateur**
**✓ Postman peut se connecter**
**✓ Inscription/connexion fonctionnent**
**✓ Base de données accessible**
**✓ Workers actifs**

→ **Votre backend est prêt pour l'équipe !** 🎯
