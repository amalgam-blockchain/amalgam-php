<?php

namespace Amalgam;

class Price {
    
    public $base;
    public $quote;
    
    public function __construct($base, $quote)
    {
        $this->base = $base;
        $this->quote = $quote;
    }
    
    public function isNull()
    {
        return ($this->base->amount == 0) && ($this->quote->amount == 0);
    }
}
