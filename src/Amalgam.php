<?php

namespace Amalgam;

use Yii;
use yii\base\Component;

class Amalgam extends Component {
    
    public $node;
    
    public function executeJson($json)
    {
        $connection = new Connection();
        $connection->nodeUrl = $this->node;
        return $connection->execJson($json);
    }
    
    public function executeJsonExternal($json)
    {
        $connection = new Connection();
        $connection->nodeUrl = $this->node;
        $array = json_decode($json, true);
        if (is_array($array) && array_key_exists('params', $array)) {
            $params = $array['params'];
            if (is_array($params) && (count($params) > 0)) {
                return $connection->execute($params[0], $params[1], $params[2], array_key_exists('id', $array) ? $array['id'] : null);
            }
        }
        return null;
    }
    
    public function execute($apiName, $command, $params = [])
    {
        $connection = new Connection();
        $connection->nodeUrl = $this->node;
        return $connection->exec($apiName, $command, $params);
    }
    
    public function broadcast($wif, $command, $params)
    {
        $connection = new Connection();
        $connection->nodeUrl = $this->node;
        $transaction = new Transaction($connection);
        $transaction->addOperation($command, $params);
        $transaction->sign([$wif]);
        return $connection->exec('network_broadcast_api', 'broadcast_transaction_synchronous', [$transaction->getTx()]);
    }
    
    // Database API

    public function getBlockHeader($blockNum)
    {
        return $this->execute('database_api', 'get_block_header', [$blockNum]);
    }
    
    public function getBlock($blockNum)
    {
        return $this->execute('database_api', 'get_block', [$blockNum]);
    }
    
    public function getOpsInBlock($blockNum, $onlyVirtual)
    {
        return $this->execute('database_api', 'get_ops_in_block', [$blockNum, $onlyVirtual]);
    }
    
    public function getConfig()
    {
        return $this->execute('database_api', 'get_config');
    }
    
    public function getDynamicGlobalProperties()
    {
        return $this->execute('database_api', 'get_dynamic_global_properties');
    }
    
    public function getChainProperties()
    {
        return $this->execute('database_api', 'get_chain_properties');
    }
    
    public function getFeedHistory()
    {
        return $this->execute('database_api', 'get_feed_history');
    }

    public function getCurrentMedianHistoryPrice()
    {
        return $this->execute('database_api', 'get_current_median_history_price');
    }
    
    public function getWitnessSchedule()
    {
        return $this->execute('database_api', 'get_witness_schedule');
    }

    public function getHardforkVersion()
    {
        return $this->execute('database_api', 'get_hardfork_version');
    }

    public function getNextScheduledHardfork()
    {
        return $this->execute('database_api', 'get_next_scheduled_hardfork');
    }

    public function getKeyReferences($key)
    {
        return $this->execute('account_by_key_api', 'get_key_references', [$key]);
    }

    public function getAccounts($names)
    {
        return $this->execute('database_api', 'get_accounts', [$names]);
    }
    
    public function lookupAccountNames($accountNames)
    {
        return $this->execute('database_api', 'lookup_account_names', [$accountNames]);
    }
    
    public function lookupAccounts($lowerBoundName, $limit)
    {
        return $this->execute('database_api', 'lookup_accounts', [$lowerBoundName, $limit]);
    }
    
    public function getAccountCount()
    {
        return $this->execute('database_api', 'get_account_count');
    }
    
    public function getConversionRequests($accountName)
    {
        return $this->execute('database_api', 'get_conversion_requests', [$accountName]);
    }
    
    public function getAccountHistory($name, $from, $limit)
    {
        return $this->execute('database_api', 'get_account_history', [$name, $from, $limit]);
    }
    
    public function getOwnerHistory($account)
    {
        return $this->execute('database_api', 'get_owner_history', [$account]);
    }
    
    public function getRecoveryRequest($account)
    {
        return $this->execute('database_api', 'get_recovery_request', [$account]);
    }

    public function getEscrow($from, $escrowId)
    {
        return $this->execute('database_api', 'get_escrow', [$from, $escrowId]);
    }
    
    public function getWithdrawRoutes($account, $withdrawRouteType)
    {
        return $this->execute('database_api', 'get_withdraw_routes', [$account, $withdrawRouteType]);
    }
    
    public function getAccountBandwidth($account, $bandwidthType)
    {
        return $this->execute('database_api', 'get_account_bandwidth', [$account, $bandwidthType]);
    }
    
    public function getSavingsWithdrawFrom($account)
    {
        return $this->execute('database_api', 'get_savings_withdraw_from', [$account]);
    }
    
    public function getSavingsWithdrawTo($account)
    {
        return $this->execute('database_api', 'get_savings_withdraw_to', [$account]);
    }
    
    public function getOrderBook($limit)
    {
        return $this->execute('database_api', 'get_order_book', [$limit]);
    }
    
    public function getOpenOrders($owner)
    {
        return $this->execute('database_api', 'get_open_orders', [$owner]);
    }
    
    public function getLiquidityQueue($startAccount, $limit)
    {
        return $this->execute('database_api', 'get_liquidity_queue', [$startAccount, $limit]);
    }
    
    public function getTransactionHex($trx)
    {
        return $this->execute('database_api', 'get_transaction_hex', [$trx]);
    }
    
    public function getTransaction($trxId)
    {
        return $this->execute('database_api', 'get_transaction', [$trxId]);
    }
    
    public function getRequiredSignatures($trx, $availableKeys)
    {
        return $this->execute('database_api', 'get_required_signatures', [$trx, $availableKeys]);
    }
    
    public function getPotentialSignatures($trx)
    {
        return $this->execute('database_api', 'get_potential_signatures', [$trx]);
    }
    
    public function verifyAuthority($trx)
    {
        return $this->execute('database_api', 'verify_authority', [$trx]);
    }
    
    public function verifyAccountAuthority($nameOrId, $signers)
    {
        return $this->execute('database_api', 'verify_account_authority', [$nameOrId, $signers]);
    }
    
    public function getActiveVotes($permlink)
    {
        return $this->execute('database_api', 'get_active_votes', [$permlink]);
    }
    
    public function getAccountVotes($voter)
    {
        return $this->execute('database_api', 'get_account_votes', [$voter]);
    }
    
    public function getContent($permlink)
    {
        return $this->execute('database_api', 'get_content', [$permlink]);
    }
    
    public function getContentReplies($permlink)
    {
        return $this->execute('database_api', 'get_content_replies', [$permlink]);
    }
    
    public function getWitnesses($witnessIds)
    {
        return $this->execute('database_api', 'get_witnesses', [$witnessIds]);
    }
    
    public function getWitnessByAccount($accountName)
    {
        return $this->execute('database_api', 'get_witness_by_account', [$accountName]);
    }
    
    public function getWitnessesByVote($from, $limit)
    {
        return $this->execute('database_api', 'get_witnesses_by_vote', [$from, $limit]);
    }
    
    public function lookupWitnessAccounts($lowerBoundName, $limit)
    {
        return $this->execute('database_api', 'lookup_witness_accounts', [$lowerBoundName, $limit]);
    }
    
    public function getWitnessCount()
    {
        return $this->execute('database_api', 'get_witness_count');
    }
    
    public function getActiveWitnesses()
    {
        return $this->execute('database_api', 'get_active_witnesses');
    }
    
    public function getMinerQueue()
    {
        return $this->execute('database_api', 'get_miner_queue');
    }
    
    public function getRewardFund($name)
    {
        return $this->execute('database_api', 'get_reward_fund', [$name]);
    }
    
    public function getVestingDelegations($account, $from, $limit)
    {
        return $this->execute('database_api', 'get_vesting_delegations', [$account, $from, $limit]);
    }
    
    // Network Broadcast API
    
    public function vote($wif, $voter, $author, $permlink, $weight)
    {
        return $this->broadcast($wif, 'vote', [
            'voter' => $voter,
            'author' => $author,
            'permlink' => $permlink,
            'weight' => $weight,
        ]);
    }
    
    public function comment($wif, $parentAuthor, $parentPermlink, $author, $permlink, $jsonMetadata)
    {
        return $this->broadcast($wif, 'comment', [
            'parent_author' => $parentAuthor,
            'parent_permlink' => $parentPermlink,
            'author' => $author,
            'permlink' => $permlink,
            'json_metadata' => $jsonMetadata,
        ]);
    }
    
    public function transfer($wif, $from, $to, $amount, $memo)
    {
        return $this->broadcast($wif, 'transfer', [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'memo' => $memo,
        ]);
    }
    
    public function transferToVesting($wif, $from, $to, $amount)
    {
        return $this->broadcast($wif, 'transfer_to_vesting', [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
        ]);
    }
    
    public function withdrawVesting($wif, $account, $vestingShares)
    {
        return $this->broadcast($wif, 'withdraw_vesting', [
            'account' => $account,
            'vesting_shares' => $vestingShares,
        ]);
    }
    
    public function limitOrderCreate($wif, $owner, $orderId, $amountToSell, $minToReceive, $fillOrKill, $expiration)
    {
        return $this->broadcast($wif, 'limit_order_create', [
            'owner' => $owner,
            'orderid' => $orderId,
            'amount_to_sell' => $amountToSell,
            'min_to_receive' => $minToReceive,
            'fill_or_kill' => $fillOrKill,
            'expiration' => $expiration,
        ]);
    }
    
    public function limitOrderCancel($wif, $owner, $orderId)
    {
        return $this->broadcast($wif, 'limit_order_cancel', [
            'owner' => $owner,
            'orderid' => $orderId,
        ]);
    }
    
    public function feedPublish($wif, $publisher, $exchangeRateBase, $exchangeRateQuote)
    {
        return $this->broadcast($wif, 'feed_publish', [
            'publisher' => $publisher,
            'exchange_rate' => [
                'base' => $exchangeRateBase,
                'quote' => $exchangeRateQuote,
            ],
        ]);
    }
    
    public function convert($wif, $owner, $requestId, $amount)
    {
        return $this->broadcast($wif, 'convert', [
            'owner' => $owner,
            'requestid' => $requestId,
            'amount' => $amount,
        ]);
    }
    
    public function accountCreate($wif, $fee, $creator, $newAccountName,
            $owner, $active, $posting, $memoKey, $jsonMetadata)
    {
        return $this->broadcast($wif, 'account_create', [
            'fee' => $fee,
            'creator' => $creator,
            'new_account_name' => $newAccountName,
            'owner' => $owner,
            'active' => $active,
            'posting' => $posting,
            'memo_key' => $memoKey,
            'json_metadata' => $jsonMetadata,
        ]);
    }
    
    public function accountUpdate($wif, $account,
            $owner, $active, $posting, $memoKey, $jsonMetadata)
    {
        return $this->broadcast($wif, 'account_update', [
            'account' => $account,
            'owner' => $owner,
            'active' => $active,
            'posting' => $posting,
            'memo_key' => $memoKey,
            'json_metadata' => $jsonMetadata,
        ]);
    }
    
    public function witnessUpdate($wif, $owner, $url, $block_signing_key, $props, $fee)
    {
        return $this->broadcast($wif, 'witness_update', [
            'owner' => $owner,
            'url' => $url,
            'block_signing_key' => $block_signing_key,
            'props' => $props,
            'fee' => $fee,
        ]);
    }
    
    public function accountWitnessVote($wif, $account, $witness, $approve)
    {
        return $this->broadcast($wif, 'account_witness_vote', [
            'account' => $account,
            'witness' => $witness,
            'approve' => $approve,
        ]);
    }
    
    public function accountWitnessProxy($wif, $account, $proxy)
    {
        return $this->broadcast($wif, 'account_witness_proxy', [
            'account' => $account,
            'proxy' => $proxy,
        ]);
    }
    
    public function deleteComment($wif, $author, $permlink)
    {
        return $this->broadcast($wif, 'delete_comment', [
            'author' => $author,
            'permlink' => $permlink,
        ]);
    }
    
    public function commentOptions($wif, $author, $permlink, $maxAcceptedPayout, $percentAmalgamDollars,
            $allowVotes, $allowCurationRewards, $extensions)
    {
        return $this->broadcast($wif, 'comment_options', [
            'author' => $author,
            'permlink' => $permlink,
            'max_accepted_payout' => $maxAcceptedPayout,
            'percent_amalgam_dollars' => $percentAmalgamDollars,
            'allow_votes' => $allowVotes,
            'allow_curation_rewards' => $allowCurationRewards,
            'extensions' => $extensions,
        ]);
    }
    
    public function setWithdrawVestingRoute($wif, $fromAccount, $toAccount, $percent, $autoVest)
    {
        return $this->broadcast($wif, 'set_withdraw_vesting_route', [
            'from_account' => $fromAccount,
            'to_account' => $toAccount,
            'percent' => $percent,
            'auto_vest' => $autoVest,
        ]);
    }
    
    public function limitOrderCreate2($wif, $owner, $orderId, $amountToSell, $exchangeRateBase, $exchangeRateQuote, $fillOrKill, $expiration)
    {
        return $this->broadcast($wif, 'limit_order_create2', [
            'owner' => $owner,
            'orderid' => $orderId,
            'amount_to_sell' => $amountToSell,
            'exchange_rate' => [
                'base' => $exchangeRateBase,
                'quote' => $exchangeRateQuote,
            ],
            'fill_or_kill' => $fillOrKill,
            'expiration' => $expiration,
        ]);
    }
    
    public function challengeAuthority($wif, $challenger, $challenged, $requireOwner)
    {
        return $this->broadcast($wif, 'challenge_authority', [
            'challenger' => $challenger,
            'challenged' => $challenged,
            'require_owner' => $requireOwner,
        ]);
    }
    
    public function proveAuthority($wif, $challenged, $requireOwner)
    {
        return $this->broadcast($wif, 'prove_authority', [
            'challenged' => $challenged,
            'require_owner' => $requireOwner,
        ]);
    }
    
    public function requestAccountRecovery($wif, $recoveryAccount, $accountToRecover, $newOwnerAuthority, $extensions)
    {
        return $this->broadcast($wif, 'request_account_recovery', [
            'recovery_account' => $recoveryAccount,
            'account_to_recover' => $accountToRecover,
            'new_owner_authority' => $newOwnerAuthority,
            'extensions' => $extensions,
        ]);
    }
    
    public function recoverAccount($wif, $accountToRecover, $newOwnerAuthority, $recentOwnerAuthority, $extensions)
    {
        return $this->broadcast($wif, 'recover_account', [
            'account_to_recover' => $accountToRecover,
            'new_owner_authority' => $newOwnerAuthority,
            'recent_owner_authority' => $recentOwnerAuthority,
            'extensions' => $extensions,
        ]);
    }
    
    public function changeRecoveryAccount($wif, $accountToRecover, $newRecoveryAccount, $extensions)
    {
        return $this->broadcast($wif, 'change_recovery_account', [
            'account_to_recover' => $accountToRecover,
            'new_recovery_account' => $newRecoveryAccount,
            'extensions' => $extensions,
        ]);
    }
    
    public function escrowTransfer($wif, $from, $to, $amountABD, $amountAmalgam, $escrowId, $agent, $fee,
            $jsonMeta, $ratificationDeadline, $escrowExpiration)
    {
        return $this->broadcast($wif, 'escrow_transfer', [
            'from' => $from,
            'to' => $to,
            'abd_amount' => $amountABD,
            'amalgam_amount' => $amountAmalgam,
            'escrow_id' => $escrowId,
            'agent' => $agent,
            'fee' => $fee,
            'json_meta' => $jsonMeta,
            'ratification_deadline' => $ratificationDeadline,
            'escrow_expiration' => $escrowExpiration,
        ]);
    }
    
    public function escrowDispute($wif, $from, $to, $agent, $who, $escrowId)
    {
        return $this->broadcast($wif, 'escrow_dispute', [
            'from' => $from,
            'to' => $to,
            'agent' => $agent,
            'who' => $who,
            'escrow_id' => $escrowId,
        ]);
    }
    
    public function escrowRelease($wif, $from, $to, $agent, $who, $receiver, $escrowId, $amountABD, $amountAmalgam)
    {
        return $this->broadcast($wif, 'escrow_release', [
            'from' => $from,
            'to' => $to,
            'agent' => $agent,
            'who' => $who,
            'receiver' => $receiver,
            'escrow_id' => $escrowId,
            'abd_amount' => $amountABD,
            'amalgam_amount' => $amountAmalgam,
        ]);
    }
    
    public function escrowApprove($wif, $from, $to, $agent, $who, $escrowId, $approve)
    {
        return $this->broadcast($wif, 'escrow_approve', [
            'from' => $from,
            'to' => $to,
            'agent' => $agent,
            'who' => $who,
            'escrow_id' => $escrowId,
            'approve' => $approve,
        ]);
    }
    
    public function transferToSavings($wif, $from, $to, $amount, $memo)
    {
        return $this->broadcast($wif, 'transfer_to_savings', [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'memo' => $memo,
        ]);
    }
    
    public function transferFromSavings($wif, $from, $requestId, $to, $amount, $memo)
    {
        return $this->broadcast($wif, 'transfer_from_savings', [
            'from' => $from,
            'request_id' => $requestId,
            'to' => $to,
            'amount' => $amount,
            'memo' => $memo,
        ]);
    }
    
    public function cancelTransferFromSavings($wif, $from, $requestId)
    {
        return $this->broadcast($wif, 'cancel_transfer_from_savings', [
            'from' => $from,
            'request_id' => $requestId,
        ]);
    }
    
    public function declineVotingRights($wif, $account, $decline)
    {
        return $this->broadcast($wif, 'decline_voting_rights', [
            'account' => $account,
            'decline' => $decline,
        ]);
    }
    
    public function resetAccount($wif, $resetAccount, $accountToReset, $newOwnerAuthority)
    {
        return $this->broadcast($wif, 'reset_account', [
            'reset_account' => $resetAccount,
            'account_to_reset' => $accountToReset,
            'new_owner_authority' => $newOwnerAuthority,
        ]);
    }
    
    public function setResetAccount($wif, $account, $currentResetAccount, $resetAccount)
    {
        return $this->broadcast($wif, 'set_reset_account', [
            'account' => $account,
            'current_reset_account' => $currentResetAccount,
            'reset_account' => $resetAccount,
        ]);
    }
    
    public function claimRewardBalance($wif, $account, $rewardAmalgam, $rewardABD, $rewardVests)
    {
        return $this->broadcast($wif, 'claim_reward_balance', [
            'account' => $account,
            'reward_amalgam' => $rewardAmalgam,
            'reward_abd' => $rewardABD,
            'reward_vests' => $rewardVests,
        ]);
    }
    
    public function delegateVestingShares($wif, $delegator, $delegatee, $vestingShares)
    {
        return $this->broadcast($wif, 'delegate_vesting_shares', [
            'delegator' => $delegator,
            'delegatee' => $delegatee,
            'vesting_shares' => $vestingShares,
        ]);
    }
    
    public function accountCreateWithDelegation($wif, $fee, $delegation, $creator, $newAccountName,
            $owner, $active, $posting, $memoKey, $jsonMetadata, $extensions)
    {
        return $this->broadcast($wif, 'account_create_with_delegation', [
            'fee' => $fee,
            'delegation' => $delegation,
            'creator' => $creator,
            'new_account_name' => $newAccountName,
            'owner' => $owner,
            'active' => $active,
            'posting' => $posting,
            'memo_key' => $memoKey,
            'json_metadata' => $jsonMetadata,
            'extensions' => $extensions,
        ]);
    }
    
    // Helper functions
    
    public function getAccount($name)
    {
        $result = $this->getAccounts([$name]);
        return ($result != null) && is_array($result) && !empty($result) ? $result[0] : null;
    }
    
    public function validateAccountName($value)
    {
        $suffix = Yii::t('app', 'Account name should ');
        if (empty($value)) {
            return $suffix . Yii::t('app', 'not be empty');
        }
        $length = strlen($value);
        if ($length < 3) {
            return $suffix . Yii::t('app', 'be longer');
        }
        if ($length > 16) {
            return $suffix . Yii::t('app', 'be shorter');
        }
        if (preg_match('/\./', $value)) {
            $suffix = Yii::t('app', 'Each account segment should ');
        }
        $ref = explode('.', $value);
        foreach ($ref as $label) {
            if (!preg_match('/^[a-z]/', $label)) {
                return $suffix . Yii::t('app', 'start with a lowercase letter');
            }
            if (!preg_match('/^[a-z0-9-]*$/', $label)) {
                return $suffix . Yii::t('app', 'have only lowercase letters, digits or dashes');
            }
            if (preg_match('/--/', $label)) {
                return $suffix . Yii::t('app', 'have only one dash in a row');
            }
            if (!preg_match('/[a-z0-9]$/', $label)) {
                return $suffix . Yii::t('app', 'end with a lowercase letter or digit');
            }
            if (!(strlen($label) >= 3)) {
                return $suffix . Yii::t('app', 'be longer');
            }
        }
        return null;
    }
    
    public function generateKey($name, $password, $role)
    {
        $seed = $name . $role . $password;
        $brainKey = trim($seed);
        $hashSha256 = hash('sha256', $brainKey, true);
        $d = gmp_import($hashSha256, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        $private_key = new PrivateKey($d);
        return $private_key->toPublicKey()->toString();
    }

    public function createAccount($registrarWif, $registrarName, $name, $publicKeys)
    {
        $owner = [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['owner'], 1]],
        ];
        $active = [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['active'], 1]],
        ];
        $posting = [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['posting'], 1]],
        ];
        $properties = $this->getChainProperties();
        $fee = Asset::fromString($properties['account_creation_fee']);
        $fee->amount = $fee->amount * 30;
        return $this->accountCreate($registrarWif, $fee->toString(), $registrarName,
                $name, $owner, $active, $posting, $publicKeys['memo'], '');
    }
    
    public function createAccountWithDelegation($registrarWif, $registrarName, $registrarDelegation, $name, $publicKeys)
    {
        $owner = [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['owner'], 1]],
        ];
        $active = [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['active'], 1]],
        ];
        $posting = [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['posting'], 1]],
        ];
        $properties = $this->getChainProperties();
        if (Utils::endsWith($registrarDelegation, AssetSymbol::AMLV)) {
            $delegation = Asset::fromString($registrarDelegation);
        } else if (Utils::endsWith($registrarDelegation, AssetSymbol::AML)) {
            $price = Utils::getVestingSharePrice($this->getDynamicGlobalProperties());
            $delegation = Asset::fromString($registrarDelegation)->multiply($price);
        } else {
            $delegation = new Asset(0, AssetSymbol::AMLV);
        }
        $fee = Asset::fromString($properties['account_creation_fee']);
        return $this->accountCreateWithDelegation($registrarWif, $fee->toString(),
                $delegation->toString(), $registrarName, $name, $owner, $active, $posting, $publicKeys['memo'], '', []);
    }
    
    public function updateAccount($wif, $name, $publicKeys)
    {
        $owner = array_key_exists('owner', $publicKeys) ? [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['owner'], 1]],
        ] : null;
        $active = array_key_exists('active', $publicKeys) ? [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['active'], 1]],
        ] : null;
        $posting = array_key_exists('posting', $publicKeys) ? [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['posting'], 1]],
        ] : null;
        $memo = array_key_exists('memo', $publicKeys) ? $publicKeys['memo'] : null;
        return $this->accountUpdate($wif, $name, $owner, $active, $posting, $memo, '');
    }
    
    public function updateWitness($wif, $owner, $url, $block_signing_key)
    {
        return $this->witnessUpdate($wif, $owner, $url, $block_signing_key, $this->getChainProperties(),
                (new Asset(0, AssetSymbol::AML))->toString());
    }
}
