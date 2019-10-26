<?php

require 'utils.php';

if (!isset($_GET['wallet'])) exit();

$wallet = $_GET['wallet'];
if (!isset($_GET['offset'])) $offset = 0;
else
    $offset = $_GET['offset'];
require 'phpQuery.php';

$url = "https://www.blockchain.com/ru/btc/address/$wallet?offset=$offset";

$data = get_content($url);
$doc = phpQuery::newDocument($data);
$address = $doc->find("a[href=/ru/btc/address/$wallet]")->text();
if ($address != $wallet) {
    echo json_encode([
        'success' => 0
    ]);
    exit();
}
$transactions_number = $doc->find('td#n_transactions')->text();
$total_received = $doc->find('td#total_received')->text();
$final_balance = $doc->find('td#final_balance')->text();
$return = [
    'success' => 1,
    'wallet' => $wallet,
    'transactions_number' => $transactions_number,
    'total_received' => $total_received,
    'final_balance' => $final_balance,
    'transactions' => []
];

$divs = $doc->find('div.txdiv');

foreach ($divs as $div) {
    $div = pq($div);

    $hash_link = 'https://www.blockchain.com' . ($div->find('th a')->attr('href'));
    $date = $div->find('th span.pull-right')->text();
    $img_name = $div->find('img')->attr('src');
    $wallets_count = count($div->find('tr td a'));
    if ($img_name == '/Resources/arrow_right_red.png') {
        $type = 'Выплата';
    } else {
        $type = 'Пополнение';
    }
    $sum = $div->find('button')->text();


    $return['transactions'][] = [
        'type' => $type,
        'date' => $date,
        'sum' => $sum,
        'hash_link' => $hash_link,
        'wallets_count' => $wallets_count
    ];

}
echo json_encode($return);



