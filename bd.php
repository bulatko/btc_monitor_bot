<?php
require 'consts.php';
set_time_limit(3000);
ini_set('display_errors', 'Off');
header("Content-Type: text/html; charset=utf-8");
mb_internal_encoding("UTF-8");
/* Переменные для соединения с базой данных */
$hostname = "localhost";
$username = $DB_USERNAME;
$password = $DB_PASSWORD;
$dbName = $DB_NAME;
$token = $BOT_TOKEN;
$mysqli = new mysqli($hostname, $username, $password, $dbName);
mysqli_set_charset($mysqli, 'utf8mb4');
/* проверяем соединение */
if (mysqli_connect_errno()) {
    printf("Ошибка соединения: %s\n", mysqli_connect_error());
    exit();
}
$mysqli->query('SET NAMES utf8mb4_unicode_ci.');
$mysqli->query("SET CHARACTER SET 'utf8mb4_unicode_ci.'");

?>