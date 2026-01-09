# PowerBank Protocol Integration - Setup Guide

Ce guide vous explique comment configurer et utiliser l'intégration du protocole PowerBank dans votre application Laravel.

## 📋 Prérequis

1. **Laravel 12.x** avec PHP 8.2+
2. **Base de données** (MySQL, PostgreSQL, SQLite)
3. **Serveur MQTT** (recommandé: EMQX)
4. **Composer** et **NPM** installés

## 🚀 Installation

### 1. Installer les dépendances

```bash
cd laravel-template
composer install
npm install
```

### 2. Installer un client MQTT (Optionnel mais recommandé)

Pour une intégration MQTT complète, vous avez plusieurs options:

#### Option A: Utiliser EMQX HTTP API (Recommandé)
EMQX fournit une API HTTP pour publier des messages MQTT. Aucune installation supplémentaire nécessaire.

#### Option B: Installer php-mqtt/client
```bash
composer require php-mqtt/client
```

#### Option C: Utiliser mosquitto-clients (via commande système)
```bash
# Ubuntu/Debian
sudo apt-get install mosquitto-clients

# macOS
brew install mosquitto
```

### 3. Configurer l'environnement

Ajoutez ces variables dans votre fichier `.env`:

```env
# PowerBank MQTT Configuration
POWERBANK_MQTT_HOST=localhost
POWERBANK_MQTT_PORT=1883
POWERBANK_MQTT_USERNAME=
POWERBANK_MQTT_PASSWORD=

# EMQX HTTP API (si vous utilisez EMQX)
POWERBANK_MQTT_API_URL=http://localhost:18083
POWERBANK_MQTT_API_USERNAME=admin
POWERBANK_MQTT_API_PASSWORD=public

# Path to mosquitto_pub (si vous utilisez mosquitto-clients)
POWERBANK_MOSQUITTO_PUB_PATH=/usr/bin/mosquitto_pub

# API Host (pour les réponses aux appareils)
POWERBANK_API_HOST=http://your-domain.com
```

### 4. Exécuter les migrations

```bash
php artisan migrate
```

Cela créera les tables suivantes:
- `devices` - Informations sur les appareils
- `device_slots` - État des compartiments
- `device_connections` - Historique des connexions MQTT

## 📡 Configuration MQTT

### Utilisation avec EMQX

1. **Installer EMQX**:
   ```bash
   # Docker
   docker run -d --name emqx -p 1883:1883 -p 8083:8083 -p 8084:8084 -p 18083:18083 emqx/emqx
   ```

2. **Accéder à la console web**: http://localhost:18083
   - Username: `admin`
   - Password: `public`

3. **Configurer l'API HTTP** dans EMQX pour permettre la publication via HTTP

### Utilisation avec Mosquitto

```bash
# Installer Mosquitto
sudo apt-get install mosquitto mosquitto-clients

# Démarrer le serveur
sudo systemctl start mosquitto
```

## 🔌 Endpoints API

### Authentification des appareils
```
POST /api/rentbox/client/connect
```

Paramètres:
- `uuid` (requis): IMEI de l'appareil
- `deviceId` (requis): ID de l'appareil (défaut: 0)
- `simUUID` (optionnel): ICCID de la SIM
- `simMobile` (optionnel): Numéro de téléphone SIM
- `sign` (requis): Signature MD5

Body:
```
hardware=V6086&software=20240904-RBMG-3.0.0.0
```

Réponse:
```json
{
    "code": 200,
    "type": 0,
    "data": "864601068367135,powerbank,host,1883,username,password,timestamp",
    "msg": "OK",
    "time": 1705661910697
}
```

### Upload de statut (rapport complet)
```
POST /api/rentbox/device/upload
```

### Retour de powerbank
```
POST /api/rentbox/device/return
```

## 🖥️ Interface Web

### Accéder à la gestion des appareils

1. Connectez-vous à l'application
2. Cliquez sur "PowerBank Devices" dans le menu
3. Vous pouvez:
   - Voir la liste de tous les appareils
   - Ajouter un nouvel appareil
   - Voir les détails d'un appareil
   - Envoyer des commandes (check, popup, etc.)
   - Voir l'état des compartiments

### Commandes disponibles

- **Check Device**: Envoie une commande de vérification à l'appareil
- **Refresh Status**: Rafraîchit le statut de l'appareil
- **Popup Slot**: Fait sortir un powerbank d'un compartiment spécifique
- **Popup by SN**: Fait sortir un powerbank par son numéro de série

## 📝 Protocole MQTT

### Topics utilisés

- `powerbank/{clientId}/check` - Commande de vérification
- `powerbank/{clientId}/popup` - Commande de popup par slot
- `powerbank/{clientId}/popup_sn` - Commande de popup par SN
- `powerbank/{clientId}/response` - Réponses de l'appareil
- `powerbank/{clientId}/upload_all` - Upload complet de statut
- `powerbank/{clientId}/heartbeat` - Heartbeat de l'appareil

### Format des messages

Les messages sont généralement en JSON:
```json
{
    "slot": 1,
    "status": "occupied",
    "sn": "PB123456",
    "battery": 85
}
```

## 🔧 Développement

### Tester l'authentification

```bash
curl -X POST "http://localhost:8000/api/rentbox/client/connect?uuid=860602069165357&deviceId=0&simUUID=89860427092281034392&simMobile=&sign=ff46bf468563a48b068d198158a21835" \
  -H "Content-Type: text/plain" \
  -d "hardware=V6086&software=20240904-RBMG-3.0.0.0"
```

### Simuler un upload de statut

```bash
curl -X POST "http://localhost:8000/api/rentbox/device/upload" \
  -H "Content-Type: application/json" \
  -d '{
    "uuid": "860602069165357",
    "data": "{\"total_slots\": 12, \"slots\": [{\"slot\": 1, \"status\": \"occupied\", \"sn\": \"PB001\", \"battery\": 85}]}"
  }'
```

## ⚠️ Notes importantes

1. **Sécurité**: En production, ajoutez une authentification pour les endpoints API
2. **MQTT**: Pour une intégration complète, utilisez un client MQTT approprié
3. **Heartbeat**: Les appareils doivent envoyer un heartbeat régulièrement
4. **Signature**: La signature MD5 doit être calculée correctement selon le protocole

## 📚 Documentation

- [Documentation PowerBank Protocol](https://docs.volinks.com/powerbank-protocol-v1/guide/overview.html)
- [Documentation EMQX](https://www.emqx.io/docs)
- [Documentation Mosquitto](https://mosquitto.org/documentation/)

## 🐛 Dépannage

### Les messages MQTT ne sont pas envoyés

1. Vérifiez que le serveur MQTT est démarré
2. Vérifiez les logs: `storage/logs/laravel.log`
3. Testez la connexion MQTT manuellement:
   ```bash
   mosquitto_pub -h localhost -p 1883 -t test/topic -m "test message"
   ```

### Les appareils ne se connectent pas

1. Vérifiez que l'endpoint `/api/rentbox/client/connect` est accessible
2. Vérifiez la signature MD5
3. Vérifiez les logs de l'application

### Les données ne s'affichent pas

1. Vérifiez que les migrations ont été exécutées
2. Vérifiez que les appareils envoient bien les données
3. Vérifiez les logs pour les erreurs

