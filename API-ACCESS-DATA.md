# 🚀 NaissanceChain API - Données d'Accès

## 🌐 URLs API

### Base URL
```
http://naissancechain.local/api/v1
```

### Endpoints Principaux
```
Authentification:
POST http://naissancechain.local/api/v1/register
POST http://naissancechain.local/api/v1/login
POST http://naissancechain.local/api/v1/logout
GET  http://naissancechain.local/api/v1/me

Naissance:
GET  http://naissancechain.local/api/v1/naissances
POST http://naissancechain.local/api/v1/naissances
GET  http://naissancechain.local/api/v1/naissances/{id}
PUT  http://naissancechain.local/api/v1/naissances/{id}
DELETE http://naissancechain.local/api/v1/naissances/{id}

Blockchain:
POST http://naissancechain.local/api/v1/blockchain/verify/{id}
GET  http://naissancechain.local/api/v1/blockchain/stats
GET  http://naissancechain.local/api/v1/blockchain/records

Upload:
POST http://naissancechain.local/api/v1/upload
GET  http://naissancechain.local/api/v1/upload/list
```

## 👤 Comptes Utilisateurs

### Compte Administrateur
```
Email:    admin@naissancechain.local
Password: Admin123!
Role:     ADMIN
```

### Compte Agent Test
```
Email:    agent@naissancechain.local
Password: Agent123!
Role:     AGENT
```

### Compte École Test
```
Email:    ecole@naissancechain.local
Password: Ecole123!
Role:     ECOLE
```

### Compte Santé Test
```
Email:    sante@naissancechain.local
Password: Sante123!
Role:     SANTE
```

## 🧪 Compte de Test (Créer via API)

### Inscription Manuel
```bash
curl -X POST http://naissancechain.local/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "role": "AGENT",
    "telephone": "1234567890",
    "prefecture": "Abidjan",
    "zone": "Zone 1"
  }'
```

## 🔐 Rôles Disponibles

### ADMIN
- Accès complet à tous les endpoints
- Gestion des utilisateurs
- Administration système

### AGENT
- CRUD naissances
- Vérifications blockchain
- Upload documents

### ECOLE
- Consultation naissances
- Vérifications limitées
- Upload documents école

### SANTE
- Consultation naissances
- Vérifications médicales
- Upload documents santé

## 📱 Configuration Flutter

### Constants
```dart
class ApiConstants {
  static const String baseUrl = 'http://naissancechain.local/api/v1';
  
  // Comptes de test
  static const String adminEmail = 'admin@naissancechain.local';
  static const String adminPassword = 'Admin123!';
  static const String agentEmail = 'agent@naissancechain.local';
  static const String agentPassword = 'Agent123!';
}
```

### Service HTTP
```dart
class ApiService {
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('${ApiConstants.baseUrl}/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'email': email, 'password': password}),
    );
    return jsonDecode(response.body);
  }
}
```

## 🌐 Configuration Web

### JavaScript/React
```javascript
const API_CONFIG = {
  baseURL: 'http://naissancechain.local/api/v1',
  users: {
    admin: { email: 'admin@naissancechain.local', password: 'Admin123!' },
    agent: { email: 'agent@naissancechain.local', password: 'Agent123!' },
  }
};
```

## 🧪 Tests Rapides

### Test Connexion Admin
```bash
curl -X POST http://naissancechain.local/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@naissancechain.local", "password": "Admin123!"}'
```

### Test Connexion Agent
```bash
curl -X POST http://naissancechain.local/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email": "agent@naissancechain.local", "password": "Agent123!"}'
```

### Test Profil (avec token)
```bash
curl -X GET http://naissancechain.local/api/v1/me \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

## 📋 Postman Collection

### Environment Variables
```
baseUrl: http://naissancechain.local/api/v1
adminEmail: admin@naissancechain.local
adminPassword: Admin123!
agentEmail: agent@naissancechain.local
agentPassword: Agent123!
```

### Tests à Exécuter
1. **Login Admin** → Obtenir token admin
2. **Login Agent** → Obtenir token agent
3. **Create Naissance** → Créer avec token agent
4. **Verify Blockchain** → Vérifier avec token admin
5. **List Naissances** → Lister avec token agent

## 🚨 Notes Importantes

### Sécurité
- Les mots de passe sont pour tests uniquement
- En production, utiliser des mots de passe forts
- Changer les comptes par défaut

### Rôles
- Chaque rôle a des permissions spécifiques
- Certains endpoints nécessitent un rôle particulier
- Tester avec différents rôles

### Tokens
- Les tokens expirent selon configuration
- Stocker les tokens sécurisés
- Rafraîchir les tokens si nécessaire

---

## 🎯 Quick Start

1. **Copier les URLs** dans votre application
2. **Utiliser les comptes** fournis pour les tests
3. **Configurer l'authentification** avec les bons rôles
4. **Tester les endpoints** avec Postman ou cURL
5. **Adapter les permissions** selon les besoins

**L'API NaissanceChain est prête pour le développement !** 🚀
