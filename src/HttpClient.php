<?php

namespace Amalgam;

class HttpClient extends BaseClient {
    
    private $url;
    private $timeout;
    
    public function __construct($url, $timeout)
    {
        $this->url = $url;
        $this->timeout = $timeout;
    }
    
    public function send($data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if (!empty($error)) {
            throw new ClientException($error);
        }
        return $result;
    }
}
