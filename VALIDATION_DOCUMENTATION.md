# NaissanceChain - Validation et Gestion des Erreurs

## Architecture de Validation Robuste

Système complet de validation et gestion d'erreurs standardisé pour l'API NaissanceChain.

## Classes FormRequest

### 1. NaissanceRequest
Validation stricte pour les actes de naissance:

**Champs obligatoires:**
- `nom_enfant`: string, 2-100 caractères
- `prenom_enfant`: string, 2-100 caractères
- `date_naissance`: date, avant aujourd'hui, après 1900
- `lieu_naissance`: string, 3-255 caractères
- `sexe`: enum (M, F)
- `nom_pere`: string, 2-100 caractères
- `prenom_pere`: string, 2-100 caractères
- `nom_mere`: string, 2-100 caractères
- `prenom_mere`: string, 2-100 caractères
- `adresse_parents`: string, 5-255 caractères
- `telephone_parents`: string, 8-20 caractères, regex téléphone
- `declarant_nom`: string, 2-100 caractères
- `declarant_prenom`: string, 2-100 caractères
- `declarant_lien`: enum (Père, Mère, Tuteur, Autre)
- `officier_etat_civil`: string, 3-100 caractères
- `numero_acte`: string, 3-50 caractères, unique
- `date_enregistrement`: date, aujourd'hui ou avant
- `latitude`: numeric, -90 à 90
- `longitude`: numeric, -180 à 180
- `hors_ligne`: boolean (optionnel)

### 2. LoginRequest
Validation pour l'authentification:

**Champs obligatoires:**
- `email`: string, max 255, regex email RFC 5322
- `password`: string, 8-128 caractères

### 3. RegisterRequest
Validation pour l'inscription:

**Champs obligatoires:**
- `name`: string, 2-255 caractères, regex noms
- `email`: string, max 255, unique, regex email
- `password`: string, 8-128 caractères, regex complexe
- `password_confirmation`: string, same:password
- `telephone`: string, 8-20 caractères, regex téléphone
- `prefecture`: string, 2-255 caractères
- `zone`: string, 2-255 caractères
- `role`: enum (ADMIN, AGENT, ECOLE, SANTE)

## ApiResponse Helper

Classe utilitaire pour réponses JSON standardisées:

### Méthodes principales:
- `success($data, $message, $status)` - Succès
- `error($message, $errors, $status)` - Erreur
- `validationError($errors, $message)` - Erreur validation (422)
- `notFound($message)` - Non trouvé (404)
- `unauthorized($message)` - Non autorisé (401)
- `forbidden($message)` - Interdit (403)
- `serverError($message, $data)` - Erreur serveur (500)
- `created($data, $message)` - Créé (201)
- `updated($data, $message)` - Mis à jour
- `deleted($message)` - Supprimé
- `paginate($paginator, $message)` - Pagination
- `custom($success, $message, $data, $status)` - Custom

### Format de réponse:
```json
{
  "success": true|false,
  "message": "Message descriptif",
  "data": {...}, // optionnel
  "errors": {...} // optionnel pour validation
}
```

## Middleware Global HandleApiErrors

Gestion centralisée des exceptions API:

### Exceptions gérées:
- `ValidationException` → 422 avec erreurs détaillées
- `AuthenticationException` → 401
- `AuthorizationException` → 403
- `ModelNotFoundException` → 404
- `NotFoundHttpException` → 404
- `MethodNotAllowedHttpException` → 405
- `AccessDeniedHttpException` → 403
- `QueryException` → 500 avec logs
- `Throwable` → 500 avec logs

### Logging:
- Erreurs de base de données
- Erreurs inattendues
- Réponses d'erreur (warning)
- Contexte complet (request, trace)

## Configuration

### Enregistrement Middleware:
```php
// bootstrap/app.php
$middleware->group('api', [
    \App\Http\Middleware\HandleApiErrors::class,
]);
```

### Utilisation dans Controllers:
```php
use App\Http\Requests\NaissanceRequest;
use App\Helpers\ApiResponse;

class NaissanceController extends Controller
{
    public function store(NaissanceRequest $request)
    {
        try {
            $naissance = $this->naissanceService->create(
                $request->validated(),
                auth()->user()
            );
            
            return ApiResponse::created($naissance, 'Acte de naissance créé');
        } catch (\Exception $e) {
            return ApiResponse::serverError('Erreur lors de la création');
        }
    }
}
```

## Tests de Validation

### Cas de test réussis:
1. **Validation Register**: Champs vides → 422 avec erreurs détaillées
2. **Validation Naissance**: Données invalides → 422 avec messages français
3. **Erreur 404**: Ressource inexistante → 404 standardisé
4. **Erreur 401**: Non authentifié → 401 standardisé
5. **Erreur 403**: Permissions insuffisantes → 403 standardisé

### Exemples de réponses:

**Validation Error (422):**
```json
{
  "success": false,
  "message": "Erreur de validation des données",
  "errors": {
    "nom_enfant": ["Le nom de l'enfant est obligatoire"],
    "email": ["Le format de l'adresse email est invalide"]
  }
}
```

**Success (200):**
```json
{
  "success": true,
  "message": "Opération réussie",
  "data": {
    "id": 1,
    "nom_enfant": "KANTE",
    ...
  }
}
```

**Not Found (404):**
```json
{
  "success": false,
  "message": "Ressource non trouvée"
}
```

## Sécurité

### Protection contre les attaques:
- Validation stricte des types et formats
- Regex pour emails et téléphones
- Limitation de longueurs
- Échappement automatique Laravel
- Logs complets des erreurs

### Messages en français:
- Toutes les erreurs traduites
- Messages clairs et actionnables
- Attributs personnalisés pour meilleure UX

## Avantages

### Standardisation:
- Format JSON uniforme
- Codes HTTP appropriés
- Messages cohérents
- Documentation complète

### Maintenance:
- Centralisation des erreurs
- Logs structurés
- Debug facile
- Évolution simple

### Sécurité:
- Validation en profondeur
- Protection contre injections
- Logs d'audit
- Gestion des exceptions

Le système de validation et gestion d'erreurs NaissanceChain est **production-ready** avec une robustesse maximale !
