<?php

namespace Amalgam;

class Operations
{
    public $operations = [];
    
    public function __construct()
    {
        $this->add(0, 'transfer', [
            'from' => Types::typeString(),
            'to' => Types::typeString(),
            'amount' => Types::typeAsset(),
            'memo' => Types::typeString()
        ]);
        $this->add(1, 'transfer_to_vesting', [
            'from' => Types::typeString(),
            'to' => Types::typeString(),
            'amount' => Types::typeAsset()
        ]);
        $this->add(2, 'withdraw_vesting', [
            'account' => Types::typeString(),
            'vesting_shares' => Types::typeAsset()
        ]);
        $this->add(3, 'limit_order_create', [
            'owner' => Types::typeString(),
            'orderid' => Types::typeUint32(),
            'amount_to_sell' => Types::typeAsset(),
            'min_to_receive' => Types::typeAsset(),
            'fill_or_kill' => Types::typeBool(),
            'expiration' => Types::typeTimePointSec()
        ]);
        $this->add(4, 'limit_order_cancel', [
            'owner' => Types::typeString(),
            'orderid' => Types::typeUint32()
        ]);
        $this->add(5, 'feed_publish', [
            'publisher' => Types::typeString(),
            'exchange_rate' => Types::typePrice()
        ]);
        $this->add(6, 'convert', [
            'owner' => Types::typeString(),
            'requestid' => Types::typeUint32(),
            'amount' => Types::typeAsset()
        ]);
        $this->add(7, 'account_create', [
            'fee' => Types::typeAsset(),
            'creator' => Types::typeString(),
            'new_account_name' => Types::typeString(),
            'owner' => Types::typeAuthority(),
            'active' => Types::typeAuthority(),
            'posting' => Types::typeAuthority(),
            'memo_key' => Types::typePublicKey(),
            'json_metadata' => Types::typeString()
        ]);
        $this->add(8, 'account_update', [
            'account' => Types::typeString(),
            'owner' => Types::typeOptional(Types::typeAuthority()),
            'active' => Types::typeOptional(Types::typeAuthority()),
            'posting' => Types::typeOptional(Types::typeAuthority()),
            'memo_key' => Types::typePublicKey(),
            'json_metadata' => Types::typeString()
        ]);
        $this->add(9, 'witness_update', [
            'owner' => Types::typeString(),
            'url' => Types::typeString(),
            'block_signing_key' => Types::typePublicKey(),
            'props' => Types::typeChainProperties(),
            'fee' => Types::typeAsset()
        ]);
        $this->add(10, 'account_witness_vote', [
            'account' => Types::typeString(),
            'witness' => Types::typeString(),
            'approve' => Types::typeBool()
        ]);
        $this->add(11, 'account_witness_proxy', [
            'account' => Types::typeString(),
            'proxy' => Types::typeString()
        ]);
        $this->add(14, 'set_withdraw_vesting_route', [
            'from_account' => Types::typeString(),
            'to_account' => Types::typeString(),
            'percent' => Types::typeUint16(),
            'auto_vest' => Types::typeBool()
        ]);
        $this->add(15, 'limit_order_create2', [
            'owner' => Types::typeString(),
            'orderid' => Types::typeUint32(),
            'amount_to_sell' => Types::typeAsset(),
            'exchange_rate' => Types::typePrice(),
            'fill_or_kill' => Types::typeBool(),
            'expiration' => Types::typeTimePointSec()
        ]);
        $this->add(16, 'request_account_recovery', [
            'recovery_account' => Types::typeString(),
            'account_to_recover' => Types::typeString(),
            'new_owner_authority' => Types::typeAuthority(),
            'extensions' => Types::typeSet(Types::typeFutureExtensions())
        ]);
        $this->add(17, 'recover_account', [
            'account_to_recover' => Types::typeString(),
            'new_owner_authority' => Types::typeAuthority(),
            'recent_owner_authority' => Types::typeAuthority(),
            'extensions' => Types::typeSet(Types::typeFutureExtensions())
        ]);
        $this->add(18, 'change_recovery_account', [
            'account_to_recover' => Types::typeString(),
            'new_recovery_account' => Types::typeString(),
            'extensions' => Types::typeSet(Types::typeFutureExtensions())
        ]);
        $this->add(19, 'escrow_transfer', [
            'from' => Types::typeString(),
            'to' => Types::typeString(),
            'abd_amount' => Types::typeAsset(),
            'amalgam_amount' => Types::typeAsset(),
            'escrow_id' => Types::typeUint32(),
            'agent' => Types::typeString(),
            'fee' => Types::typeAsset(),
            'json_meta' => Types::typeString(),
            'ratification_deadline' => Types::typeTimePointSec(),
            'escrow_expiration' => Types::typeTimePointSec()
        ]);
        $this->add(20, 'escrow_dispute', [
            'from' => Types::typeString(),
            'to' => Types::typeString(),
            'agent' => Types::typeString(),
            'who' => Types::typeString(),
            'escrow_id' => Types::typeUint32()
        ]);
        $this->add(21, 'escrow_release', [
            'from' => Types::typeString(),
            'to' => Types::typeString(),
            'agent' => Types::typeString(),
            'who' => Types::typeString(),
            'receiver' => Types::typeString(),
            'escrow_id' => Types::typeUint32(),
            'abd_amount' => Types::typeAsset(),
            'amalgam_amount' => Types::typeAsset()
        ]);
        $this->add(22, 'escrow_approve', [
            'from' => Types::typeString(),
            'to' => Types::typeString(),
            'agent' => Types::typeString(),
            'who' => Types::typeString(),
            'escrow_id' => Types::typeUint32(),
            'approve' => Types::typeBool()
        ]);
        $this->add(23, 'transfer_to_savings', [
            'from' => Types::typeString(),
            'to' => Types::typeString(),
            'amount' => Types::typeAsset(),
            'memo' => Types::typeString()
        ]);
        $this->add(24, 'transfer_from_savings', [
            'from' => Types::typeString(),
            'request_id' => Types::typeUint32(),
            'to' => Types::typeString(),
            'amount' => Types::typeAsset(),
            'memo' => Types::typeString()
        ]);
        $this->add(25, 'cancel_transfer_from_savings', [
            'from' => Types::typeString(),
            'request_id' => Types::typeUint32()
        ]);
        $this->add(27, 'decline_voting_rights', [
            'account' => Types::typeString(),
            'decline' => Types::typeBool()
        ]);
        $this->add(28, 'delegate_vesting_shares', [
            'delegator' => Types::typeString(),
            'delegatee' => Types::typeString(),
            'vesting_shares' => Types::typeAsset()
        ]);
        $this->add(29, 'tbd1', [
            'from' => Types::typeAsset()
        ]);
        $this->add(30, 'tbd2', [
            'from' => Types::typeAsset()
        ]);
        $this->add(31, 'tbd3', [
            'from' => Types::typeAsset()
        ]);
        $this->add(32, 'tbd4', [
            'from' => Types::typeAsset()
        ]);
        $this->add(33, 'tbd5', [
            'from' => Types::typeAsset()
        ]);
        $this->add(34, 'tbd6', [
            'from' => Types::typeAsset()
        ]);
        $this->add(35, 'tbd7', [
            'from' => Types::typeAsset()
        ]);
        $this->add(36, 'tbd8', [
            'from' => Types::typeAsset()
        ]);
        $this->add(37, 'tbd9', [
            'from' => Types::typeAsset()
        ]);
        $this->add(38, 'tbd10', [
            'from' => Types::typeAsset()
        ]);
    }
    
    private function add($id, $name, $params)
    {
        $this->operations[] = Types::typeNamedOperation($id, $name, $params);
    }
}
