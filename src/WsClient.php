<?php

namespace Amalgam;

use WebSocket\Client;
use WebSocket\ConnectionException;

class WsClient extends BaseClient {
    
    private $client;
    
    public function __construct($url, $timeout)
    {
        $this->client = new Client($url, ['timeout' => $timeout]);
    }
    
    public function send($data)
    {
        try {
            $this->client->send($data);
            return $this->client->receive();
        } catch (ConnectionException $e) {
            throw new ClientException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
