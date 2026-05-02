# NaissanceChain API Documentation Guide

## 🚀 Documentation Swagger/OpenAPI Complète

Système de documentation API complet avec Swagger/OpenAPI pour NaissanceChain.

## 📋 Configuration Swagger

### Fichiers de Configuration:
- **`config/l5-swagger.php`** - Configuration principale Swagger
- **Routes web** - URLs d'accès à la documentation

### URLs d'Accès:
- **Documentation principale**: `http://localhost:8000/api/documentation`
- **Documentation raccourcie**: `http://localhost:8000/api/docs`

## 📚 Annotations API Complètes

### 1. Authentification (`AuthController`)

#### Endpoints Documentés:
- **POST** `/auth/register` - Inscription utilisateur
- **POST** `/auth/login` - Connexion utilisateur  
- **POST** `/auth/logout` - Déconnexion utilisateur

#### Exemples Requêtes/Réponses:

**Inscription:**
```bash
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Réponse Succès (201):**
```json
{
  "success": true,
  "message": "Inscription réussie",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2026-04-30T10:00:00Z"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer"
  }
}
```

**Connexion:**
```bash
POST /api/v1/auth/login
Content-Type: application/json
Authorization: Bearer {token}

{
  "email": "john@example.com",
  "password": "password123"
}
```

### 2. Naissances (`NaissanceController`)

#### Endpoints Documentés:
- **POST** `/naissances` - Créer acte de naissance
- **GET** `/naissances/{id}` - Voir acte spécifique

#### Exemples Requêtes/Réponses:

**Création Acte:**
```bash
POST /api/v1/naissances
Content-Type: application/json
Authorization: Bearer {token}

{
  "nom_enfant": "TOURE",
  "prenom_enfant": "Ibrahim",
  "date_naissance": "2026-03-20",
  "lieu_naissance": "Hôpital Donka, Conakry",
  "sexe": "M",
  "nom_pere": "TOURE",
  "prenom_pere": "Mamadou",
  "nom_mere": "DIALLO",
  "prenom_mere": "Aicha",
  "declarant_nom": "TOURE",
  "declarant_prenom": "Mamadou",
  "declarant_lien": "Père",
  "officier_etat_civil": "M. Barry",
  "numero_acte": "ACTE-2026-002",
  "date_enregistrement": "2026-04-30T10:00:00Z",
  "latitude": 9.6412,
  "longitude": -13.5784,
  "hors_ligne": false
}
```

**Réponse Succès (201):**
```json
{
  "success": true,
  "message": "Acte de naissance créé avec succès",
  "data": {
    "id": 1,
    "numero_unique": "NC-2026-000001",
    "nom_enfant": "TOURE",
    "prenom_enfant": "Ibrahim",
    "date_naissance": "2026-03-20",
    "lieu_naissance": "Hôpital Donka, Conakry",
    "sexe": "M",
    "numero_acte": "ACTE-2026-002",
    "statut": "valide",
    "hash_sha256": "abc123def456789...",
    "qr_code_url": "/storage/qr/abc123.png",
    "created_at": "2026-04-30T10:00:00Z",
    "agent": {
      "id": 1,
      "nom": "BARRY",
      "prenom": "Mamadou"
    }
  }
}
```

### 3. Blockchain (`BlockchainController`)

#### Endpoints Documentés:
- **POST** `/blockchain/store/{id}` - Stocker sur blockchain
- **GET** `/blockchain/verify/{id}` - Vérifier intégrité

#### Exemples Requêtes/Réponses:

**Stockage Blockchain:**
```bash
POST /api/v1/blockchain/store/1
Authorization: Bearer {token}
```

**Réponse Succès (200):**
```json
{
  "success": true,
  "message": "Enregistrement blockchain créé avec succès",
  "data": {
    "naissance_id": 1,
    "blockchain_record_id": 1,
    "hash": "abc123def456...",
    "block_number": 1,
    "timestamp": "2026-04-30T10:00:00Z",
    "previous_hash": null
  }
}
```

**Vérification Intégrité:**
```bash
GET /api/v1/blockchain/verify/1
```

**Réponse Succès (200):**
```json
{
  "success": true,
  "message": "Vérification réussie",
  "data": {
    "verified": true,
    "message": "L'intégrité de l'acte est vérifiée",
    "hash": "abc123def456...",
    "block_number": 1,
    "timestamp": "2026-04-30T10:00:00Z",
    "previous_hash": null
  }
}
```

### 4. Synchronisation (`SyncController`)

#### Endpoints Documentés:
- **POST** `/sync` - Synchroniser données hors ligne
- **GET** `/sync/pending` - Voir enregistrements en attente

#### Exemples Requêtes/Réponses:

**Synchronisation:**
```bash
POST /api/v1/sync
Content-Type: application/json
Authorization: Bearer {token}

{
  "naissances": [
    {
      "nom_enfant": "TOURE",
      "prenom_enfant": "Ibrahim",
      "date_naissance": "2026-03-20",
      "lieu_naissance": "Hôpital Donka, Conakry",
      "sexe": "M",
      "numero_acte": "ACTE-2026-002",
      "latitude": 9.6412,
      "longitude": -13.5784,
      "created_at": "2026-04-30T09:00:00Z",
      "hash_temporaire": "temp123..."
    }
  ]
}
```

**Réponse Succès (200):**
```json
{
  "success": true,
  "message": "Synchronisation terminée",
  "data": {
    "total": 1,
    "synchronized": 1,
    "failed": 0,
    "results": [
      {
        "success": true,
        "naissance_id": 1,
        "message": "Synchronisé"
      }
    ]
  }
}
```

## 🏷️ Tags Documentation

Les endpoints sont organisés par tags pour une navigation facile:

- **Authentification** - Gestion des utilisateurs
- **Naissances** - Gestion des actes de naissance
- **Blockchain** - Opérations blockchain
- **Synchronisation** - Synchronisation hors ligne

## 🔐 Sécurité API

### Authentification Bearer Token:
```bash
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### Types de Sécurité:
- **BearerAuth** - JWT Token requis pour endpoints protégés
- **Public** - Endpoints publics (login, register)

## 📊 Modèles de Données

### Schema Naissance:
```json
{
  "id": "integer",
  "numero_unique": "string",
  "nom_enfant": "string",
  "prenom_enfant": "string",
  "date_naissance": "date",
  "lieu_naissance": "string",
  "sexe": "enum(M,F)",
  "numero_acte": "string",
  "statut": "enum(valide,en_attente,synchronise,annule)",
  "hash_sha256": "string",
  "qr_code_url": "string",
  "created_at": "datetime",
  "updated_at": "datetime",
  "agent": "object"
}
```

## 🎯 Utilisation Swagger UI

### Fonctionnalités:
- **Try it out** - Tester endpoints directement
- **Authentication** - Configurer token Bearer
- **Parameters** - Remplir paramètres requis
- **Responses** - Voir formats de réponse
- **Schemas** - Explorer modèles de données

### Navigation:
1. Accéder à `http://localhost:8000/api/documentation`
2. Choisir un tag dans le menu
3. Cliquer sur un endpoint
4. Cliquer "Try it out"
5. Remplir les paramètres
6. Exécuter la requête

## 🔧 Configuration Personnalisée

### Variables d'Environnement:
```env
L5_SWAGGER_GENERATE_ALWAYS=false
L5_SWAGGER_GENERATE_YAML_COPY=false
L5_SWAGGER_CONST_HOST=http://localhost:8000
```

### Options d'Affichage:
- **Default Models Expand Depth**: 2
- **Try It Out Enabled**: true
- **Doc Expansion**: none
- **Filter**: true

## 📝 Génération Documentation

### Commandes Artisan:
```bash
# Générer documentation
php artisan l5-swagger:generate

# Publier assets
php artisan vendor:publish --tag=l5-swagger

# Installer package
composer require darkaonline/l5-swagger
```

## 🌐 Déploiement Documentation

### Production:
- URL: `https://votre-domaine.com/api/documentation`
- Sécurisé avec HTTPS
- Token JWT requis

### Développement:
- URL: `http://localhost:8000/api/documentation`
- Mode développement activé
- Debug information disponible

La documentation API NaissanceChain est **complète** et **production-ready** avec Swagger/OpenAPI ! 🚀
