<?php

namespace Amalgam;

class Operations
{
    public $operations = [];
    
    public function __construct()
    {
        $this->add(0, 'vote', [
            'voter' => Types::typeString(),
            'author' => Types::typeString(),
            'permlink' => Types::typeString(),
            'weight' => Types::typeInt16()
        ]);
        $this->add(1, 'comment', [
            'parent_author' => Types::typeString(),
            'parent_permlink' => Types::typeString(),
            'author' => Types::typeString(),
            'permlink' => Types::typeString(),
            'json_metadata' => Types::typeString()
        ]);
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
        $this->add(5, 'limit_order_create', [
            'owner' => Types::typeString(),
            'orderid' => Types::typeUint32(),
            'amount_to_sell' => Types::typeAsset(),
            'min_to_receive' => Types::typeAsset(),
            'fill_or_kill' => Types::typeBool(),
            'expiration' => Types::typeTimePointSec()
        ]);
        $this->add(6, 'limit_order_cancel', [
            'owner' => Types::typeString(),
            'orderid' => Types::typeUint32()
        ]);
        $this->add(7, 'feed_publish', [
            'publisher' => Types::typeString(),
            'exchange_rate' => Types::typePrice()
        ]);
        $this->add(8, 'convert', [
            'owner' => Types::typeString(),
            'requestid' => Types::typeUint32(),
            'amount' => Types::typeAsset()
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
        $this->add(12, 'account_witness_vote', [
            'account' => Types::typeString(),
            'witness' => Types::typeString(),
            'approve' => Types::typeBool()
        ]);
        $this->add(13, 'account_witness_proxy', [
            'account' => Types::typeString(),
            'proxy' => Types::typeString()
        ]);
        $this->add(17, 'delete_comment', [
            'author' => Types::typeString(),
            'permlink' => Types::typeString()
        ]);
        $this->add(19, 'comment_options', [
            'author' => Types::typeString(),
            'permlink' => Types::typeString(),
            'max_accepted_payout' => Types::typeAsset(),
            'percent_amalgam_dollars' => Types::typeUint16(),
            'allow_votes' => Types::typeBool(),
            'allow_curation_rewards' => Types::typeBool(),
            'extensions' => Types::typeSet(Types::typeCommentOptionsExtension())
        ]);
        $this->add(20, 'set_withdraw_vesting_route', [
            'from_account' => Types::typeString(),
            'to_account' => Types::typeString(),
            'percent' => Types::typeUint16(),
            'auto_vest' => Types::typeBool()
        ]);
        $this->add(21, 'limit_order_create2', [
            'owner' => Types::typeString(),
            'orderid' => Types::typeUint32(),
            'amount_to_sell' => Types::typeAsset(),
            'exchange_rate' => Types::typePrice(),
            'fill_or_kill' => Types::typeBool(),
            'expiration' => Types::typeTimePointSec()
        ]);
        $this->add(22, 'challenge_authority', [
            'challenger' => Types::typeString(),
            'challenged' => Types::typeString(),
            'require_owner' => Types::typeBool()
        ]);
        $this->add(23, 'prove_authority', [
            'challenged' => Types::typeString(),
            'require_owner' => Types::typeBool()
        ]);
        $this->add(24, 'request_account_recovery', [
            'recovery_account' => Types::typeString(),
            'account_to_recover' => Types::typeString(),
            'new_owner_authority' => Types::typeAuthority(),
            'extensions' => Types::typeSet(Types::typeFutureExtensions())
        ]);
        $this->add(25, 'recover_account', [
            'account_to_recover' => Types::typeString(),
            'new_owner_authority' => Types::typeAuthority(),
            'recent_owner_authority' => Types::typeAuthority(),
            'extensions' => Types::typeSet(Types::typeFutureExtensions())
        ]);
        $this->add(26, 'change_recovery_account', [
            'account_to_recover' => Types::typeString(),
            'new_recovery_account' => Types::typeString(),
            'extensions' => Types::typeSet(Types::typeFutureExtensions())
        ]);
        $this->add(27, 'escrow_transfer', [
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
        $this->add(28, 'escrow_dispute', [
            'from' => Types::typeString(),
            'to' => Types::typeString(),
            'agent' => Types::typeString(),
            'who' => Types::typeString(),
            'escrow_id' => Types::typeUint32()
        ]);
        $this->add(29, 'escrow_release', [
            'from' => Types::typeString(),
            'to' => Types::typeString(),
            'agent' => Types::typeString(),
            'who' => Types::typeString(),
            'receiver' => Types::typeString(),
            'escrow_id' => Types::typeUint32(),
            'abd_amount' => Types::typeAsset(),
            'amalgam_amount' => Types::typeAsset()
        ]);
        $this->add(31, 'escrow_approve', [
            'from' => Types::typeString(),
            'to' => Types::typeString(),
            'agent' => Types::typeString(),
            'who' => Types::typeString(),
            'escrow_id' => Types::typeUint32(),
            'approve' => Types::typeBool()
        ]);
        $this->add(32, 'transfer_to_savings', [
            'from' => Types::typeString(),
            'to' => Types::typeString(),
            'amount' => Types::typeAsset(),
            'memo' => Types::typeString()
        ]);
        $this->add(33, 'transfer_from_savings', [
            'from' => Types::typeString(),
            'request_id' => Types::typeUint32(),
            'to' => Types::typeString(),
            'amount' => Types::typeAsset(),
            'memo' => Types::typeString()
        ]);
        $this->add(34, 'cancel_transfer_from_savings', [
            'from' => Types::typeString(),
            'request_id' => Types::typeUint32()
        ]);
        $this->add(36, 'decline_voting_rights', [
            'account' => Types::typeString(),
            'decline' => Types::typeBool()
        ]);
        $this->add(37, 'reset_account', [
            'reset_account' => Types::typeString(),
            'account_to_reset' => Types::typeString(),
            'new_owner_authority' => Types::typeAuthority()
        ]);
        $this->add(38, 'set_reset_account', [
            'account' => Types::typeString(),
            'current_reset_account' => Types::typeString(),
            'reset_account' => Types::typeString()
        ]);
        $this->add(39, 'claim_reward_balance', [
            'account' => Types::typeString(),
            'reward_amalgam' => Types::typeAsset(),
            'reward_abd' => Types::typeAsset(),
            'reward_vests' => Types::typeAsset()
        ]);
        $this->add(40, 'delegate_vesting_shares', [
            'delegator' => Types::typeString(),
            'delegatee' => Types::typeString(),
            'vesting_shares' => Types::typeAsset()
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
