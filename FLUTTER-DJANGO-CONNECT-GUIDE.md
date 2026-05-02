# 🔗 Guide Connexion Flutter & Django - NaissanceChain API

## 📋 Informations Essentielles pour votre Équipe

### 🌐 URL de Base API
```
URL: http://127.0.0.1/api/v1
Alternative: http://naissancechain.local/api/v1 (si DNS configuré)
```

### 👤 Comptes Utilisateurs (Tests)
```
Admin:
  Email: admin@naissancechain.local
  Password: Admin123!
  Role: ADMIN
  Token: Obtenu via POST /login

Agent:
  Email: agent@naissancechain.local  
  Password: Agent123!
  Role: AGENT
  Token: Obtenu via POST /login

Ecole:
  Email: ecole@naissancechain.local
  Password: Ecole123!
  Role: ECOLE

Sante:
  Email: sante@naissancechain.local
  Password: Sante123!
  Role: SANTE
```

## 🔐 Flux d'Authentification

### 1. Connexion (Login)
```http
POST http://127.0.0.1/api/v1/login
Content-Type: application/json

{
  "email": "admin@naissancechain.local",
  "password": "Admin123!"
}
```

**Réponse:**
```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@naissancechain.local",
      "role": "ADMIN"
    },
    "token": "1|abc123def456789..."
  }
}
```

### 2. Utilisation du Token
```http
Authorization: Bearer 1|abc123def456789...
```

## 📝 Endpoints Principaux

### 🔐 Authentification
```
POST /login          - Connexion
POST /logout         - Déconnexion  
GET  /me             - Profil utilisateur
```

### 📝 Naissances (Nouveaux Champs)
```
GET  /naissances           - Lister naissances
POST /naissances           - Créer naissance
GET  /naissances/{id}      - Détails naissance
PUT  /naissances/{id}      - Mettre à jour
DELETE /naissances/{id}      - Supprimer
```

### ⛓️ Blockchain
```
POST /blockchain/verify/{id}  - Vérifier blockchain
GET  /blockchain/stats         - Statistiques
GET  /blockchain/records      - Historique
```

### 📁 Upload
```
POST /upload              - Upload fichier
GET  /upload/list         - Lister fichiers
```

## 🧸 Champs Naissance (Format Exact)

### Request Body (POST /naissances)
```json
{
  "nom_complet_enfant": "TOURE Ibrahim Junior",
  "sexe_enfant": "M",
  "date_naissance_enfant": "2026-05-02",
  "heure_naissance_enfant": "14:30",
  "nom_complet_pere": "TOURE Mamadou Ali",
  "profession_pere": "Ingénieur Informaticien",
  "date_naissance_pere": "1985-03-15",
  "nom_complet_mere": "DIALLO Aicha Fatoumata",
  "profession_mere": "Enseignante",
  "date_naissance_mere": "1988-07-20",
  "ville_region": "Abidjan",
  "quartier_secteur": "Cocody Angré",
  "lieu_naissance_enfant": "Hôpital Donka, Conakry",
  "nom_declarant": "TOURE Mamadou Ali",
  "numero_declarant": "1234567890123456",
  "officier_etat_civil": "M. Barry Mamadou",
  "numero_acte": "ACTE-2026-0001",
  "date_enregistrement": "2026-05-02",
  "latitude": 9.6412,
  "longitude": -13.5784,
  "hors_ligne": false
}
```

### Response Format
```json
{
  "success": true,
  "message": "Acte de naissance créé avec succès",
  "data": {
    "id": 1,
    "numero_unique": "NC-2026-000001",
    "nom_complet_enfant": "TOURE Ibrahim Junior",
    "sexe_enfant": "M",
    "date_naissance_enfant": "2026-05-02",
    "heure_naissance_enfant": "14:30",
    "nom_complet_pere": "TOURE Mamadou Ali",
    "profession_pere": "Ingénieur Informaticien",
    "date_naissance_pere": "1985-03-15",
    "nom_complet_mere": "DIALLO Aicha Fatoumata",
    "profession_mere": "Enseignante",
    "date_naissance_mere": "1988-07-20",
    "ville_region": "Abidjan",
    "quartier_secteur": "Cocody Angré",
    "lieu_naissance_enfant": "Hôpital Donka, Conakry",
    "nom_declarant": "TOURE Mamadou Ali",
    "numero_declarant": "1234567890123456",
    "numero_acte": "ACTE-2026-0001",
    "statut": "valide",
    "hash_sha256": "abc123...",
    "qr_code_url": "/storage/qr/abc123.png",
    "created_at": "2026-05-02T17:00:00.000Z",
    "agent": {
      "id": 2,
      "name": "Agent Test",
      "email": "agent@naissancechain.local"
    }
  }
}
```

## 📱 Configuration Flutter

### Constants
```dart
class ApiConstants {
  static const String baseUrl = 'http://127.0.0.1/api/v1';
  
  // Auth
  static const String login = '/login';
  static const String logout = '/logout';
  static const String profile = '/me';
  
  // Naissances
  static const String naissances = '/naissances';
  
  // Blockchain
  static const String blockchainVerify = '/blockchain/verify';
  static const String blockchainStats = '/blockchain/stats';
  
  // Users
  static const String adminEmail = 'admin@naissancechain.local';
  static const String adminPassword = 'Admin123!';
  static const String agentEmail = 'agent@naissancechain.local';
  static const String agentPassword = 'Agent123!';
}
```

### HTTP Service
```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  final String baseUrl = 'http://127.0.0.1/api/v1';
  String? _token;

  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'email': email, 'password': password}),
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      if (data['success']) {
        _token = data['data']['token'];
        return data;
      }
    }
    throw Exception('Login failed');
  }

  Future<Map<String, dynamic>> createNaissance(Map<String, dynamic> naissance) async {
    final response = await http.post(
      Uri.parse('$baseUrl/naissances'),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $_token',
      },
      body: jsonEncode(naissance),
    );
    
    if (response.statusCode == 201) {
      return jsonDecode(response.body);
    }
    throw Exception('Failed to create naissance');
  }

  Future<List<dynamic>> getNaissances() async {
    final response = await http.get(
      Uri.parse('$baseUrl/naissances'),
      headers: {
        'Authorization': 'Bearer $_token',
      },
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load naissances');
  }
}
```

### Model Naissance
```dart
class Naissance {
  final int id;
  final String numeroUnique;
  final String nomCompletEnfant;
  final String sexeEnfant;
  final String dateNaissanceEnfant;
  final String heureNaissanceEnfant;
  final String nomCompletPere;
  final String professionPere;
  final String? dateNaissancePere;
  final String nomCompletMere;
  final String professionMere;
  final String? dateNaissanceMere;
  final String villeRegion;
  final String quartierSecteur;
  final String lieuNaissanceEnfant;
  final String nomDeclarant;
  final String numeroDeclarant;
  final String numeroActe;
  final String statut;
  final String? hashSha256;
  final String? qrCodeUrl;
  final DateTime createdAt;

  Naissance({
    required this.id,
    required this.numeroUnique,
    required this.nomCompletEnfant,
    required this.sexeEnfant,
    required this.dateNaissanceEnfant,
    required this.heureNaissanceEnfant,
    required this.nomCompletPere,
    required this.professionPere,
    this.dateNaissancePere,
    required this.nomCompletMere,
    required this.professionMere,
    this.dateNaissanceMere,
    required this.villeRegion,
    required this.quartierSecteur,
    required this.lieuNaissanceEnfant,
    required this.nomDeclarant,
    required this.numeroDeclarant,
    required this.numeroActe,
    required this.statut,
    this.hashSha256,
    this.qrCodeUrl,
    required this.createdAt,
  });

  factory Naissance.fromJson(Map<String, dynamic> json) {
    return Naissance(
      id: json['id'],
      numeroUnique: json['numero_unique'],
      nomCompletEnfant: json['nom_complet_enfant'],
      sexeEnfant: json['sexe_enfant'],
      dateNaissanceEnfant: json['date_naissance_enfant'],
      heureNaissanceEnfant: json['heure_naissance_enfant'],
      nomCompletPere: json['nom_complet_pere'],
      professionPere: json['profession_pere'],
      dateNaissancePere: json['date_naissance_pere'],
      nomCompletMere: json['nom_complet_mere'],
      professionMere: json['profession_mere'],
      dateNaissanceMere: json['date_naissance_mere'],
      villeRegion: json['ville_region'],
      quartierSecteur: json['quartier_secteur'],
      lieuNaissanceEnfant: json['lieu_naissance_enfant'],
      nomDeclarant: json['nom_declarant'],
      numeroDeclarant: json['numero_declarant'],
      numeroActe: json['numero_acte'],
      statut: json['statut'],
      hashSha256: json['hash_sha256'],
      qrCodeUrl: json['qr_code_url'],
      createdAt: DateTime.parse(json['created_at']),
    );
  }
}
```

## 🐍 Configuration Django

### Settings
```python
# settings.py
import os

# API Configuration
API_BASE_URL = 'http://127.0.0.1/api/v1'
API_TIMEOUT = 30

# Authentication
API_ADMIN_EMAIL = 'admin@naissancechain.local'
API_ADMIN_PASSWORD = 'Admin123!'
API_AGENT_EMAIL = 'agent@naissancechain.local'
API_AGENT_PASSWORD = 'Agent123!'
```

### API Service
```python
import requests
import json
from datetime import datetime

class NaissanceChainAPI:
    def __init__(self):
        self.base_url = 'http://127.0.0.1/api/v1'
        self.token = None
        self.session = requests.Session()
    
    def login(self, email, password):
        """Connexion à l'API"""
        url = f"{self.base_url}/login"
        data = {
            "email": email,
            "password": password
        }
        
        response = self.session.post(url, json=data)
        
        if response.status_code == 200:
            result = response.json()
            if result.get('success'):
                self.token = result['data']['token']
                self.session.headers.update({
                    'Authorization': f'Bearer {self.token}'
                })
                return result
        raise Exception(f"Login failed: {response.text}")
    
    def create_naissance(self, naissance_data):
        """Créer une naissance"""
        url = f"{self.base_url}/naissances"
        
        response = self.session.post(url, json=naissance_data)
        
        if response.status_code == 201:
            return response.json()
        raise Exception(f"Failed to create naissance: {response.text}")
    
    def get_naissances(self):
        """Lister les naissances"""
        url = f"{self.base_url}/naissances"
        
        response = self.session.get(url)
        
        if response.status_code == 200:
            return response.json()
        raise Exception(f"Failed to get naissances: {response.text}")
    
    def get_naissance(self, naissance_id):
        """Détails d'une naissance"""
        url = f"{self.base_url}/naissances/{naissance_id}"
        
        response = self.session.get(url)
        
        if response.status_code == 200:
            return response.json()
        raise Exception(f"Failed to get naissance: {response.text}")
    
    def verify_blockchain(self, naissance_id):
        """Vérifier blockchain"""
        url = f"{self.base_url}/blockchain/verify/{naissance_id}"
        
        response = self.session.post(url)
        
        if response.status_code == 200:
            return response.json()
        raise Exception(f"Failed to verify blockchain: {response.text}")

# Usage
api = NaissanceChainAPI()

# Login admin
api.login('admin@naissancechain.local', 'Admin123!')

# Créer naissance
naissance_data = {
    "nom_complet_enfant": "TOURE Ibrahim Junior",
    "sexe_enfant": "M",
    "date_naissance_enfant": "2026-05-02",
    "heure_naissance_enfant": "14:30",
    "nom_complet_pere": "TOURE Mamadou Ali",
    "profession_pere": "Ingénieur Informaticien",
    "nom_complet_mere": "DIALLO Aicha Fatoumata",
    "profession_mere": "Enseignante",
    "ville_region": "Abidjan",
    "quartier_secteur": "Cocody Angré",
    "lieu_naissance_enfant": "Hôpital Donka, Conakry",
    "nom_declarant": "TOURE Mamadou Ali",
    "numero_declarant": "1234567890123456",
    "officier_etat_civil": "M. Barry Mamadou",
    "numero_acte": "ACTE-2026-0001",
    "date_enregistrement": "2026-05-02"
}

result = api.create_naissance(naissance_data)
print(f"Naissance créée: {result['data']['numero_unique']}")
```

## 🚨 Codes d'Erreur

### HTTP Status Codes
```
200 - Succès
201 - Créé (POST)
400 - Requête invalide
401 - Non authentifié
403 - Permission refusée
404 - Non trouvé
422 - Erreur de validation
500 - Erreur serveur
```

### Format Erreur
```json
{
  "success": false,
  "message": "Erreur de validation",
  "errors": {
    "nom_complet_enfant": ["Le nom complet de l'enfant est obligatoire"],
    "sexe_enfant": ["Le sexe de l'enfant est obligatoire"]
  }
}
```

## 🧪 Tests de Connexion

### Test 1: Vérifier API Accessible
```bash
curl http://127.0.0.1/api/v1/
```

### Test 2: Login Admin
```bash
curl -X POST http://127.0.0.1/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@naissancechain.local", "password": "Admin123!"}'
```

### Test 3: Lister Naissances (avec token)
```bash
curl -X GET http://127.0.0.1/api/v1/naissances \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

## 📋 Checklist Intégration

### Pour Flutter
- [ ] Configurer `http` package
- [ ] Créer `ApiService` class
- [ ] Implémenter models `Naissance`, `User`
- [ ] Gérer tokens (SharedPreferences)
- [ ] Gérer erreurs réseau
- [ ] Tests unitaires

### Pour Django
- [ ] Configurer `requests` library
- [ ] Créer `NaissanceChainAPI` class
- [ ] Implémenter models Django
- [ ] Gérer sessions/tokens
- [ ] Gérer erreurs API
- [ ] Tests intégration

---

## 🎯 Instructions Rapides

1. **URL Backend**: `http://127.0.0.1/api/v1`
2. **Login Admin**: `admin@naissancechain.local` / `Admin123!`
3. **Login Agent**: `agent@naissancechain.local` / `Agent123!`
4. **Token**: Utiliser `Authorization: Bearer TOKEN`
5. **Champs**: Respecter exactement les nouveaux formats

**🚀 Votre backend NaissanceChain est prêt pour Flutter & Django !**
