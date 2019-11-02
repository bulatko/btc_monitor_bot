<?php

require 'bd.php';

$q = $mysqli->query("select wallet from wallets");

while ($row = mysqli_fetch_array($q)){
    $wallet = $row[0];

}
