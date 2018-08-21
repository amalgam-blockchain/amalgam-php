<?php

namespace Amalgam;

class Operations
{
    public $operations = [];
    
    public function __construct()
    {
        $this->add(2, 'transfer', [
                'from' => Types::typeString(),
                'to' => Types::typeString(),
                'amount' => Types::typeAsset(),
                'memo' => Types::typeString()
        ]);
        $this->add(3, 'transfer_to_vesting', [
                'from' => Types::typeString(),
                'to' => Types::typeString(),
                'amount' => Types::typeAsset()
        ]);
        $this->add(4, 'withdraw_vesting', [
                'account' => Types::typeString(),
                'vesting_shares' => Types::typeAsset()
        ]);
        $this->add(7, 'feed_publish', [
                'publisher' => Types::typeString(),
                'exchange_rate' => Types::typePrice()
        ]);
        $this->add(9, 'account_create', [
                'fee' => Types::typeAsset(),
                'creator' => Types::typeString(),
                'new_account_name' => Types::typeString(),
                'owner' => Types::typeAuthority(),
                'active' => Types::typeAuthority(),
                'posting' => Types::typeAuthority(),
                'memo_key' => Types::typePublicKey(),
                'json_metadata' => Types::typeString()
        ]);
        $this->add(10, 'account_update', [
                'account' => Types::typeString(),
                'owner' => Types::typeOptional(Types::typeAuthority()),
                'active' => Types::typeOptional(Types::typeAuthority()),
                'posting' => Types::typeOptional(Types::typeAuthority()),
                'memo_key' => Types::typePublicKey(),
                'json_metadata' => Types::typeString()
        ]);
        $this->add(11, 'witness_update', [
                'owner' => Types::typeString(),
                'url' => Types::typeString(),
                'block_signing_key' => Types::typePublicKey(),
                'props' => Types::typeChainProperties(),
                'fee' => Types::typeAsset()
        ]);
        $this->add(39, 'claim_reward_balance', [
                'account' => Types::typeString(),
                'reward_amalgam' => Types::typeAsset(),
                'reward_abd' => Types::typeAsset(),
                'reward_vests' => Types::typeAsset()
        ]);
        $this->add(41, 'account_create_with_delegation', [
                'fee' => Types::typeAsset(),
                'delegation' => Types::typeAsset(),
                'creator' => Types::typeString(),
                'new_account_name' => Types::typeString(),
                'owner' => Types::typeAuthority(),
                'active' => Types::typeAuthority(),
                'posting' => Types::typeAuthority(),
                'memo_key' => Types::typePublicKey(),
                'json_metadata' => Types::typeString(),
                'extensions' => Types::typeSet(Types::typeFutureExtensions())
        ]);
    }
    
    private function add($id, $name, $params)
    {
        $this->operations[] = Types::typeNamedOperation($id, $name, $params);
    }
}
