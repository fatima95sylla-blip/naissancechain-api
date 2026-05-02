# NaissanceChain - Module Blockchain

## Architecture Blockchain Hybride

Système blockchain interne pour garantir l'intégrité des actes de naissance sans complexité inutile.

## Principe de Fonctionnement

### 1. Hash SHA-256
- Basé sur: `nom_enfant|prenom_enfant|date_naissance|lieu_naissance|sexe|parents|date_enregistrement`
- Stocké en base de données + blockchain
- Non réversible et unique

### 2. Chaînage Interne
- Chaque enregistrement référence le précédent
- Structure: `hash_actuel` dépend de `hash_precedent`
- Création d'une blockchain interne sécurisée

## Endpoints API

### POST /api/v1/blockchain/store/{id}
Stocke un acte sur la blockchain
```json
{
  "success": true,
  "message": "Enregistrement blockchain créé avec succès",
  "data": {
    "naissance_id": 1,
    "blockchain_record_id": 1,
    "hash": "abc123...",
    "block_number": 1,
    "timestamp": "2026-04-30T14:00:00",
    "previous_hash": "0"
  }
}
```

### GET /api/v1/blockchain/verify/{id}
Vérifie l'intégrité d'un acte
```json
{
  "success": true,
  "message": "Vérification blockchain terminée",
  "data": {
    "status": "verified",
    "message": "Données intactes et vérifiées",
    "verified": true,
    "block_number": 1,
    "timestamp": "2026-04-30T14:00:00",
    "hash": "abc123..."
  }
}
```

### GET /api/v1/blockchain/stats
Statistiques blockchain
```json
{
  "success": true,
  "message": "Statistiques blockchain récupérées",
  "data": {
    "total_records": 10,
    "verified_records": 10,
    "latest_block_number": 10,
    "chain_integrity": true
  }
}
```

### GET /api/v1/blockchain/chain
Chaîne blockchain complète
```json
{
  "success": true,
  "message": "Chaîne blockchain complète",
  "data": [
    {
      "block_number": 1,
      "hash": "abc123...",
      "previous_hash": "0",
      "timestamp": "2026-04-30T14:00:00",
      "naissance_id": 1,
      "verified": true
    }
  ]
}
```

## Services

### BlockchainService

**Méthodes principales:**
- `generateHash(Naissance $naissance): string` - Génère hash SHA-256
- `storeOnChain(Naissance $naissance): BlockchainRecord` - Stocke sur blockchain
- `verifyIntegrity(Naissance $naissance): array` - Vérifie intégrité
- `getStats(): array` - Statistiques blockchain
- `verifyChainIntegrity(): bool` - Vérifie chaîne complète

## Sécurité

### Protection Anti-Falsification
- Hash non réversible
- Chaînage immuable
- Détection immédiate de modifications
- Logs de toutes les opérations

### Intégrité des Données
- Recalcul hash à chaque vérification
- Validation du chaînage
- Timestamps immuables
- Signatures de données

## Base de Données

### Table blockchain_records
```sql
CREATE TABLE blockchain_records (
    id BIGINT PRIMARY KEY,
    naissance_id BIGINT FOREIGN KEY,
    hash VARCHAR(64) UNIQUE,
    previous_hash VARCHAR(64),
    timestamp TIMESTAMP,
    block_number INTEGER,
    data_signature VARCHAR(64),
    verified BOOLEAN DEFAULT 1
);
```

## Intégration avec NaissanceService

### Création d'Acte
1. Création enregistrement MySQL
2. Génération hash SHA-256
3. Stockage sur blockchain
4. Génération QR code

### Vérification
1. Récupération enregistrement
2. Vérification blockchain
3. Validation chaînage
4. Retour statut

## Tests d'Intégrité

### Cas de Test
```bash
# Créer un acte
POST /api/v1/naissances
{
  "nom_enfant": "KANTE",
  "prenom_enfant": "Mariam",
  ...
}

# Vérifier blockchain
GET /api/v1/blockchain/verify/1

# Statistiques
GET /api/v1/blockchain/stats

# Chaîne complète
GET /api/v1/blockchain/chain
```

## Évolutivité

### Préparation Blockchain Externe
- Structure compatible Ethereum
- Export de hash vers smart contracts
- Bridge vers blockchains publiques
- Maintien compatibilité descendante

### Scalabilité
- Indexation optimisée
- Requêtes paginées
- Cache des vérifications
- Logs structurés

## Monitoring

### Logs d'Opérations
- Création de blocs
- Vérifications
- Tentatives de falsification
- Statistiques de performance

### Alertes
- Chaîne corrompue
- Echecs de vérification
- Performance dégradée
- Accès non autorisés

## Conclusion

Module blockchain **production-ready** offrant:
- Sécurité maximale sans complexité
- Intégrité garantie des actes
- Évolutivité vers blockchain externe
- Monitoring complet
- API REST standardisée
