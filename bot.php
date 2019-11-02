<?php
//171961446
require "bd.php";
require "utils.php";

$kk = file_get_contents('php://input');
$output = json_decode($kk, TRUE);

$row = mysqli_fetch_row($mysqli->query("select * from mainVars"));

$lastMessage = $row[1];

$t = time();
if (isset($output['callback_query']['data'])) {
    $id = $output['callback_query']['message']['chat']['id'];
    $data = $output['callback_query']['data'];
} else {
    if (!isset($output['message']['chat']['id'])) exit();
    $id = $output['message']['chat']['id'];
    $message = $output['message']['text'];
    $message_id = $output['message']['message_id'];
}


if ($data) {
    $callback_query_id = $output['callback_query']['id'];
    $username = $output['callback_query']['from']['first_name'];
    $message_id = $output['callback_query']['message']['message_id'];
    if (stristr($data, 'getWalletsList.')) {
        $arr = explode('.', $data);
        $offset = $arr[1];
        $limit = $arr[2];
        $array = [];
        $wallets = getWallets($mysqli, $offset, $limit);
        foreach ($wallets as $wallet) {
            $name = $wallet->wallet;
            $wallet_id = $wallet->id;
            $array[] = [createCallbackData($name, "showWallet.$wallet_id.0.15")];
        }
        $arr = [];
        if ($offset - $limit >= 0) {
            $o1 = $offset - $limit;
            $arr[] = createCallbackData('<', "getWalletsList.$o1.$limit");
        }
        $walletsNum = mysqli_num_rows($mysqli->query("select * from wallets"));
        if ($offset + $limit < $walletsNum) {
            $o1 = $offset + $limit;
            $arr[] = createCallbackData('>', "getWalletsList.$o1.$limit");
        }
        $array[] = $arr;
        $array[] = [createCallbackData('Выход', 'exit')];
        editMessageText($token, $id, $message_id, "Список твоих кошельков", createReplyMarkup($array));

    } else if (stristr($data, 'showWallet.')) {
        $arr = explode('.', $data);
        $wallet_id = $arr[1];
        $offset = $arr[2];
        $limit = $arr[3];
        $wallet = new wallet($mysqli);
        $wallet->createWalletById($wallet_id);
        $text = "";
        if ($wallet->is_updating) {
            $transactions_num = mysqli_num_rows($mysqli->query("select * from transactions where wallet_id=$wallet_id"));
            $all_transactions_num = $wallet->transactions_number;
            $text = "<b>Кошелёк обновляется.</b>\n" .
                "<b>Обработано платежей:</b> <code>$transactions_num/$all_transactions_num</code>\n";
            editMessageText($token, $id, $message_id, $text, createReplyMarkup([
                [
                    createCallbackData('Выход', 'exit')
                ]
            ]));
        }
        $array = [];
        $trs = $wallet->getTransactions($offset, $limit);
        foreach ($trs as $t) {
            $type = $t['type'];
            $sum = $t['sum'];
            $tr_id = $t['id'];
            $array[] = [createCallbackData("$type $sum", "getTransaction.$tr_id.$wallet_id.$offset.$limit")];
        }
        $arr = [];
        if ($offset - $limit >= 0) {
            $o1 = $offset - $limit;
            $arr[] = createCallbackData('<', "showWallet.$wallet_id.$o1.$limit");
        }
        if ($offset + $limit < $wallet->getTransactionsCount()) {
            $o1 = $offset + $limit;
            $arr[] = createCallbackData('>', "showWallet.$wallet_id.$o1.$limit");
        }
        $array[] = $arr;
        $name = $wallet->wallet;
        $tn = $wallet->transactions_number;
        $tr = $wallet->total_received;
        $fb = $wallet->final_balance;
        $desc = $wallet->description;
        $text = "Кошелек: <b>$name</b>\n" .
            "Количество транзакций: <b>$tn</b>\n" .
            "Всего получено: <b>$tr</b>\n" .
            "Баланс: <b>$fb</b>\n\n" .
            "Описание: <i>$desc</i>\n";
        $array[] = [createCallbackData("Изменить описание", "editDescription.$wallet_id")];
        $array[] = [createCallbackData("Обновить кошелек", "updateWallet.$name")];
        $array[] = [createCallbackData("Удалить кошелек", "deleteWallet.$wallet_id")];
        $array[] = [createCallbackData("Выход", "exit")];
        editMessageText($token, $id, $message_id, $text, createReplyMarkup($array));

    } else if (stristr($data, 'editDescription.')) {
        $text = "Введи описание для кошелька";
        answerCallbackQuery($token, $callback_query_id, $text);
        $array = [
            [
                createCallbackData("Выход", "exit")
            ]
        ];
        editMessageText($token, $id, $message_id, $text, createReplyMarkup($array));
        setLastMessage($mysqli, $data);

    } else if (stristr($data, 'updateWallet.')) {
        $wallet = explode('.',$data);
        $wallet = $wallet_id[1];
        $text = "Кошелек обновляется";
        answerCallbackQuery($token, $callback_query_id, $text);
        sendMessageMain($token,$id,$text);
        get_content("$URL/worker.php?wallet=$wallet");
    } else if (stristr($data, 'deleteWallet.')) {
        $wallet_id = explode('.',$data);
        $wallet_id = $wallet_id[1];
        $mysqli->query("delete from wallets where id = $wallet_id");
        $text = "Кошелек удален";
        answerCallbackQuery($token, $callback_query_id, $text);
        sendMessageMain($token,$id,$text);
    } else if (stristr($data, 'getTransaction.')) {
        $arr = explode('.', $data);
        $tr_id = $arr[1];
        $wallet_id = $arr[2];
        $offset = $arr[3];
        $limit = $arr[4];
        $text = "<b>Информация по транзакции:</b>\n";
        $tr = mysqli_fetch_row($mysqli->query("select * from transactions where id = $tr_id"));
        $type = $tr[2];
        $date = $tr[3];
        $sum = $tr[4];
        $hash_link = $tr[5];
        $wallets_count = $tr[6];
        $text .= "Тип: <b>$type</b>\n" .
            "Дата транзакции: <b>$date</b>\n" .
            "Сумма: <b>$sum</b>\n" .
            "Hash Link: <b>$hash_link</b>\n";
        $array = [
            [
                createCallbackData("Назад", "showWallet.$wallet_id.$offset.$limit")
            ],
            [
                createCallbackData("Выход", "exit")
            ],
        ];
        editMessageText($token, $id, $message_id, $text, createReplyMarkup($array));
        setLastMessage($mysqli, $data);

    } else if ($data == 'addWallet') {
        $text = "Введи адрес нового кошелька(кошельков)";
        answerCallbackQuery($token, $callback_query_id, $text);
        editMessageText($token, $id, $message_id, $text,
            createReplyMarkup([
                [
                    createCallbackData("Выход", "exit")
                ]
            ]));
        setLastMessage($mysqli, $data);
    } else if ($data == 'exit') {
        deleteMessage($token, $id, $message_id);
        sendMessageMain($token, $id, "Привет, $username");
        setLastMessage($mysqli, "");
    }
    exit();
} else
    if ($message == '/start') {
        sendMessageMain($token, $id, "Привет.");
    } else if ($lastMessage == 'addWallet') {
    $wallets = explode("\n", $message);
    $c = 0;
    foreach($wallets as $wallet){
        $c++;
        }
        sendMessageMain($token,$id,"Кошельки добавлены в количистве: $c\n" .
            json_encode($wallets));

        foreach($wallets as $wallet){
            get_content("$URL/worker.php?wallet=$wallet");
        }

    } else

        if (stristr($lastMessage, 'editDescription.')) {
            $arr = explode('.', $lastMessage);
            $wallet_id = $arr[1];
            $wallet = new wallet($mysqli);
            $wallet->createWalletById($wallet_id);
            $wallet->setDescription($message);
            sendMessageMain($token, $id, "Описание установлено");
        } else

            if ($lastMessage == '/json') {
                sendMessage($token, $id, $kk);
                exit();
            } else

                if ($message == '/menu') {

                    sendMessageMain($token, $id, "Привет, $username");


                } else
                    if ($message == '/start') {

                        sendMessageMain($token, $id, "Привет, $username");


                    } else
                        if ($message == '/id' || $message == '/Id' || $message == '/ID') {

                            sendMessageMain($token, $id, "Твой ID: $id");


                        } else {


                            sendMessageMain($token, $id, "Не понимаю о чем ты.");
                        }


setLastMessage($mysqli, $id, $message);
//     file_get_contents($tt."/sendMessage?chat_id=".$id."&text=Все говорят ".$output['message']['text'].", а ты купи слона");