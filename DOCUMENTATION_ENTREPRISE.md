# 📋 Documentation Technique - Intégration PowerBank

**Document destiné à l'entreprise pour la configuration des appareils PowerBank**

---

## 🌐 Informations de Connexion

### URL de Base de l'API
```
https://powerbank.universaltechnologiesafrica.com/api
```

---

## 🔌 Endpoints API Disponibles

### 1. Authentification des Appareils (Connexion MQTT)

**Endpoint:** `POST /api/rentbox/client/connect`

**URL Complète:** `https://powerbank.universaltechnologiesafrica.com/api/rentbox/client/connect`

#### Paramètres de Requête (Query Parameters)

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `uuid` | string | ✅ Oui | IMEI du module 4G (identifiant unique de l'appareil) |
| `deviceId` | string | ✅ Oui | ID de l'appareil (par défaut: `0`) |
| `simUUID` | string | ❌ Non | ICCID de la carte SIM |
| `simMobile` | string | ❌ Non | Numéro de téléphone de la carte SIM |
| `sign` | string | ✅ Oui | Signature MD5 (voir calcul ci-dessous) |

#### Body de la Requête
```
Content-Type: text/plain

hardware=V6086&software=20240904-RBMG-3.0.0.0
```

**Remplacez les versions par les valeurs réelles de votre appareil**

#### Calcul de la Signature

La signature `sign` doit être calculée comme suit :

```php
$sign = MD5("deviceId={deviceId}|simMobile={simMobile}|simUUID={simUUID}|uuid={uuid}");
```

**Exemple:**
- deviceId = `0`
- simMobile = `` (vide)
- simUUID = `89860427092281034392`
- uuid = `860602069165357`

```
sign = MD5("deviceId=0|simMobile=|simUUID=89860427092281034392|uuid=860602069165357")
sign = "ff46bf468563a48b068d198158a21835"
```

#### Exemple de Requête cURL

```bash
curl -X POST "https://powerbank.universaltechnologiesafrica.com/api/rentbox/client/connect?uuid=860602069165357&deviceId=0&simUUID=89860427092281034392&simMobile=&sign=ff46bf468563a48b068d198158a21835" \
  -H "Content-Type: text/plain" \
  -d "hardware=V6086&software=20240904-RBMG-3.0.0.0"
```

#### Réponse Succès (200)

```json
{
    "code": 200,
    "type": 0,
    "data": "864601068367135,powerbank,host.mqtt.com,1883,864601068367135,password123,1705661910800",
    "msg": "OK",
    "time": 1705661910697
}
```

**Format de `data` (séparé par virgules):**
1. `clientId` - ID client MQTT (utilisez l'IMEI)
2. `productKey` - Toujours `powerbank`
3. `host` - Adresse du serveur MQTT
4. `port` - Port MQTT (généralement 1883)
5. `username` - Nom d'utilisateur MQTT
6. `password` - Mot de passe MQTT
7. `timestamp` - Horodatage en millisecondes

#### Réponse Erreur (401)

```json
{
    "code": 401,
    "type": 1,
    "msg": "Invalid signature",
    "time": 1705661910697
}
```

---

### 2. Upload de Statut Complet (Rapport de l'Appareil)

**Endpoint:** `POST /api/rentbox/device/upload`

**URL Complète:** `https://powerbank.universaltechnologiesafrica.com/api/rentbox/device/upload`

#### Body de la Requête (JSON)

```json
{
    "uuid": "860602069165357",
    "data": "{\"total_slots\": 12, \"slots\": [{\"slot\": 1, \"status\": \"occupied\", \"sn\": \"PB001\", \"battery\": 85}, {\"slot\": 2, \"status\": \"empty\", \"sn\": null, \"battery\": null}]}"
}
```

**Note:** Le champ `data` est une chaîne JSON encodée contenant les informations de l'appareil.

#### Format du JSON dans `data`

```json
{
    "total_slots": 12,
    "slots": [
        {
            "slot": 1,
            "status": "occupied",
            "sn": "PB001",
            "battery": 85
        },
        {
            "slot": 2,
            "status": "empty",
            "sn": null,
            "battery": null
        }
    ]
}
```

**Statuts possibles pour les compartiments:**
- `empty` - Compartiment vide
- `occupied` - Compartiment occupé (powerbank présent)
- `fault` - Défaut technique
- `maintenance` - En maintenance

#### Exemple de Requête cURL

```bash
curl -X POST "https://powerbank.universaltechnologiesafrica.com/api/rentbox/device/upload" \
  -H "Content-Type: application/json" \
  -d '{
    "uuid": "860602069165357",
    "data": "{\"total_slots\": 12, \"slots\": [{\"slot\": 1, \"status\": \"occupied\", \"sn\": \"PB001\", \"battery\": 85}]}"
  }'
```

#### Réponse Succès (200)

```json
{
    "code": 200,
    "type": 0,
    "msg": "OK",
    "time": 1705661910697
}
```

---

### 3. Retour de PowerBank

**Endpoint:** `POST /api/rentbox/device/return`

**URL Complète:** `https://powerbank.universaltechnologiesafrica.com/api/rentbox/device/return`

#### Body de la Requête (JSON)

```json
{
    "uuid": "860602069165357",
    "slot": 1,
    "sn": "PB001"
}
```

#### Paramètres

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `uuid` | string | ✅ Oui | IMEI de l'appareil |
| `slot` | integer | ✅ Oui | Numéro du compartiment (commence à 1) |
| `sn` | string | ✅ Oui | Numéro de série du powerbank |

#### Exemple de Requête cURL

```bash
curl -X POST "https://powerbank.universaltechnologiesafrica.com/api/rentbox/device/return" \
  -H "Content-Type: application/json" \
  -d '{
    "uuid": "860602069165357",
    "slot": 1,
    "sn": "PB001"
  }'
```

#### Réponse Succès (200)

```json
{
    "code": 200,
    "type": 0,
    "msg": "OK",
    "time": 1705661910697
}
```

---

## 📡 Communication MQTT

### Topics MQTT

Une fois l'authentification réussie, l'appareil recevra les informations de connexion MQTT. Les topics à utiliser sont :

#### Topics pour Recevoir des Commandes (Subscribe)

- `powerbank/{clientId}/check` - Commande de vérification
- `powerbank/{clientId}/popup` - Commande de popup par slot
- `powerbank/{clientId}/popup_sn` - Commande de popup par SN

#### Topics pour Envoyer des Données (Publish)

- `powerbank/{clientId}/response` - Réponses aux commandes
- `powerbank/{clientId}/upload_all` - Upload complet (alternative à HTTP)
- `powerbank/{clientId}/heartbeat` - Heartbeat (0x7A)

### Format des Messages MQTT

#### Commande Popup par Slot

**Topic:** `powerbank/{clientId}/popup`

**Message:**
```json
{
    "slot": 1
}
```

#### Commande Popup par SN

**Topic:** `powerbank/{clientId}/popup_sn`

**Message:**
```json
{
    "sn": "PB001"
}
```

#### Réponse aux Commandes

**Topic:** `powerbank/{clientId}/response`

**Message (exemple pour check - 0x10):**
```json
{
    "command": "0x10",
    "status": "ok",
    "data": {...}
}
```

#### Heartbeat

**Topic:** `powerbank/{clientId}/heartbeat`

**Message:** `0x7A`

**Réponse attendue:** `0x7A`

---

## 🔄 Flux de Communication Recommandé

### 1. Au Démarrage de l'Appareil

1. Appeler `/api/rentbox/client/connect` pour obtenir les credentials MQTT
2. Se connecter au serveur MQTT avec les credentials reçus
3. S'abonner aux topics de commande
4. Envoyer un heartbeat initial

### 2. Communication Continue

1. **Heartbeat:** Envoyer toutes les 30-60 secondes
2. **Upload de statut:** 
   - Via HTTP: `POST /api/rentbox/device/upload` (périodique ou après changement)
   - Via MQTT: Publier sur `powerbank/{clientId}/upload_all` (optionnel)
3. **Réponses aux commandes:** Publier sur `powerbank/{clientId}/response`

### 3. Événements

1. **Retour de powerbank:** `POST /api/rentbox/device/return`
2. **Changement de statut:** Mettre à jour via upload

---

## ⚠️ Points Importants

### Sécurité

1. **Signature MD5:** Toujours calculer correctement la signature pour l'authentification
2. **HTTPS:** Utiliser HTTPS en production pour sécuriser les communications
3. **MQTT:** Utiliser MQTT over TLS (port 8883) en production si possible

### Gestion des Erreurs

- En cas d'erreur 401 (signature invalide), vérifier le calcul de la signature
- En cas d'erreur 404 (appareil non trouvé), l'appareil sera créé automatiquement à la première connexion
- En cas d'erreur réseau, implémenter un système de retry avec backoff exponentiel

### Performance

- Ne pas envoyer d'upload de statut plus d'une fois par minute (sauf événements importants)
- Heartbeat recommandé toutes les 30-60 secondes
- Utiliser MQTT pour les commandes en temps réel, HTTP pour les rapports périodiques

---

## 📞 Support Technique

Pour toute question technique, contactez l'équipe de développement avec :
- L'IMEI de l'appareil (uuid)
- Les logs d'erreur
- L'heure de l'incident
- La version hardware/software de l'appareil

---

## 📝 Checklist de Configuration

- [ ] Récupérer l'URL de base de l'API
- [ ] Implémenter le calcul de signature MD5
- [ ] Configurer l'appel à `/api/rentbox/client/connect`
- [ ] Parser la réponse pour extraire les credentials MQTT
- [ ] Implémenter la connexion MQTT
- [ ] S'abonner aux topics de commande
- [ ] Implémenter l'upload de statut (HTTP ou MQTT)
- [ ] Implémenter le heartbeat
- [ ] Gérer les événements (retour de powerbank)
- [ ] Tester toutes les fonctionnalités

---

**Version du Document:** 1.0  
**Date:** 2024  
**Protocole:** PowerBank Protocol v1

