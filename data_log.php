<?php
//    include error_reporting(0);
    include("mysql_conn.php"); 

    if(!$fileLine = file('http://ad7zj-kingman.no-ip.org:82/cgi-bin/xanquery.sh')) {
        die("Failed to contact remote inverter");
    }

    $connection = new connect;
    $connection->database = "PVoutput";
    $connection->connect();

    $yday = date("z");
    $year = date("Y");
    $month = date("F");
    $monthNum = date("n");
    $mday = date("d");
    $hour = date("G");
    $min = date("i");

    preg_match('/^V:(.+?)\sI:(.+?)\sP:(.+?)\s/', $fileLine[0], $matches);
    $vin = $matches[1];
    $iin = $matches[2];
    $pin = $matches[3];

    preg_match('/^V:(.+?)\sI:(.+?)\sP:(.+?)\sF:(.+?)\s/', $fileLine[1], $matches);
    $vout = $matches[1];
    $iout = $matches[2];
    $pout = $matches[3];
    $freq = $matches[4];

    $kwh_today = $fileLine[2];
    $kwh_life = $fileLine[3];

    preg_match('/^C:(.+?)\sF:(.+?)\s/', $fileLine[4], $matches);
    $tempC = $matches[1];
    $tempF = $matches[2];
    $TimeOnline = $fileLine[5];

    // Insert the daily information by updating the table if it exists
    $sql="SELECT * FROM Xantrex_Daily WHERE Year='$year' AND YearDay='$yday'";
    $result = mysql_query($sql) or die('Query failed: ' . mysql_error());

    if(mysql_affected_rows() < 1) {
        $sql="INSERT INTO Xantrex_Daily VALUES('', '$year', '$monthNum', '$mday', '$yday', '$kwh_today', '$TimeOnline')";
        $result = mysql_query($sql) or die('Query failed: ' . mysql_error());
    }
    else {
	$sql="UPDATE Xantrex_Daily SET KWHtoday='$kwh_today', TimeOnline='$TimeOnline' WHERE Year='$year' AND YearDay='$yday'";
	$result = mysql_query($sql) or die('Query failed: ' . mysql_error());
    }

    // Insert hourly information
    $sql="INSERT INTO Xantrex_Hourly VALUES('', '$year', '$monthNum', '$mday', '$yday', '$hour', '$min', '$pin', '$pout', '$vin', '$vout', '$iin', '$iout', '$tempF')";
    $result = mysql_query($sql) or die('Query failed: ' . mysql_error());

    mysql_close();

?>

