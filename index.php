<?php

require 'utils.php';

if (!isset($_GET['wallet'])) exit();

$wallet = $_GET['wallet'];
if (!isset($_GET['offset'])) $offset = 1;
else
    $offset = $_GET['offset'];
require 'phpQuery.php';

$url = "https://www.blockchain.com/ru/btc/address/$wallet?page=$offset";

$data = get_content($url);
$doc = phpQuery::newDocument($data);
$address = $doc->find("span.sc-1ryi78w-0.iDdexO.sc-16b9dsl-1.epNUkC.u3ufsr-0.fMGGJf:eq(0)")->text();
if ($address != $wallet) {
    echo json_encode([
        'success' => 0,
        'address' => $address,
        'wallet' => $wallet
    ]);
    exit();
}
$transactions_number = $doc->find('#__next > div.sc-1myx216-0.iygrgv > div > div:nth-child(3) > div.sc-2msc2s-0.kBCojR > div > div:nth-child(3) > div:nth-child(2) > span')->text();
$total_received = $doc->find('#__next > div.sc-1myx216-0.iygrgv > div > div:nth-child(3) > div.sc-2msc2s-0.kBCojR > div > div:nth-child(4) > div:nth-child(2) > span')->text();
$final_balance = $doc->find('#__next > div.sc-1myx216-0.iygrgv > div > div:nth-child(3) > div.sc-2msc2s-0.kBCojR > div > div:nth-child(6) > div:nth-child(2) > span')->text();
$return = [
    'success' => 1,
    'wallet' => $wallet,
    'transactions_number' => $transactions_number,
    'total_received' => $total_received,
    'final_balance' => $final_balance,
    'transactions' => []
];

$divs = $doc->find('div.sc-1fp9csv-0.iFnncD');

foreach ($divs as $div) {
    $div = pq($div);

    $hash_link = 'https://www.blockchain.com' . ($div->find('.sc-1r996ns-0.dkIjuo.sc-1tbyx6t-1.fDJjsh.iklhnl-0.dBPJKC')->attr('href'));
    $date = $div->find('span.sc-1ryi78w-0.iDdexO.sc-16b9dsl-1.epNUkC.u3ufsr-0.fMGGJf:eq(0)')->text();
    $wallets_count = count($div->find('tr td a'));
    $button = $div->find('.sc-1ryi78w-0.iDdexO.sc-16b9dsl-1.epNUkC.u3ufsr-0.fMGGJf.sc-1uk35kp-0.jgKjTo');
    $type = "Выплата";
    if(!$button->text()) {
        $button = $div->find('.sc-1ryi78w-0.iDdexO.sc-16b9dsl-1.epNUkC.u3ufsr-0.fMGGJf.sc-1uk35kp-0.hmdfEi');
        $type = "Пополнение";
    }
    $sum = $button->text();


    $return['transactions'][] = [
        'type' => $type,
        'date' => $date,
        'sum' => $sum,
        'hash_link' => $hash_link,
        'wallets_count' => $wallets_count
    ];

}
echo json_encode($return);



