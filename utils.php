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
    $q = $mysqli->query("select id from wallets limit $offset, $limit");
    while ($row = mysqli_fetch_array($q)){
        $wallet_id = $row[0];
        $wallet = new wallet($mysqli);
        $wallet->createWalletById($wallet_id);
        $ret[] = $wallet;
    }
    return $ret;
}


function sendPhoto($token, $id, $file_id, $caption, $reply_markup = null, $parse_mode = 'html')
{
    return get_content("https://api.telegram.org/bot$token/sendPhoto?chat_id=$id&photo=$file_id&caption=" . urlencode($caption) . "&reply_markup=$reply_markup&parse_mode=$parse_mode");
}


function sendMessageMarkdown($token, $id, $msg)
{
    return file_get_contents("https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $id . "&text=" . urlencode($msg) . "&parse_mode=Markdown");
}

function sendMessageMain($token, $id, $msg)
{
    $arr = createMainMenu();

    return sendMessage($token, $id, $msg,
        $arr
    );

}

function sendPhotoMain($token, $id, $file_id, $msg)
{
    $arr = createMainMenu();

    return sendPhoto($token, $id, $file_id, $msg,
        $arr
    );

}

function createMainMenu()
{

    $arr = [
        [createCallbackData('Список кошельков', 'getWalletsList.0.10')],
        [createCallbackData('Добавить кошелек', 'addWallet')],

    ];

    return createReplyMarkup($arr);
}

function sendContact($token, $id, $phone_number, $first_name, $reply_markup = null)
{
    return get_content("https://api.telegram.org/bot$token/sendContact?chat_id=$id&phone_number=$phone_number&first_name=$first_name&reply_markup=$reply_markup");
}

function sendMessage($token, $id, $msg, $reply_markup = null, $disable_web_page_preview = 1, $parse_mode = 'html', $reply_to_message_id = null)
{
    return get_content("https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $id . "&text=" . urlencode($msg) . "&reply_markup=$reply_markup&disable_web_page_preview=$disable_web_page_preview&parse_mode=$parse_mode&reply_to_message_id=$reply_to_message_id");
}

function editMessageText($token, $id, $message_id, $text, $reply_markup = null, $disable_web_page_preview = 1, $pref = 1)
{

    return get_content("https://api.telegram.org/bot$token/editMessageText?chat_id=$id&message_id=$message_id&text=" . urlencode($text) . "&reply_markup=$reply_markup&disable_web_page_preview=$disable_web_page_preview&parse_mode=html");

}

function editMessageMedia($token, $id, $message_id, $media, $reply_markup = null)
{

    return get_content("https://api.telegram.org/bot$token/editMessageMedia?chat_id=$id&message_id=$message_id&media=$media&reply_markup=$reply_markup");

}

function editMessageCaption($token, $id, $message_id, $caption, $reply_markup = null)
{
    return get_content("https://api.telegram.org/bot$token/editMessageMedia?chat_id=$id&message_id=$message_id&caption=$caption&reply_markup=$reply_markup");

}

function createMedia($type, $media, $caption)
{
    $a = array(
        "type" => $type,
        "media" => $media,
        "caption" => $caption
    );
    return json_encode($a);
}

function editMessageReplyMarkup($token, $id, $message_id, $reply_markup)
{
    return get_content("https://api.telegram.org/bot$token/editMessageReplyMarkup?chat_id=$id&message_id=$message_id&reply_markup=$reply_markup");
}

function createReplyMarkup($opz)
{
    $keyboard = array('inline_keyboard' => $opz);
    $keyboard = json_encode($keyboard, true);
    $reply_markup = $keyboard;
    return $reply_markup;
}

function createCallbackData($text, $data)
{
    return array('text' => $text, 'callback_data' => $data);
}

function createURL($text, $url)
{
    return array('text' => $text, 'url' => $url);
}

function createKeyboardButton($text, $request_contact = false, $request_location = false)
{
    $r = [
        'text' => $text,
        'request_contact' => $request_contact,
        'request_location' => $request_location
    ];
    return $r;
}

function createKeyboardMenu($buttons, $resize_keyboard = true, $one_time_keyboard = false, $selective = true)
{

    $keyboard = json_encode($keyboard = ['keyboard' => $buttons,
        'resize_keyboard' => $resize_keyboard,
        'one_time_keyboard' => $one_time_keyboard,
        'selective' => $selective
    ]);
    $reply_markup = $keyboard;
    return $reply_markup;

}

function forwardMessage($token, $id, $from_id, $message_id)
{
    return file_get_contents("https://api.telegram.org/bot" . $token . "/forwardMessage?chat_id=" . $id . "&from_chat_id=" . $from_id . "&message_id=" . $message_id);


}

function deleteMessage($token, $id, $message_id)
{
    get_content("https://api.telegram.org/bot" . $token . "/deleteMessage?chat_id=" . $id . "&message_id=" . $message_id);

}

function answerCallbackQuery($token, $callback_query_id, $text, $show_alert = true)
{
    get_content("https://api.telegram.org/bot$token/answerCallbackQuery?" .
        "callback_query_id=$callback_query_id&" .
        "text=$text&" .
        "show_alert=$show_alert");
}


function setLastMessage($mysqli, $message)
{
    $mysqli->query("update mainVars set lastMessage = '$message'");
}



function getChatInviteLink($token, $chat_id){
    $res = get_content("https://api.telegram.org/bot$token/getchat?chat_id=$chat_id");
    $res = json_decode($res, 1);
    return $res['result']['invite_link'];
}
