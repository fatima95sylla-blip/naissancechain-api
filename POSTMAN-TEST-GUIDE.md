# 🧪 Guide Complet de Test Postman - NaissanceChain API

## 🚀 Étape 1: Configuration Postman

### 1.1 Créer une Collection
1. Ouvrir Postman
2. Clic sur **"New"** → **"Collection"**
3. Nommer: **"NaissanceChain API"**
4. Description: **"Tests complets API NaissanceChain avec nouveaux champs"**

### 1.2 Créer un Environnement
1. Clic sur **"New"** → **"Environment"**
2. Nommer: **"NaissanceChain Dev"**
3. Ajouter les variables:

| Variable | Valeur Initiale | Description |
|----------|----------------|-------------|
| `baseUrl` | `http://127.0.0.1/api/v1` | URL de base API |
| `adminEmail` | `admin@naissancechain.local` | Email admin |
| `adminPassword` | `Admin123!` | Mot de passe admin |
| `agentEmail` | `agent@naissancechain.local` | Email agent |
| `agentPassword` | `Agent123!` | Mot de passe agent |
| `adminToken` | `{{token}}` | Token admin (auto) |
| `agentToken` | `{{token}}` | Token agent (auto) |

## 🔐 Étape 2: Authentification

### 2.1 Login Admin
**Méthode**: `POST`
**URL**: `{{baseUrl}}/login`
**Headers**:
```
Content-Type: application/json
```
**Body** (raw JSON):
```json
{
    "email": "{{adminEmail}}",
    "password": "{{adminPassword}}"
}
```
**Tests** (Scripts → Tests):
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    if (response.success && response.data.token) {
        pm.environment.set("adminToken", response.data.token);
        pm.collectionVariables.set("adminToken", response.data.token);
        pm.test("Admin login successful", () => {
            pm.expect(response.success).to.be.true;
            pm.expect(response.data.token).to.be.a('string');
        });
    }
} else {
    pm.test("Admin login failed", () => {
        pm.expect(pm.response.code).to.eql(200);
    });
}
```

### 2.2 Login Agent
**Méthode**: `POST`
**URL**: `{{baseUrl}}/login`
**Headers**:
```
Content-Type: application/json
```
**Body** (raw JSON):
```json
{
    "email": "{{agentEmail}}",
    "password": "{{agentPassword}}"
}
```
**Tests**:
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    if (response.success && response.data.token) {
        pm.environment.set("agentToken", response.data.token);
        pm.collectionVariables.set("agentToken", response.data.token);
        pm.test("Agent login successful", () => {
            pm.expect(response.success).to.be.true;
            pm.expect(response.data.token).to.be.a('string');
        });
    }
} else {
    pm.test("Agent login failed", () => {
        pm.expect(pm.response.code).to.eql(200);
    });
}
```

## 👤 Étape 3: Test Profil Utilisateur

### 3.1 Profil Admin
**Méthode**: `GET`
**URL**: `{{baseUrl}}/me`
**Headers**:
```
Authorization: Bearer {{adminToken}}
Content-Type: application/json
```
**Tests**:
```javascript
pm.test("Profil admin accessible", () => {
    pm.expect(pm.response.code).to.eql(200);
    const response = pm.response.json();
    pm.expect(response.success).to.be.true;
    pm.expect(response.data.email).to.eql(pm.environment.get("adminEmail"));
});
```

### 3.2 Profil Agent
**Méthode**: `GET`
**URL**: `{{baseUrl}}/me`
**Headers**:
```
Authorization: Bearer {{agentToken}}
Content-Type: application/json
```
**Tests**:
```javascript
pm.test("Profil agent accessible", () => {
    pm.expect(pm.response.code).to.eql(200);
    const response = pm.response.json();
    pm.expect(response.success).to.be.true;
    pm.expect(response.data.email).to.eql(pm.environment.get("agentEmail"));
});
```

## 📝 Étape 4: Test Création Naissance (NOUVEAUX CHAMPS)

### 4.1 Créer Naissance Complète
**Méthode**: `POST`
**URL**: `{{baseUrl}}/naissances`
**Headers**:
```
Authorization: Bearer {{agentToken}}
Content-Type: application/json
```
**Body** (raw JSON):
```json
{
    "nom_complet_enfant": "TOURE Ibrahim Junior",
    "sexe_enfant": "M",
    "date_naissance_enfant": "2026-05-02",
    "heure_naissance_enfant": "14:30",
    "nom_complet_pere": "TOURE Mamadou Ali",
    "profession_pere": "Ingénieur Informaticien",
    "date_naissance_pere": "1985-03-15",
    "nom_complet_mere": "DIALLO Aicha Fatoumata",
    "profession_mere": "Enseignante",
    "date_naissance_mere": "1988-07-20",
    "ville_region": "Abidjan",
    "quartier_secteur": "Cocody Angré",
    "lieu_naissance_enfant": "Hôpital Donka, Conakry",
    "nom_declarant": "TOURE Mamadou Ali",
    "numero_declarant": "1234567890123456",
    "officier_etat_civil": "M. Barry Mamadou",
    "numero_acte": "ACTE-2026-0001",
    "date_enregistrement": "2026-05-02",
    "latitude": 9.6412,
    "longitude": -13.5784,
    "hors_ligne": false
}
```
**Tests**:
```javascript
pm.test("Création naissance réussie", () => {
    pm.expect(pm.response.code).to.eql(201);
    const response = pm.response.json();
    pm.expect(response.success).to.be.true;
    pm.expect(response.data.nom_complet_enfant).to.eql("TOURE Ibrahim Junior");
    pm.expect(response.data.sexe_enfant).to.eql("M");
    pm.expect(response.data.numero_unique).to.be.a('string');
    
    // Sauvegarder l'ID pour les tests suivants
    if (response.data.id) {
        pm.collectionVariables.set("naissanceId", response.data.id);
        pm.collectionVariables.set("naissanceNumero", response.data.numero_unique);
    }
});

pm.test("Validation champs obligatoires", () => {
    const response = pm.response.json();
    pm.expect(response.data).to.have.property('nom_complet_enfant');
    pm.expect(response.data).to.have.property('sexe_enfant');
    pm.expect(response.data).to.have.property('date_naissance_enfant');
    pm.expect(response.data).to.have.property('heure_naissance_enfant');
    pm.expect(response.data).to.have.property('nom_complet_pere');
    pm.expect(response.data).to.have.property('profession_pere');
    pm.expect(response.data).to.have.property('nom_complet_mere');
    pm.expect(response.data).to.have.property('profession_mere');
    pm.expect(response.data).to.have.property('ville_region');
    pm.expect(response.data).to.have.property('quartier_secteur');
    pm.expect(response.data).to.have.property('lieu_naissance_enfant');
    pm.expect(response.data).to.have.property('nom_declarant');
    pm.expect(response.data).to.have.property('numero_declarant');
});
```

## 📋 Étape 5: Test Lister Naissances

### 5.1 Lister toutes les naissances
**Méthode**: `GET`
**URL**: `{{baseUrl}}/naissances`
**Headers**:
```
Authorization: Bearer {{agentToken}}
Content-Type: application/json
```
**Tests**:
```javascript
pm.test("Liste naissances accessible", () => {
    pm.expect(pm.response.code).to.eql(200);
    const response = pm.response.json();
    pm.expect(response.success).to.be.true;
    pm.expect(response.data).to.be.an('array');
    
    if (response.data.length > 0) {
        pm.expect(response.data[0]).to.have.property('nom_complet_enfant');
        pm.expect(response.data[0]).to.have.property('sexe_enfant');
    }
});

pm.test("Structure réponse correcte", () => {
    const response = pm.response.json();
    pm.expect(response).to.have.property('success');
    pm.expect(response).to.have.property('data');
    pm.expect(response).to.have.property('message');
});
```

## 🔍 Étape 6: Test Détails Naissance

### 6.1 Obtenir une naissance spécifique
**Méthode**: `GET`
**URL**: `{{baseUrl}}/naissances/{{naissanceId}}`
**Headers**:
```
Authorization: Bearer {{agentToken}}
Content-Type: application/json
```
**Tests**:
```javascript
pm.test("Détails naissance accessibles", () => {
    pm.expect(pm.response.code).to.eql(200);
    const response = pm.response.json();
    pm.expect(response.success).to.be.true;
    pm.expect(response.data.id).to.eql(pm.collectionVariables.get("naissanceId"));
    pm.expect(response.data.numero_unique).to.eql(pm.collectionVariables.get("naissanceNumero"));
});
```

## ⛓️ Étape 7: Test Blockchain

### 7.1 Vérifier Blockchain
**Méthode**: `POST`
**URL**: `{{baseUrl}}/blockchain/verify/{{naissanceId}}`
**Headers**:
```
Authorization: Bearer {{adminToken}}
Content-Type: application/json
```
**Tests**:
```javascript
pm.test("Vérification blockchain", () => {
    pm.expect(pm.response.code).to.eql(200);
    const response = pm.response.json();
    pm.expect(response.success).to.be.true;
    pm.expect(response.data).to.have.property('verified');
});
```

### 7.2 Statistiques Blockchain
**Méthode**: `GET`
**URL**: `{{baseUrl}}/blockchain/stats`
**Headers**:
```
Authorization: Bearer {{adminToken}}
Content-Type: application/json
```
**Tests**:
```javascript
pm.test("Statistiques blockchain", () => {
    pm.expect(pm.response.code).to.eql(200);
    const response = pm.response.json();
    pm.expect(response.success).to.be.true;
    pm.expect(response.data).to.have.property('total_records');
    pm.expect(response.data).to.have.property('verified_records');
});
```

## 🚨 Étape 8: Tests d'Erreur

### 8.1 Test Validation Erreur
**Méthode**: `POST`
**URL**: `{{baseUrl}}/naissances`
**Headers**:
```
Authorization: Bearer {{agentToken}}
Content-Type: application/json
```
**Body** (raw JSON - champs manquants):
```json
{
    "nom_complet_enfant": "TEST Erreur",
    "sexe_enfant": "M"
    // Champs obligatoires manquants
}
```
**Tests**:
```javascript
pm.test("Erreur validation champs manquants", () => {
    pm.expect(pm.response.code).to.eql(422);
    const response = pm.response.json();
    pm.expect(response.success).to.be.false;
    pm.expect(response.errors).to.be.an('object');
});
```

### 8.2 Test Non Authentifié
**Méthode**: `GET`
**URL**: `{{baseUrl}}/naissances`
**Headers**:
```
Content-Type: application/json
```
**Tests**:
```javascript
pm.test("Erreur non authentifié", () => {
    pm.expect(pm.response.code).to.eql(401);
    const response = pm.response.json();
    pm.expect(response.success).to.be.false;
});
```

## 🏃‍♂️ Étape 9: Exécution Automatisée

### 9.1 Créer un Test Flow
1. Dans la collection, cliquer sur **"Run"**
2. Sélectionner tous les endpoints dans l'ordre:
   - Login Admin
   - Login Agent
   - Profil Admin
   - Profil Agent
   - Créer Naissance
   - Lister Naissances
   - Détails Naissance
   - Vérifier Blockchain
   - Statistiques Blockchain
3. Cliquer **"Run NaissanceChain API"**

### 9.2 Résultats Attendus
- ✅ **8/9** tests devraient réussir
- ❌ **1/9** test d'erreur devrait échouer (normal)

## 📊 Étape 10: Rapport de Test

### 10.1 Exporter les Résultats
1. Après exécution, cliquer sur **"Export Results"**
2. Choisir format **JSON** ou **HTML**
3. Sauvegarder comme **"NaissanceChain-Test-Report-$(date).json"**

### 10.2 Validation Finale
Le test est **VALIDÉ** si:
- ✅ Tous les endpoints authentifiés fonctionnent
- ✅ Les nouveaux champs sont acceptés
- ✅ Les validations fonctionnent
- ✅ Les erreurs sont gérées
- ✅ La blockchain fonctionne

---

## 🎯 Instructions Rapides

### 1. Démarrer WAMP64
```bash
# Démarrer manuellement l'interface WAMP64
# Clic "Start All Services"
# Attendre icone verte
```

### 2. Exécuter Migration
```bash
run-migration.bat
```

### 3. Créer Utilisateurs
```bash
php artisan tinker
# Copier-coller le code de create_users.php
```

### 4. Lancer Postman
1. Importer la collection
2. Configurer l'environnement
3. Exécuter les tests

**🚀 Votre API NaissanceChain est prête pour les tests Postman !**
