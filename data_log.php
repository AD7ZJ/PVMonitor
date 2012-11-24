<?php
//    include error_reporting(0);
    include("mysql_conn.php"); 

    if(!$fileString = file_get_contents('http://ad7zj-kingman.no-ip.org:82/xantrex/historicalData.txt')) {
        die("Failed to contact remote inverter");
    }

    $connection = new connect;
    $connection->database = "PVOutputNew";
    $connection->connect();

    $yday = date("z");
    $year = date("Y");
    $month = date("F");
    $monthNum = date("n");
    $mday = date("d");
    $hour = date("G");
    $min = date("i");

    // get the last entry in the database
    $sql="SELECT * FROM Xantrex_Hourly ORDER BY Timestamp DESC LIMIT 1;";
    $result = mysql_query($sql) or die('Query failed: ' . mysql_error());
    $row = mysql_fetch_assoc($result);
    $lastEntryTime = $row["Timestamp"];

    preg_match_all('/<start>\s+(.*?)\s+<end>/si', $fileString, $matches);

    foreach($matches[1] as $stringy) {
        $entryLines = explode("\n", $stringy);
        $timestamp = $entryLines[0];
        // Is this a new entry?
        if($timestamp > $lastEntryTime) {
            preg_match('/^V:(.+?)\sI:(.+?)\sP:(.+?)\s+(.*)$/', $entryLines[1], $matches);
            $vin = $matches[1];
            $iin = $matches[2];
            $pin = $matches[3];

            preg_match('/^V:(.+?)\sI:(.+?)\sP:(.+?)\sF:(.+?)\s*$/', $entryLines[2], $matches);
            $vout = $matches[1];
            $iout = $matches[2];
            $pout = $matches[3];
            $freq = $matches[4];

            $kwh_today = $entryLines[3];
            $kwh_life = $entryLines[4];

            preg_match('/^C:(.+?)\sF:(.+?)$/', $entryLines[5], $matches);
            $tempC = $matches[1];
            $tempF = $matches[2];
            $TimeOnline = $entryLines[6];


            print("timestamp: $timestamp Vin: $vin Iin: $iin Pin: $pin Temp: $tempF\n");
            print("Vout: $vout Iout: $iout Pout: $pout Freq: $freq KWHToday: $kwh_today KWHLife: $kwh_life\n");


            // Insert the daily information by updating the table if it exists
            $yday = date("z", $timestamp);
            $year = date("Y", $timestamp);
            $month = date("F", $timestamp);
            $monthNum = date("n", $timestamp);
            $mday = date("d", $timestamp);
            $hour = date("G", $timestamp);
            $min = date("i", $timestamp);

            $sql="SELECT * FROM Xantrex_Daily WHERE Year='$year' AND YearDay='$yday'";
            $result = mysql_query($sql) or die('Query failed: ' . mysql_error());

            if(mysql_affected_rows() < 1) {
                $sql="INSERT INTO Xantrex_Daily VALUES('', '$year', '$monthNum', '$mday', '$yday', '$timestamp', '$kwh_today', '$TimeOnline')";
                $result = mysql_query($sql) or die('Query failed: ' . mysql_error());
            }
            else {
                $sql="UPDATE Xantrex_Daily SET KWHtoday='$kwh_today', TimeOnline='$TimeOnline' WHERE Year='$year' AND YearDay='$yday'";
                $result = mysql_query($sql) or die('Query failed: ' . mysql_error());
            }

            // Insert hourly information
            $sql="INSERT INTO Xantrex_Hourly VALUES('', '$year', '$monthNum', '$mday', '$yday', '$timestamp', '$hour', '$min', '$pin', '$pout', '$vin', '$vout', '$iin', '$iout', '$tempF')";
            $result = mysql_query($sql) or die('Query failed: ' . mysql_error());
        }
    }

    mysql_close();

?>

