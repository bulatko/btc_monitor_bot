<?php

require 'bd.php';
require 'utils.php';

$q = $mysqli->query("select wallet from wallets");

while ($row = mysqli_fetch_array($q)){
    $wallet = $row[0];
    get_content("$URL/worker.php?wallet=$wallet");
}
