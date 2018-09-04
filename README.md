# Amalgam API PHP library for Yii framework
This extension provides access to Amalgam blockchain from web applications built on Yii framework.

## Quick start
The preferred way to install this extension is through composer. Just run the following command:

```bash
composer require amalgam-blockchain/amalgam-php
```

## Configure
Add following lines to your main configuration file and specify correct node IP address and port:

```php
'amalgam' => [
    'class' => 'Amalgam\Amalgam',
    'node' => 'ws://127.0.0.1:8090',
],
```

Both WebSocket (ws/wss) and JSON-RPC (http/https) protocols are supported to connect to nodes.