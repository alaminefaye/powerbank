<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PowerBank Protocol Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for PowerBank device communication protocol
    |
    */

    'mqtt_host' => env('POWERBANK_MQTT_HOST', 'powerbank.universaltechnologiesafrica.com'),
    'mqtt_port' => env('POWERBANK_MQTT_PORT', 1883),
    'mqtt_username' => env('POWERBANK_MQTT_USERNAME', ''),
    'mqtt_password' => env('POWERBANK_MQTT_PASSWORD', ''),
    
    // EMQX HTTP API (if using EMQX)
    'mqtt_api_url' => env('POWERBANK_MQTT_API_URL', ''),
    'mqtt_api_username' => env('POWERBANK_MQTT_API_USERNAME', ''),
    'mqtt_api_password' => env('POWERBANK_MQTT_API_PASSWORD', ''),
    
    // Mosquitto pub path (if using mosquitto-clients)
    'mosquitto_pub_path' => env('POWERBANK_MOSQUITTO_PUB_PATH', '/usr/bin/mosquitto_pub'),
    
    'api_host' => env('POWERBANK_API_HOST', env('APP_URL')),
    
    'product_key' => env('POWERBANK_PRODUCT_KEY', 'powerbank'),
    
    'heartbeat_timeout' => env('POWERBANK_HEARTBEAT_TIMEOUT', 300), // 5 minutes
];

