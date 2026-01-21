# 📋 Technical Documentation - PowerBank Integration

**Document intended for the company for PowerBank device configuration**

---

## 🌐 Connection Information

### API Base URL
```
https://powerbank.universaltechnologiesafrica.com/api
```

---

## 🔌 Available API Endpoints

### 1. Device Authentication (MQTT Connection)

**Endpoint:** `POST /api/rentbox/client/connect`

**Complete URL:** `https://powerbank.universaltechnologiesafrica.com/api/rentbox/client/connect`

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `uuid` | string | ✅ Yes | 4G module IMEI (unique device identifier) |
| `deviceId` | string | ✅ Yes | Device ID (default: `0`) |
| `simUUID` | string | ❌ No | SIM card ICCID |
| `simMobile` | string | ❌ No | SIM card phone number |
| `sign` | string | ✅ Yes | MD5 signature (see calculation below) |

#### Request Body
```
Content-Type: text/plain

hardware=V6086&software=20240904-RBMG-3.0.0.0
```

**Replace the versions with the actual values from your device**

#### Signature Calculation

The `sign` signature must be calculated as follows:

```php
$sign = MD5("deviceId={deviceId}|simMobile={simMobile}|simUUID={simUUID}|uuid={uuid}");
```

**Example:**
- deviceId = `0`
- simMobile = `` (empty)
- simUUID = `89860427092281034392`
- uuid = `860602069165357`

```
sign = MD5("deviceId=0|simMobile=|simUUID=89860427092281034392|uuid=860602069165357")
sign = "ff46bf468563a48b068d198158a21835"
```

#### cURL Request Example

```bash
curl -X POST "https://powerbank.universaltechnologiesafrica.com/api/rentbox/client/connect?uuid=860602069165357&deviceId=0&simUUID=89860427092281034392&simMobile=&sign=ff46bf468563a48b068d198158a21835" \
  -H "Content-Type: text/plain" \
  -d "hardware=V6086&software=20240904-RBMG-3.0.0.0"
```

#### Success Response (200)

```json
{
    "code": 200,
    "type": 0,
    "data": "864601068367135,powerbank,host.mqtt.com,1883,864601068367135,password123,1705661910800",
    "msg": "OK",
    "time": 1705661910697
}
```

**Format of `data` (comma-separated):**
1. `clientId` - MQTT client ID (use the IMEI)
2. `productKey` - Always `powerbank`
3. `host` - MQTT server address
4. `port` - MQTT port (usually 1883)
5. `username` - MQTT username
6. `password` - MQTT password
7. `timestamp` - Timestamp in milliseconds

#### Error Response (401)

```json
{
    "code": 401,
    "type": 1,
    "msg": "Invalid signature",
    "time": 1705661910697
}
```

---

### 2. Complete Status Upload (Device Report)

**Endpoint:** `POST /api/rentbox/device/upload`

**Complete URL:** `https://powerbank.universaltechnologiesafrica.com/api/rentbox/device/upload`

#### Request Body (JSON)

```json
{
    "uuid": "860602069165357",
    "data": "{\"total_slots\": 12, \"slots\": [{\"slot\": 1, \"status\": \"occupied\", \"sn\": \"PB001\", \"battery\": 85}, {\"slot\": 2, \"status\": \"empty\", \"sn\": null, \"battery\": null}]}"
}
```

**Note:** The `data` field is an encoded JSON string containing device information.

#### Format of JSON in `data`

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

**Possible statuses for compartments:**
- `empty` - Empty compartment
- `occupied` - Occupied compartment (powerbank present)
- `fault` - Technical fault
- `maintenance` - Under maintenance

#### cURL Request Example

```bash
curl -X POST "https://powerbank.universaltechnologiesafrica.com/api/rentbox/device/upload" \
  -H "Content-Type: application/json" \
  -d '{
    "uuid": "860602069165357",
    "data": "{\"total_slots\": 12, \"slots\": [{\"slot\": 1, \"status\": \"occupied\", \"sn\": \"PB001\", \"battery\": 85}]}"
  }'
```

#### Success Response (200)

```json
{
    "code": 200,
    "type": 0,
    "msg": "OK",
    "time": 1705661910697
}
```

---

### 3. PowerBank Return

**Endpoint:** `POST /api/rentbox/device/return`

**Complete URL:** `https://powerbank.universaltechnologiesafrica.com/api/rentbox/device/return`

#### Request Body (JSON)

```json
{
    "uuid": "860602069165357",
    "slot": 1,
    "sn": "PB001"
}
```

#### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `uuid` | string | ✅ Yes | Device IMEI |
| `slot` | integer | ✅ Yes | Compartment number (starts at 1) |
| `sn` | string | ✅ Yes | Powerbank serial number |

#### cURL Request Example

```bash
curl -X POST "https://powerbank.universaltechnologiesafrica.com/api/rentbox/device/return" \
  -H "Content-Type: application/json" \
  -d '{
    "uuid": "860602069165357",
    "slot": 1,
    "sn": "PB001"
  }'
```

#### Success Response (200)

```json
{
    "code": 200,
    "type": 0,
    "msg": "OK",
    "time": 1705661910697
}
```

---

## 📡 MQTT Communication

### MQTT Topics

Once authentication is successful, the device will receive MQTT connection information. The topics to use are:

#### Topics for Receiving Commands (Subscribe)

- `powerbank/{clientId}/check` - Check command
- `powerbank/{clientId}/popup` - Popup command by slot
- `powerbank/{clientId}/popup_sn` - Popup command by SN

#### Topics for Sending Data (Publish)

- `powerbank/{clientId}/response` - Command responses
- `powerbank/{clientId}/upload_all` - Complete upload (alternative to HTTP)
- `powerbank/{clientId}/heartbeat` - Heartbeat (0x7A)

### MQTT Message Formats

#### Popup Command by Slot

**Topic:** `powerbank/{clientId}/popup`

**Message:**
```json
{
    "slot": 1
}
```

#### Popup Command by SN

**Topic:** `powerbank/{clientId}/popup_sn`

**Message:**
```json
{
    "sn": "PB001"
}
```

#### Command Responses

**Topic:** `powerbank/{clientId}/response`

**Message (example for check - 0x10):**
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

**Expected Response:** `0x7A`

---

## 🔄 Recommended Communication Flow

### 1. On Device Startup

1. Call `/api/rentbox/client/connect` to obtain MQTT credentials
2. Connect to the MQTT server with the received credentials
3. Subscribe to command topics
4. Send an initial heartbeat

### 2. Continuous Communication

1. **Heartbeat:** Send every 30-60 seconds
2. **Status upload:** 
   - Via HTTP: `POST /api/rentbox/device/upload` (periodic or after change)
   - Via MQTT: Publish to `powerbank/{clientId}/upload_all` (optional)
3. **Command responses:** Publish to `powerbank/{clientId}/response`

### 3. Events

1. **Powerbank return:** `POST /api/rentbox/device/return`
2. **Status change:** Update via upload

---

## ⚠️ Important Points

### Security

1. **MD5 Signature:** Always calculate the signature correctly for authentication
2. **HTTPS:** Use HTTPS in production to secure communications
3. **MQTT:** Use MQTT over TLS (port 8883) in production if possible

### Error Handling

- In case of error 401 (invalid signature), verify the signature calculation
- In case of error 404 (device not found), the device will be created automatically on first connection
- In case of network error, implement a retry system with exponential backoff

### Performance

- Do not send status upload more than once per minute (except for important events)
- Heartbeat recommended every 30-60 seconds
- Use MQTT for real-time commands, HTTP for periodic reports

---

## 📞 Technical Support

For any technical questions, contact the development team with:
- Device IMEI (uuid)
- Error logs
- Incident time
- Device hardware/software version

---

## 📝 Configuration Checklist

- [ ] Get the API base URL
- [ ] Implement MD5 signature calculation
- [ ] Configure call to `/api/rentbox/client/connect`
- [ ] Parse response to extract MQTT credentials
- [ ] Implement MQTT connection
- [ ] Subscribe to command topics
- [ ] Implement status upload (HTTP or MQTT)
- [ ] Implement heartbeat
- [ ] Handle events (powerbank return)
- [ ] Test all functionalities

---

**Document Version:** 1.0  
**Date:** 2024  
**Protocol:** PowerBank Protocol v1
