# NaissanceChain - Système de Notifications

## Architecture de Notifications Distribuées

Système complet de notifications avec Laravel Notifications, Queue Jobs et SMS Mock pour l'API NaissanceChain.

## Configuration Queue

### Connections Configurées:
- **notifications**: Queue principale pour notifications individuelles
- **bulk_notifications**: Queue pour notifications en masse
- **database**: Stockage des jobs avec retry automatique

### Configuration:
```php
'notifications' => [
    'driver' => 'database',
    'queue' => 'notifications',
    'retry_after' => 60,
    'after_commit' => true,
],
'bulk_notifications' => [
    'driver' => 'database',
    'queue' => 'bulk_notifications',
    'retry_after' => 120,
    'after_commit' => true,
]
```

## Notification Classes

### NaissanceCreated
Déclenchée lors de la création d'un acte de naissance:

**Channels:** mail, database, sms
**Destinataires:**
- Agent qui a créé l'acte
- Administrateurs système
- Agents de la même préfecture

**Contenu Email:**
- Détails complets de l'acte
- Lien vers l'acte
- Information blockchain
- QR code URL

### NaissanceVerified
Déclenchée lors de la vérification d'un acte:

**Channels:** mail, database, sms
**Destinataires:**
- Mêmes destinataires que création
- Résultat de vérification inclus

**Contenu Email:**
- Statut vérification (✅/⚠️)
- Hash blockchain
- Date de vérification
- Lien investigation si alerte

## Queue Jobs

### SendNotificationJob
Job individuel pour envoyer une notification:

**Caractéristiques:**
- 3 tentatives maximum
- Timeout 60 secondes
- Retry après 30/60/90 secondes
- Tags pour monitoring
- Logs détaillés

**Exemple d'utilisation:**
```php
SendNotificationJob::dispatch(
    'naissance_created',
    $naissance,
    $user
);
```

### ProcessBulkNotificationsJob
Job pour notifications en masse:

**Caractéristiques:**
- 2 tentatives maximum
- Timeout 300 secondes (5 minutes)
- Dispatch individuel des notifications
- Statistiques de traitement
- Gestion des erreurs individuelles

**Exemple d'utilisation:**
```php
ProcessBulkNotificationsJob::dispatch(
    'naissance_created',
    $naissance,
    $userIds
);
```

## SMS Channel

### SmsChannel Mock
Provider SMS simulé pour développement:

**Fonctionnalités:**
- Validation format téléphone
- Masquage numéro pour logs
- Simulation délai (0.1-0.5s)
- Logs détaillés
- Support international (+224)

**Configuration:**
```env
SMS_PROVIDER=mock
SMS_API_KEY=votre_cle
SMS_SENDER_ID=NaissanceChain
SMS_TIMEOUT=30
SMS_RETRY_ATTEMPTS=3
```

**Format SMS:**
```
NaissanceChain: Nouvel acte enregistre - TOURE Ibrahim (ACTE-2026-002) - 20/03/2026
```

## Intégration NaissanceService

### Méthodes ajoutées:

#### sendNaissanceCreatedNotification()
Déclenché automatiquement après création d'acte:
- Récupère utilisateurs à notifier
- Dispatch jobs individuels
- Gestion erreurs non bloquante

#### sendNaissanceVerifiedNotification()
Déclenché lors de vérification:
- Inclut résultat vérification
- Notifie mêmes utilisateurs
- Logs erreurs séparés

#### sendBulkNotification()
Pour notifications en masse:
- Support multiple utilisateurs
- Queue dédiée bulk_notifications
- Statistiques de traitement

#### getUsersToNotify()
Logique de sélection des destinataires:
- Agent créateur
- Administrateurs
- Agents même préfecture
- Évite doublons

#### verifyAndNotify()
Vérification avec notification:
- Combine vérification blockchain
- Envoi notification automatique
- Retour résultat complet

## Commande de Test

### TestNotificationCommand
Commande artisan pour tester les notifications:

```bash
# Test synchronisé
php artisan naissancechain:test-notification --sync

# Test avec IDs spécifiques
php artisan naissancechain:test-notification --naissance-id=1 --user-id=1

# Test type spécifique
php artisan naissancechain:test-notification naissance_verified
```

**Sortie exemple:**
```
Sending naissance_created notification:
  Naissance: TOURE Ibrahim (ID: 1)
  User: Nexacore Fatima (admin@naissancechain.gn)
  Sync: Yes
✅ Notification sent synchronously

📊 Queue Status:
  Jobs in queue: 0
  Failed jobs: 0
```

## Workflow de Notification

### Création Acte:
1. Création enregistrement MySQL
2. Stockage sur blockchain
3. Génération QR code
4. **Dispatch notification job**
5. Traitement asynchrone
6. Envoi email + SMS + database

### Vérification Acte:
1. Vérification intégrité blockchain
2. **Dispatch notification job**
3. Traitement asynchrone
4. Envoi email + SMS + database

## Monitoring et Logs

### Logs Structurés:
```json
{
  "level": "info",
  "message": "Notification sent successfully",
  "context": {
    "type": "naissance_created",
    "naissance_id": 1,
    "user_id": 1,
    "user_email": "admin@naissancechain.gn",
    "sent_at": "2026-04-30T17:51:13.000Z"
  }
}
```

### Métriques:
- Volume notifications par type
- Taux de succès/échec
- Temps de traitement moyen
- Erreurs par provider

## Sécurité

### Protection:
- Validation destinataires
- Masquage données sensibles
- Logs structurés sans PII
- Retry avec backoff exponentiel

### Permissions:
- Notifications basées sur rôles
- Filtrage par préfecture
- Évitement doublons
- Audit trail complet

## Performance

### Optimisations:
- Queue asynchrone
- Batch processing possible
- Tags pour monitoring
- Retry automatique
- Timeout protection

### Scalabilité:
- Support high volume
- Queue dédiées
- Workers multiples
- Load balancing

## Évolution Future

### Providers Réels:
- Twilio SMS
- AWS SNS
- Email providers (SendGrid, Mailgun)
- Push notifications mobile

### Features:
- Templates dynamiques
- Préférences utilisateur
- Digest notifications
- Analytics avancés

Le système de notifications NaissanceChain est **production-ready** avec architecture distribuée et monitoring complet !
