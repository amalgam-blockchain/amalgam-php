<?php

namespace Amalgam;

if (!extension_loaded('gmp')) {
    throw new \Exception('GMP extension not installed');
}

class Asset {
    
    public $amount;
    public $symbol;
    
    public function __construct($amount, $symbol)
    {
        $this->amount = $amount;
        $this->symbol = $symbol;
    }
    
    public static function fromString($value)
    {
        $value = trim($value);
        if (!preg_match('/^[0-9]+\.?[0-9]* [A-Za-z0-9]+$/', $value)) {
            throw new \Exception('Expecting amount like "99.000 SYMBOL", instead got "' . $value . '"');
        }
        list($amount, $symbol) = explode(' ', $value);
        if (strlen($symbol) > 6) {
            throw new \Exception('Symbols are not longer than 6 characters, ' . $symbol + '-' . strlen($symbol));
        }
        $dot = strpos($amount, '.');
        $precision = $dot === false ? 0 : strlen($amount) - $dot - 1;
        $expectedPrecision = AssetSymbol::getPrecision($symbol);
        if ($precision != $expectedPrecision) {
            throw new \Exception('Wrong precision, expected ' . $expectedPrecision . ', instead got ' . $precision);
        }
        return new Asset(intval(str_replace('.', '', $amount)), $symbol);
    }
    
    public function toString()
    {
        $amount = strval($this->amount);
        $precision = AssetSymbol::getPrecision($this->symbol);
        if ($precision > 0) {
            while (strlen($amount) <= $precision) {
                $amount = '0' . $amount;
            }
            $amount = substr_replace($amount, '.', strlen($amount) - $precision, 0);
        }
        return $amount . ' ' . $this->symbol;
    }
    
    public function multiply($price)
    {
        if (strcmp($this->symbol, $price->base->symbol) === 0) {
            if ($price->base->amount <= 0) {
                throw new \Exception('Price base must be > 0');
            }
            $result = gmp_div_q(gmp_mul(gmp_init($this->amount, 10), gmp_init($price->quote->amount, 10)), gmp_init($price->base->amount, 10));
            return new Asset(gmp_intval($result), $price->quote->symbol);
        } else if (strcmp($this->symbol, $price->quote->symbol) === 0) {
            if ($price->quote->amount <= 0) {
                throw new \Exception('Price quote must be > 0');
            }
            $result = gmp_div_q(gmp_mul(gmp_init($this->amount, 10), gmp_init($price->base->amount, 10)), gmp_init($price->quote->amount, 10));
            return new Asset(gmp_intval($result), $price->base->symbol);
        }
        throw new \Exception('Provided asset does not fulfil the requirements to perform the multiply operation');
    }
    
    public function add($asset)
    {
        if (strcmp($this->symbol, $asset->symbol) !== 0) {
            throw new \Exception('Asset symbols are not identical');
        }
        return new Asset($this->amount + $asset->amount, $this->symbol);
    }
    
    public function subtract($asset)
    {
        if (strcmp($this->symbol, $asset->symbol) !== 0) {
            throw new \Exception('Asset symbols are not identical');
        }
        return new Asset($this->amount - $asset->amount, $this->symbol);
    }
}
