# Exemple d'utilisation des middlewares de sécurité

## 1. Utilisation dans les routes

```php
// routes/api.php
use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\SecurityMiddleware;
use App\Http\Middleware\ApiCorsMiddleware;

// Les middlewares sont déjà appliqués globalement au groupe 'api'
Route::post('/auth/register', [AuthController::class, 'register']);

// Application manuelle si nécessaire
Route::middleware(['api.cors', 'security'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
```

## 2. Utilisation dans les contrôleurs

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\SecureRegisterRequest;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function register(SecureRegisterRequest $request)
    {
        // La validation et la sanitization sont automatiques
        $validated = $request->validated();
        
        // Utilisation des données sécurisées
        $user = User::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'data' => $user
        ]);
    }
}
```

## 3. Variables d'environnement à ajouter

```env
# .env
SECURITY_LOG_SQL=true
SECURITY_LOG_RATE_LIMIT=true
SECURITY_LOG_SUSPICIOUS=true

# Configuration CORS
CORS_ALLOWED_ORIGINS=http://localhost:3000,https://naissancechain.com
```

## 4. Logs de sécurité

Les événements de sécurité sont loggés dans `storage/logs/security-YYYY-MM-DD.log` :

```
[2024-01-15 10:30:45] security.WARNING: RATE_LIMIT_EXCEEDED {"ip":"192.168.1.100","attempts":61,"uri":"/api/auth/login","method":"POST","timestamp":"2024-01-15T10:30:45.000Z"}
[2024-01-15 10:31:12] security.WARNING: SUSPICIOUS_REQUEST {"ip":"192.168.1.101","user_agent":"curl/7.68.0","uri":"/api/users","method":"DELETE","timestamp":"2024-01-15T10:31:12.000Z"}
[2024-01-15 10:32:01] security.WARNING: SUSPICIOUS_SQL_QUERY {"sql":"SELECT * FROM users WHERE email = ? UNION SELECT * FROM passwords","bindings":["test@example.com"],"time":15,"connection":"mysql","timestamp":"2024-01-15T10:32:01.000Z"}
```

## 5. Configuration des limites

```php
// config/security.php
'rate_limiting' => [
    'api' => [
        'max_attempts' => 60,        // 60 requêtes/minute
        'decay_minutes' => 1,
        'block_duration' => 15,      // Blocage 15 minutes
    ],
    'auth' => [
        'max_attempts' => 5,         // 5 tentatives/15min
        'decay_minutes' => 15,
        'block_duration' => 30,      // Blocage 30 minutes
    ],
],
```

## 6. Headers de sécurité appliqués

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Strict-Transport-Security: max-age=31536000; includeSubDomains`
- `Content-Security-Policy: default-src 'self'...`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: geolocation=(), microphone=(), camera=()`
