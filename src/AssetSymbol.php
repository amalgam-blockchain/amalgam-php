<?php

namespace Amalgam;

class AssetSymbol {
    
    const AML = 'AML';
    const AMLV = 'AMLV';
    const AMLD = 'AMLD';
    
    private static $symbols = [
        self::AML => 3,
        self::AMLV => 6,
        self::AMLD => 3,
    ];
    
    public static function getPrecision($name)
    {
        if (!array_key_exists($name, self::$symbols)) {
            throw new \Exception('Unknown symbol: ' . $name);
        }
        return self::$symbols[$name];
    }
}
