<?php

require 'bd.php';
require 'utils.php';

$wallet = $_GET['wallet'];
$wallet_id = $_GET['wallet_id'];
if($wallet) {
    if(!mysqli_num_rows($mysqli->query("select * from wallets where wallet = '$wallet'"))){
        $w = new wallet($mysqli);
        $params = get_content("t.shn-host.ru/btc_monitor/?wallet=$wallet");
        $params = json_decode($params, 1);
        if(!$params['success'])exit();
        $transactions_number = $params['transactions_number'];
        $total_received = $params['total_received'];
        $final_balance = $params['final_balance'];
        $w->createWalletByParams($wallet, $transactions_number, $total_received, $final_balance);
        $w->setUpdate(0);
    } else {
        $wallet_id = mysqli_fetch_row($mysqli->query("select id from wallets where wallet = '$wallet'"))[0];
        $w = new wallet($mysqli);
        $w->createWalletById($wallet_id);
        $w->setUpdate(0);
    }
} elseif($wallet_id) {
    $w = new wallet($mysqli);
    $w->createWalletById($wallet_id);
    $w->setUpdate(0);
} else exit();
$arr = [];
for($i = 0; ; $i++){
    $offset = $i*50;
    $res = get_content("t.shn-host.ru/btc_monitor/?offset=$offset&wallet=$wallet");
    $res = json_decode($res, 1);
    if($w->transactions_number != $res['transactions_number'])
    {
        $w->setTransactionsNumber($res['transactions_number']);
        $w->setFinalBalance($res['final_balance']);
        $w->setTotalReceived($res['total_received']);
    }
    $res = $res['transactions'];
    if(!count($res)){
        $w->setUpdate(-1);
        break;
    }
    foreach($res as $transaction){
    if(!$w->addTransaction($transaction['type'], $transaction['date'], $transaction['sum'], $transaction['hash_link'], $transaction['wallets_count'])){
        $w->setUpdate(-1);
        exit();
    }
    }
    $w->setUpdate($offset);
}


