# NaissanceChain API - Exemples d'Utilisation

## Configuration

**Base URL:** `http://localhost:8000/api/v1`

## Endpoints d'Authentification

### 1. Register (Création d'utilisateur)

**Endpoint:** `POST /api/v1/register`

**Request:**
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Barry Mamadou",
    "email": "barry.mamadou@naissancechain.gn",
    "password": "password123",
    "password_confirmation": "password123",
    "telephone": "+224620123456",
    "prefecture": "Conakry",
    "zone": "Dixinn",
    "role": "AGENT"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Inscription réussie",
  "data": {
    "user": {
      "id": 2,
      "name": "Barry Mamadou",
      "email": "barry.mamadou@naissancechain.gn",
      "telephone": "+224620123456",
      "prefecture": "Conakry",
      "zone": "Dixinn",
      "actif": true,
      "created_at": "2026-04-30T14:00:00.000000Z",
      "updated_at": "2026-04-30T14:00:00.000000Z"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

### 2. Login

**Endpoint:** `POST /api/v1/login`

**Request:**
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "barry.mamadou@naissancechain.gn",
    "password": "password123"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "user": {
      "id": 2,
      "name": "Barry Mamadou",
      "email": "barry.mamadou@naissancechain.gn",
      "telephone": "+224620123456",
      "prefecture": "Conakry",
      "zone": "Dixinn",
      "actif": true,
      "role": "AGENT",
      "permissions": [
        "naissances.create",
        "naissances.read",
        "naissances.update",
        "verification.read"
      ],
      "created_at": "2026-04-30T14:00:00.000000Z"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

### 3. Get Profile

**Endpoint:** `GET /api/v1/me`

**Request:**
```bash
curl -X GET http://localhost:8000/api/v1/me \
  -H "Authorization: Bearer 1|abc123..."
```

**Response:**
```json
{
  "success": true,
  "message": "Informations utilisateur récupérées",
  "data": {
    "id": 2,
    "name": "Barry Mamadou",
    "email": "barry.mamadou@naissancechain.gn",
    "telephone": "+224620123456",
    "prefecture": "Conakry",
    "zone": "Dixinn",
    "actif": true,
    "role": "AGENT",
    "permissions": [
      "naissances.create",
      "naissances.read",
      "naissances.update",
      "verification.read"
    ],
    "created_at": "2026-04-30T14:00:00.000000Z"
  }
}
```

### 4. Logout

**Endpoint:** `POST /api/v1/logout`

**Request:**
```bash
curl -X POST http://localhost:8000/api/v1/logout \
  -H "Authorization: Bearer 1|abc123..."
```

**Response:**
```json
{
  "success": true,
  "message": "Déconnexion réussie"
}
```

## Exemples Postman

### Collection Postman

```json
{
  "info": {
    "name": "NaissanceChain API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Auth",
      "item": [
        {
          "name": "Register",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"name\": \"Barry Mamadou\",\n  \"email\": \"barry.mamadou@naissancechain.gn\",\n  \"password\": \"password123\",\n  \"password_confirmation\": \"password123\",\n  \"telephone\": \"+224620123456\",\n  \"prefecture\": \"Conakry\",\n  \"zone\": \"Dixinn\",\n  \"role\": \"AGENT\"\n}"
            },
            "url": {
              "raw": "{{baseUrl}}/api/v1/register",
              "host": ["{{baseUrl}}"],
              "path": ["api", "v1", "register"]
            }
          }
        },
        {
          "name": "Login",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"barry.mamadou@naissancechain.gn\",\n  \"password\": \"password123\"\n}"
            },
            "url": {
              "raw": "{{baseUrl}}/api/v1/login",
              "host": ["{{baseUrl}}"],
              "path": ["api", "v1", "login"]
            }
          }
        },
        {
          "name": "Get Profile",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{baseUrl}}/api/v1/me",
              "host": ["{{baseUrl}}"],
              "path": ["api", "v1", "me"]
            }
          }
        },
        {
          "name": "Logout",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{baseUrl}}/api/v1/logout",
              "host": ["{{baseUrl}}"],
              "path": ["api", "v1", "logout"]
            }
          }
        }
      ]
    }
  ],
  "variable": [
    {
      "key": "baseUrl",
      "value": "http://localhost:8000"
    },
    {
      "key": "token",
      "value": ""
    }
  ]
}
```

## Variables d'Environnement

**.env configuration:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=naissancechain
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost
SESSION_DRIVER=file

# Pour la production
APP_ENV=production
APP_DEBUG=false
```

## Commandes d'Installation

```bash
# Installation complète
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=UserSeeder
php artisan serve
```

## Tests d'Authentification

### Test avec ADMIN
```bash
# Login admin
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@naissancechain.gn",
    "password": "admin123"
  }'
```

### Test avec AGENT
```bash
# Login agent
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "barry.mamadou@naissancechain.gn",
    "password": "agent123"
  }'
```

## Gestion des Erreurs

### Réponse d'erreur standard
```json
{
  "success": false,
  "message": "Message d'erreur",
  "errors": {
    "field": ["Message d'erreur spécifique"]
  }
}
```

### Codes d'erreur
- `401` - Non authentifié
- `403` - Accès interdit (rôle insuffisant)
- `422` - Erreur de validation
- `500` - Erreur serveur

## Middleware de Rôles

Exemple de route protégée par rôle:
```php
Route::middleware(['auth:sanctum', 'role:ADMIN'])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'users']);
});

Route::middleware(['auth:sanctum', 'role:AGENT,ECOLE'])->group(function () {
    Route::post('/naissances', [NaissanceController::class, 'store']);
});
```

## Sécurité

- Tokens Sanctum avec expiration configurable
- Hash des mots de passe avec bcrypt
- Validation des entrées avec FormRequest
- Protection par rôles et permissions
- CORS configuré pour les requêtes cross-origin
