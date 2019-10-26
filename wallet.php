<?php


class wallet
{
    /** @var Mysqli $mysqli */
    private $mysqli;
    public $id, $wallet, $transactions_number, $total_received, $final_balance, $is_updating, $last_update_offset, $transactions;

    public function __wallet($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function createWalletById($id)
    {
        $this->id = $id;
        $row = mysqli_fetch_row($this->mysqli->query("select * from wallets where id = '$id'"));

        $this->wallet = $row[1];
        $this->transactions_number = $row[2];
        $this->total_received = $row[3];
        $this->final_balance = $row[4];
        $this->is_updating = $row[5];
        $this->last_update_offset = $row[6];
        $this->transactions = $this->getAllTransactions();


    }

    public function createWalletByParams($wallet, $transactions_number, $total_received, $final_balance)
    {

        $this->wallet = $wallet;
        $this->transactions_number = $transactions_number;
        $this->total_received = $total_received;
        $this->final_balance = $final_balance;
        $this->is_updating = 0;
        $this->last_update_offset = 0;
        $this->mysqli->query("insert into wallets values (0,'$wallet', '$transactions_number', '$total_received', '$final_balance', 0, 0)");
        $this->id = mysqli_fetch_row(
            $this->mysqli->query("
            select id from wallets order by id desc limit 1
            "))[0];
        $this->transactions = [];

    }

    private function getAllTransactions()
    {
        $trs = [];
        $q = $this->mysqli->query("select * from transactions where wallet_id = '" . $this->id . "' order by date desc");
        while ($row = mysqli_fetch_array($q)) {

            $trs[] = [
                'type' => $row[2],
                'date' => $row[3],
                'sum' => $row[4],
                'hash_link' => $row[5],
                'wallets_count' => $row[6]
            ];

        }
        return $trs;


    }

    public function getTransactions($offset, $limit)
    {
        $trs = [];
        for ($i = 0; $i < $limit && $offset + $i < count($this->transactions); $i++) {
            $trs[] = $this->transactions[$offset + $i];
        };
        return $trs;
    }

    public function addTransaction($type, $date, $sum, $hash_link, $wallets_count){
        $wallet_id = $this->id;
        $this->mysqli->query("insert into transactions values (0,$wallet_id,$type,'$date', '$sum','$hash_link', $wallets_count)");
        $this->transactions[] = [
            'type' => $type,
            'date' => $date,
            'sum' => $sum,
            'hash_link' => $hash_link,
            'wallets_count' => $wallets_count
        ];
        $this->refreshTransactions();

    }
    private function refreshTransactions(){
        $trs = $this->transactions;
        $c = 1;
        while($c){
            $c = 0;
            for($i = 0; $i < count($trs) - 1; $i++){
                if($trs[$i]['date'] > $trs[$i + 1]['date']){
                    $c = 1;
                    $t = $trs[$i];
                    $trs[$i] = $trs[$i + 1];
                    $trs[$i + 1] = $t;
                }
            }
        }
        $this->transactions = $trs;
    }

}