<?php

require 'wallet.php';

function get_content($url, $data = [], $getlink = null)
{

    $ch = curl_init($url);
    if ($data != null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . 'cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . 'cookie.txt');
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

function getWallets($mysqli, $offset, $limit){
    $ret = [];
    $q = $mysqli->query("select id from wallets limit $offset,$limit");
    while ($row = mysqli_fetch_array($q)){
        $wallet_id = $row[0];
        $wallet = new wallet($mysqli);
        $wallet->createWalletById($wallet_id);
        $ret[] = $wallet;
    }
}
