# NaissanceChain API Documentation

## Overview

NaissanceChain est une API REST pour l'enregistrement numérique des actes de naissance avec QR code et vérification en ligne. L'API utilise Laravel Sanctum pour l'authentification et supporte la synchronisation hors ligne.

## Base URL

```
http://localhost:8000/api/v1
```

## Authentication

L'API utilise Laravel Sanctum avec Bearer Tokens. Pour accéder aux endpoints protégés, incluez le token dans l'en-tête Authorization:

```
Authorization: Bearer {token}
```

## Endpoints

### Authentification

#### POST /api/v1/login
Authentifie un agent et retourne un token d'accès.

**Request Body:**
```json
{
  "email": "agent@naissancechain.gn",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "agent": {
      "id": 1,
      "nom": "Barry",
      "prenom": "Mamadou",
      "email": "mamadou@naissancechain.gn",
      "role": "agent",
      "prefecture": "Conakry",
      "actif": true
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

#### POST /api/v1/logout
Déconnecte l'agent (requiert authentification).

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Déconnexion réussie"
}
```

#### GET /api/v1/me
Retourne les informations de l'agent authentifié (requiert authentification).

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nom": "Barry",
    "prenom": "Mamadou",
    "email": "agent@naissancechain.gn",
    "role": "agent",
    "prefecture": "Conakry",
    "actif": true,
    "derniere_connexion": "2026-04-30T13:00:00.000000Z"
  }
}
```

### Actes de Naissance

#### POST /api/v1/naissances
Crée un nouvel acte de naissance (requiert authentification).

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "nom_enfant": "Sylla",
  "prenom_enfant": "Khadija",
  "date_naissance": "2026-04-15",
  "lieu_naissance": "Hôpital Ignace Deen, Conakry",
  "heure_naissance": "14:30",
  "sexe": "F",
  "nom_pere": "Sylla",
  "prenom_pere": "Moussa",
  "profession_pere": "Informaticien",
  "nom_mere": "Sylla",
  "prenom_mere": "Fatoumata",
  "profession_mere": "Styliste",
  "adresse_parents": "Washington, petit simbaya, Conakry",
  "telephone_parents": "+224625238709",
  "declarant_nom": "Sylla",
  "declarant_prenom": "Moussa",
  "declarant_lien": "Père",
  "officier_etat_civil": "M. Bah",
  "numero_acte": "ACTE-2026-001",
  "date_enregistrement": "2026-04-30",
  "latitude": 9.6412,
  "longitude": -13.5784,
  "hors_ligne": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "Acte de naissance créé avec succès",
  "data": {
    "id": 1,
    "numero_unique": "NC-2026-ABC12345",
    "nom_enfant": "Sylla",
    "prenom_enfant": "Khadija",
    "date_naissance": "2026-04-15",
    "lieu_naissance": "Hôpital Ignace Deen, Conakry",
    "sexe": "F",
    "statut": "valide",
    "qr_code_url": "http://localhost:8000/storage/qrcodes/qr-abc123.svg",
    "created_at": "2026-04-30T13:00:00.000000Z",
    "agent": {
      "id": 1,
      "nom": "Barry",
      "prenom": "Mamadou",
      "role": "agent"
    }
  }
}
```

#### GET /api/v1/naissances
Retourne la liste des actes de naissance de l'agent authentifié (requiert authentification).

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "numero_unique": "NC-2026-ABC12345",
      "nom_enfant": "Sylla",
      "prenom_enfant": "Khadija",
      "date_naissance": "2026-04-15",
      "statut": "valide",
      "created_at": "2026-04-30T13:00:00.000000Z"
    }
  ]
}
```

#### GET /api/v1/naissances/{id}
Retourne les détails d'un acte de naissance spécifique (requiert authentification).

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "numero_unique": "NC-2026-ABC12345",
    "nom_enfant": "Sylla",
    "prenom_enfant": "Khadija",
    "date_naissance": "2026-04-15",
    "lieu_naissance": "Hôpital Ignace Deen, Conakry",
    "sexe": "F",
    "statut": "valide",
    "qr_code_url": "http://localhost:8000/storage/qrcodes/qr-abc123.svg",
    "agent": {
      "id": 1,
      "nom": "Barry",
      "prenom": "Mamadou",
      "role": "agent"
    }
  }
}
```

### Vérification

#### GET /api/v1/verification/{numero}
Vérifie l'authenticité d'un acte de naissance par son numéro unique (public).

**Response:**
```json
{
  "success": true,
  "message": "Acte de naissance valide et authentique",
  "data": {
    "naissance": {
      "id": 1,
      "numero_unique": "NC-2026-ABC12345",
      "nom_enfant": "Sylla",
      "prenom_enfant": "Khadija",
      "date_naissance": "2026-04-15",
      "statut": "valide"
    },
    "valide": true,
    "qr_code_url": "http://localhost:8000/storage/qrcodes/qr-abc123.svg",
    "verification_url": "http://localhost:8000/api/v1/verification/NC-2026-ABC12345/show"
  }
}
```

#### GET /api/v1/verification/{numero}/show
Retourne les informations publiques d'un acte de naissance (public).

**Response:**
```json
{
  "success": true,
  "data": {
    "numero_unique": "NC-2026-ABC12345",
    "nom_enfant": "Sylla",
    "prenom_enfant": "Khadija",
    "date_naissance": "2026-04-15",
    "lieu_naissance": "Hôpital Ignace Deen, Conakry",
    "sexe": "F",
    "date_enregistrement": "2026-04-30",
    "officier_etat_civil": "M. Bah",
    "qr_code_url": "http://localhost:8000/storage/qrcodes/qr-abc123.svg",
    "logo_url": "http://localhost:8000/storage/logos/logo_naissancechain.png",
    "valide": true
  }
}
```

### Synchronisation

#### POST /api/v1/sync
Synchronise les données hors ligne (requiert authentification).

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "naissances": [
    {
      "id": 1,
      "numero_unique": "NC-2026-ABC12345",
      "nom_enfant": "KANTE",
      "prenom_enfant": "Mariam",
      "date_naissance": "2026-03-15",
      "lieu_naissance": "Hôpital Central de Kindia",
      "sexe": "F",
      "nom_pere": "KANTE",
      "prenom_pere": "Amadou",
      "nom_mere": "DIALLO",
      "prenom_mere": "Aminatou",
      "adresse_parents": "123 Rue Principale, Kindia",
      "declarant_nom": "KANTE",
      "declarant_prenom": "Amadou",
      "declarant_lien": "Père",
      "officier_etat_civil": "M. Sow",
      "numero_acte": "ACTE-2026-001",
      "date_enregistrement": "2026-04-30",
      "agent_id": 1,
      "hash_sha256": "abc123...",
      "hash_precedent": null
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Synchronisation terminée",
  "data": {
    "success": 1,
    "failed": 0,
    "errors": []
  }
}
```

#### GET /api/v1/sync/pending
Retourne les enregistrements en attente de synchronisation (requiert authentification).

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "numero_unique": "NC-2026-ABC12345",
      "statut": "en_attente",
      "hors_ligne": true,
      "created_at": "2026-04-30T13:00:00.000000Z"
    }
  ]
}
```

## Codes d'Erreur

| Code | Description |
|------|-------------|
| 200 | Succès |
| 201 | Créé |
| 204 | Aucun contenu |
| 400 | Requête invalide |
| 401 | Non authentifié |
| 403 | Accès interdit |
| 404 | Ressource non trouvée |
| 422 | Erreur de validation |
| 500 | Erreur serveur |

## Format des Réponses

Toutes les réponses suivent ce format standard:

**Succès:**
```json
{
  "success": true,
  "message": "Message de succès",
  "data": {}
}
```

**Erreur:**
```json
{
  "success": false,
  "message": "Message d'erreur",
  "errors": {}
}
```

## Installation

1. Clonez le repository
2. Installez les dépendances: `composer install`
3. Configurez le fichier `.env`
4. Générez la clé d'application: `php artisan key:generate`
5. Lancez les migrations: `php artisan migrate`
6. Démarrez le serveur: `php artisan serve`

## Configuration

Variables d'environnement requises dans `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=naissancechain
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost
SESSION_DRIVER=file
```

## Sécurité

- Les mots de passe sont hashés avec bcrypt
- Les tokens d'authentification expirent après 24 heures
- Validation des entrées côté serveur
- Protection CSRF activée
- CORS configuré pour les requêtes cross-origin
