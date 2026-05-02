# Tests Automatisés NaissanceChain

## Structure des Tests

```
tests/
├── Feature/
│   ├── Auth/
│   │   └── AuthControllerTest.php      # Tests Authentification
│   ├── Naissance/
│   │   └── NaissanceControllerTest.php # Tests Naissance
│   ├── Blockchain/
│   │   └── BlockchainControllerTest.php # Tests Blockchain
│   └── Api/
│       └── ApiEndpointsTest.php        # Tests API Endpoints
├── Unit/
│   ├── Models/
│   │   ├── UserTest.php               # Tests Modèle User
│   │   └── NaissanceTest.php          # Tests Modèle Naissance
│   └── Services/
│       ├── AuthServiceTest.php       # Tests Service Auth
│       └── NaissanceServiceTest.php    # Tests Service Naissance
├── Integration/
│   └── CompleteWorkflowTest.php       # Tests Workflow Complet
└── TestCase.php                       # TestCase de base
```

## Commandes d'Exécution

### Exécuter tous les tests
```bash
php artisan test
```

### Tests par catégorie
```bash
# Tests Authentification
php artisan test --filter AuthControllerTest

# Tests Naissance
php artisan test --filter NaissanceControllerTest

# Tests Blockchain
php artisan test --filter BlockchainControllerTest

# Tests API
php artisan test --filter ApiEndpointsTest

# Tests Unitaires
php artisan test --testsuite=Unit

# Tests Feature
php artisan test --testsuite=Feature

# Tests Intégration
php artisan test --filter CompleteWorkflowTest
```

### Tests avec couverture
```bash
# Couverture complète
php artisan test --coverage

# Couverture HTML
php artisan test --coverage-html reports/coverage

# Couverture par fichier
php artisan test --coverage-text --min=80
```

### Tests parallèles
```bash
# 8 processus
php artisan test --parallel --processes=8

# Configuration automatique
php artisan test --parallel
```

### Tests avec variables d'environnement
```bash
# Environnement de test
php artisan test --env=testing

# Base de données SQLite
php artisan test --env=testing --database=sqlite
```

## Tests Couverts

### 1. Authentification ✅
- **Inscription** : validation, création utilisateur, token JWT
- **Connexion** : authentification, gestion erreurs
- **Déconnexion** : révocation tokens
- **Profil** : accès aux données utilisateur
- **Sécurité** : rate limiting, validation entrées

### 2. Naissance ✅
- **Création** : validation, upload fichiers, hash blockchain
- **Lecture** : liste, détail, permissions utilisateur
- **Mise à jour** : modification, validation, permissions
- **Suppression** : suppression, permissions, nettoyage
- **Fichiers** : validation types, stockage sécurisé

### 3. Blockchain ✅
- **Vérification** : validation hash, statut transaction
- **Informations** : détails bloc, données transaction
- **Historique** : liste transactions, pagination
- **Statistiques** : métriques réseau, état système
- **Batch** : vérifications multiples, résumé

### 4. API Endpoints ✅
- **Format** : structure JSON cohérente
- **Headers** : CORS, sécurité, content-type
- **Pagination** : format Laravel standard
- **Erreurs** : 401, 403, 404, 422, 429, 500
- **Validation** : format erreurs standardisé

### 5. Modèles ✅
- **User** : relations, tokens, validation
- **Naissance** : scopes, casting, relations
- **Factory** : données de test réalistes

### 6. Services ✅
- **AuthService** : logique métier authentification
- **NaissanceService** : logique métier naissances
- **Validation** : règles métier, gestion erreurs

### 7. Intégration ✅
- **Workflow complet** : registration → naissance → blockchain
- **Isolation utilisateurs** : permissions multi-utilisateurs
- **Gestion erreurs** : scénarios d'échec
- **Sécurité** : XSS, rate limiting, validation

## Configuration

### phpunit.xml
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="BCRYPT_ROUNDS" value="4"/>
<env name="CACHE_DRIVER" value="array"/>
<env name="SESSION_DRIVER" value="array"/>
<env name="QUEUE_CONNECTION" value="sync"/>
<env name="MAIL_MAILER" value="array"/>
```

### .env.testing
```env
APP_ENV=testing
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
SECURITY_LOG_SQL=false
SECURITY_LOG_RATE_LIMIT=false
SECURITY_LOG_SUSPICIOUS=false
```

## Helpers TestCase

### createTestUser()
```php
$user = $this->createTestUser([
    'email' => 'custom@example.com',
    'name' => 'Custom Name'
]);
```

### createAuthenticatedUser()
```php
$auth = $this->createAuthenticatedUser();
// $auth['user'], $auth['token'], $auth['headers']
```

### assertApiResponse()
```php
$this->assertApiResponse($response, 201, true);
```

### assertValidationError()
```php
$this->assertValidationError($response, 'email');
```

## CI/CD Integration

### GitHub Actions
```yaml
- name: Run Tests
  run: |
    composer install
    cp .env.testing .env
    php artisan key:generate
    php artisan migrate:fresh
    php artisan test --coverage
```

### GitLab CI
```yaml
test:
  script:
    - composer install
    - cp .env.testing .env
    - php artisan key:generate
    - php artisan migrate:fresh
    - php artisan test --coverage
  coverage: '/Coverage: \d+\.\d+%/'
```

## Rapports

### Couverture de code
```bash
php artisan test --coverage-html storage/reports/coverage
```

### Rapports de performance
```bash
php artisan test --profile
```

### Tests de charge
```bash
php artisan test --filter PerformanceTest
```

## Bonnes Pratiques

1. **Isolation** : Chaque test indépendant
2. **Fixtures** : Données prévisibles avec factories
3. **Assertions** : Vérifications précises
4. **Nettoyage** : RefreshDatabase automatique
5. **Mocking** : Services externes simulés
6. **Couverture** : Minimum 80% visé

## Débogage

### Test spécifique
```bash
php artisan test --filter test_user_can_register_successfully
```

### Mode verbose
```bash
php artisan test -v
```

### Stopper à la première erreur
```bash
php artisan test --stop-on-failure
```

### Exécuter en mode debug
```bash
php artisan test --debug
```
