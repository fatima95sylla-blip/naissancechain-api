# NaissanceChain - Gestion des Fichiers

## Architecture de Stockage

Système complet de gestion des fichiers pour l'API NaissanceChain avec upload sécurisé et URLs accessibles.

## Configuration Storage

### Disks Configurés:
- **public**: `storage/app/public` → `/storage`
- **logos**: `storage/app/public/logos` → `/storage/logos`
- **documents**: `storage/app/public/documents` → `/storage/documents`

### Commande Artisan:
```bash
php artisan naissancechain:storage-link
```

## Database Schema

### Champs ajoutés à `naissances`:
- `logo_path` (varchar, nullable) - Chemin vers logo
- `document_path` (varchar, nullable) - Chemin vers document

## UploadController

### Endpoints API:

#### POST /api/v1/upload
Upload générique avec type spécifié:
```json
{
  "file": "multipart/form-data",
  "type": "logo|document"
}
```

#### POST /api/v1/upload/logo
Upload spécifique pour logos:
```json
{
  "file": "multipart/form-data"
}
```

#### POST /api/v1/upload/document
Upload spécifique pour documents:
```json
{
  "file": "multipart/form-data"
}
```

#### DELETE /api/v1/upload
Suppression de fichier:
```json
{
  "path": "logos/filename.png"
}
```

#### GET /api/v1/upload/info
Informations fichier:
```json
{
  "path": "logos/filename.png"
}
```

#### GET /api/v1/upload/list
Liste fichiers par répertoire:
```json
{
  "directory": "logos|documents"
}
```

## Validation des Fichiers

### Types autorisés:
- Images: `jpeg`, `jpg`, `png`
- Documents: `pdf`, `doc`, `docx`

### Limites:
- Taille maximale: 5MB
- Noms de fichiers uniques avec timestamp et random

### Nommage automatique:
- Logos: `logo_{timestamp}_{random}.{ext}`
- Documents: `document_{timestamp}_{random}.{ext}`

## Réponses API

### Succès (200):
```json
{
  "success": true,
  "message": "Fichier uploadé avec succès",
  "data": {
    "filename": "logo_1777566626_HZxhnIQPYU.png",
    "path": "logos/logo_1777566626_HZxhnIQPYU.png",
    "url": "/storage/logos/logo_1777566626_HZxhnIQPYU.png",
    "type": "logo",
    "size": 70,
    "mime_type": "image/png"
  }
}
```

### Erreur Validation (422):
```json
{
  "success": false,
  "message": "Erreur de validation du fichier",
  "errors": {
    "file": ["Le format du fichier n'est pas autorisé"],
    "type": ["Le type doit être logo ou document"]
  }
}
```

### Erreur Serveur (500):
```json
{
  "success": false,
  "message": "Erreur lors de l'upload du fichier",
  "data": {
    "error": "Message d'erreur détaillé"
  }
}
```

## Tests d'Upload

### PowerShell Script:
```powershell
# test_upload_curl.ps1
$token = "votre_token"
$apiUrl = "http://localhost:8000/api/v1/upload/logo"
$filePath = "logo.png"

curl.exe -X POST $apiUrl `
  -H "Authorization: Bearer $token" `
  -F "file=@$filePath" `
  -F "type=logo"
```

### cURL Command:
```bash
curl -X POST http://localhost:8000/api/v1/upload/logo \
  -H "Authorization: Bearer TOKEN" \
  -F "file=@logo.png" \
  -F "type=logo"
```

## Sécurité

### Protection:
- Validation stricte des types MIME
- Taille maximale contrôlée
- Noms de fichiers générés aléatoirement
- Stockage isolé par type

### Permissions:
- Authentification requise (Bearer token)
- Accès protégé via middleware
- Logs des opérations d'upload

## Intégration avec NaissanceService

### Utilisation dans création d'acte:
```php
// Après création naissance
if ($request->hasFile('logo')) {
    $upload = $this->uploadController->uploadLogo($request);
    $naissance->logo_path = $upload['path'];
}

if ($request->hasFile('document')) {
    $upload = $this->uploadController->uploadDocument($request);
    $naissance->document_path = $upload['path'];
}

$naissance->save();
```

## Stockage Physique

### Structure des dossiers:
```
storage/
├── app/
│   └── public/
│       ├── logos/
│       │   └── logo_1777566626_HZxhnIQPYU.png
│       └── documents/
│           └── document_1777566626_ABC123XYZ.pdf
public/
└── storage/
    ├── logos/ → storage/app/public/logos
    └── documents/ → storage/app/public/documents
```

### URLs Accessibles:
- Logo: `http://domain.com/storage/logos/filename.png`
- Document: `http://domain.com/storage/documents/filename.pdf`

## Gestion Windows

### Problèmes résolus:
- Permissions symlink → Fallback directories
- mklink avec droits admin → Commande artisan adaptée
- GitIgnore protection → Contournement manuel

### Commandes Windows:
```bash
# Créer dossiers
mkdir storage\app\public\logos
mkdir storage\app\public\documents

# Lien symbolique (admin)
mklink /D public\storage\logos storage\app\public\logos
mklink /D public\storage\documents storage\app\public\documents
```

## Monitoring

### Logs d'upload:
- Succès: fichiers uploadés avec métadonnées
- Erreurs: validation, taille, permissions
- Sécurité: tentatives non autorisées

### Métriques:
- Volume total uploadé
- Types de fichiers par catégorie
- Espace disque utilisé

Le système de gestion des fichiers NaissanceChain est **production-ready** avec sécurité maximale et compatibilité Windows !
