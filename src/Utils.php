<?php

namespace Amalgam;

if (!extension_loaded('gmp')) {
    throw new \Exception('GMP extension not installed');
}

class Utils {
    
    public static function bigIntToString($value, $length)
    {
        $value = gmp_export($value, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        while (strlen($value) < $length) {
            $value = chr(0) . $value;
        }
        return $value;
    }
    
    public static function bigIntToDER($value)
    {
        $value = gmp_export($value, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        if (ord($value[0]) & 0x80) {
            $value = chr(0) . $value;
        }
        return $value;
    }
    
    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return $length === 0 || (substr($haystack, -$length) === $needle);
    }
    
    public static function getVestingSharePrice($props)
    {
        $total_vesting_fund_amalgam = Asset::fromString($props['total_vesting_fund_amalgam']);
        $total_vesting_shares = Asset::fromString($props['total_vesting_shares']);
        if (($total_vesting_fund_amalgam->amount == 0) || ($total_vesting_shares->amount == 0)) {
            return new Price(new Asset(1000, AssetSymbol::AML), new Asset(1000000, AssetSymbol::AMLV));
        }
        return new Price($total_vesting_shares, $total_vesting_fund_amalgam);
    }
}
