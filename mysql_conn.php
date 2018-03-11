<?php

class connect {
    var $user = "xantrex";
    var $pass = "xantrex";
    public $database;

    public function connect() {
        $db = new mysqli('localhost',$this->user,$this->pass,$this->database);
        if ($db->connect_errno) {
            echo "MySQL Connection was unsuccessful";
        }
        return $db;
    }
}
?>
