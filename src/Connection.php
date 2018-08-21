<?php

namespace Amalgam;

use Yii;
use WebSocket\Client;
use WebSocket\ConnectionException;

class Connection {
    
    public $nodeUrl;
    
    private $reserveNodeUrlList;
    private $currentNodeUrl;
    private static $connection;
    private static $currentId;

    const TIMEOUT = 5;
    const MAX_NUMBER_OF_TRIES = 3;

    private function getConnection()
    {
        if (self::$connection === null) {
            $this->newConnection($this->getCurrentUrl());
        }
        return self::$connection;
    }

    private function newConnection($nodeUrl)
    {
        self::$connection = new Client($nodeUrl, ['timeout' => self::TIMEOUT]);
        return self::$connection;
    }

    private function getCurrentUrl()
    {
        if ($this->currentNodeUrl === null) {
            if (is_array($this->nodeUrl)) {
                $this->reserveNodeUrlList = $this->nodeUrl;
                $url = array_shift($this->reserveNodeUrlList);
            } else {
                $url = $this->nodeUrl;
            }

            $this->currentNodeUrl = $url;
        }
        return $this->currentNodeUrl;
    }

    private function existsReserveNodeUrl()
    {
        return !empty($this->reserveNodeUrlList);
    }

    private function setReserveNodeUrlToCurrentUrl()
    {
        $this->currentNodeUrl = array_shift($this->reserveNodeUrlList);
    }

    private function connectToReserveNode()
    {
        $this->setReserveNodeUrlToCurrentUrl();
        return $this->newConnection($this->getCurrentUrl());
    }
    
    private function getCurrentId()
    {
        if (self::$currentId === null) {
            self::$currentId = 0;
        }
        return self::$currentId;
    }

    private function setCurrentId($id)
    {
        self::$currentId = $id;
    }

    private function getNextId()
    {
        $next = $this->getCurrentId() + 1;
        $this->setCurrentId($next);
        return $next;
    }
    
    private function checkResult($result)
    {
        if (is_array($result)) {
            if (array_key_exists('result', $result)) {
                return $result['result'];
            } else if (array_key_exists('error', $result)) {
                $error = $result['error'];
                if (is_array($error) && array_key_exists('message', $error)) {
                    $message = $error['message'];
                    $begin = strpos($message, ':');
                    $begin = ($begin === false) ? 0 : $begin + 1;
                    $end = strpos($message, '{');
                    if ($end === false) {
                        $end = strlen($message);
                    }
                    throw new \Exception(ucfirst(trim(substr($message, $begin, $end - $begin))));
                }
            }
        }
        throw new \Exception('Unknown response');
    }
    
    public function exec($apiName, $command, $params = [])
    {
        return $this->checkResult($this->execute($apiName, $command, $params));
    }

    public function execJson($json)
    {
        return $this->checkResult($this->executeInternal($json));
    }

    public function execute($apiName, $command, $params = [], $id = null)
    {
        if (empty($id)) {
            $id = $this->getNextId();
        }
        return $this->executeInternal(json_encode([
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                $apiName,
                $command,
                $params
            ],
            'id' => $id,
        ]));
    }

    private function executeInternal($data, $try_number = 1)
    {
        try {
            $connection = $this->getConnection();
            $connection->send($data);
            $result = json_decode($connection->receive(), true);
            if (is_array($result) && array_key_exists('error', $result)) {
                $error = $result['error'];
                if (is_array($error) && array_key_exists('message', $error)) {
                    Yii::$app->trigger('amalgamError', new Event([
                        'command' => $data,
                        'error' => $error['message'],
                    ]));
                }
            }
        } catch (ConnectionException $e) {
            if ($try_number < self::MAX_NUMBER_OF_TRIES) {
                $result = $this->executeInternal($data, $try_number + 1);
            } elseif ($this->existsReserveNodeUrl()) {
                $this->connectToReserveNode();
                $result = $this->executeInternal($data);
            } else {
                Yii::$app->trigger('amalgamError', new Event([
                    'command' => $data,
                    'error' => $e->getMessage(),
                ]));
                throw $e;
            }
        }
        return $result;
    }
}
