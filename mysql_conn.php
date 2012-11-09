<?php

class connect {
    var $user = "xantrex";
    var $pass = "xantrex";
    public $database;

    public function connect() {
        if (!mysql_connect('localhost',$this->user,$this->pass)) {
            echo "MySQL Connection was unsuccessful";
        }
        mysql_select_db($this->database);
    }
}
?>
