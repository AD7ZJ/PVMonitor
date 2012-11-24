<?php 
    include("phpgraphlib.php");
    include("mysql_conn.php"); 
    $time_start = microtime(true);

    $mday=$_GET[day];
    $monthNum=$_GET[month];
    $year=$_GET[year];

    $connection = new connect;
    $connection->database = "PVOutputNew";
    $connection->connect();


    // If not passed in the URL, use data from today
    if(!isset($year)) {
	$year = date("Y");
    }
    if(!isset($monthNum)) {
	$monthNum = date("n");
    }
    if(!isset($mday)) {
	$mday = date("d");
    }
    
    $KWH = array();
    $IinArray = array();
    $PinArray = array();
    $TempFArray = array();    

    $beginLim = 30 - $mday;
    if($monthNum == 1) {
	$monthLessOne = 12;
	$yearLessOne = $year - 1;
    }
    else {
	$monthLessOne = $monthNum - 1;
	$yearLessOne = $year;
    }

    $roll = 30;
    $dayroll = 28;

    $sql="SELECT * FROM Xantrex_Daily WHERE (Month ='$monthNum' AND Year='$year') OR (Month = '$monthLessOne' AND Year = '$yearLessOne') ORDER BY Year, YearDay";
    $result = mysql_query($sql) or die('Query failed: ' . mysql_error());

    $num = mysql_num_rows($result);
    if($num < 30) {
	$pointer = 0;
    }
    else {
	$pointer = $num - $roll;
    }
    if($result) {
	if(!mysql_data_seek($result, $pointer)) {
	    print("Cannot seek to row location");
	}
	while($row = mysql_fetch_assoc($result)) {
	    $yearShort = $row["Year"];
	    $yearShort = $yearShort - 2000;
	    $mon = $row["Month"];
	    $day = $row["MonthDay"];
	    $KWHtoday = $row["KWHtoday"];
	    $KWH["$mon/$day/$yearShort"] = round($KWHtoday,1);
	}
    }

    if($num) {
    	$graph = new PHPGraphLib(800,300, "graphKWHtoday.png");
    	$graph->addData($KWH);
    	$graph->setTitle("Kilowatt Hours For The Past 30 Days From $mon/$day/$yearShort");
    	$graph->setBars(false);
    	$graph->setLine(true);
    	$graph->setDataPoints(true);
    	$graph->setDataPointColor("blue");
    	$graph->setDataValues(true);
    	$graph->setDataValueColor("blue");
	$graph->setupXAxis(20);
    	$graph->createGraph();
    }
    else {
	print("No data for today yet! <br>");
    }

   // print("$num rows affected");
    if($result) {
        if(!mysql_data_seek($result, $pointer)) {
            print("Cannot seek to row location");
        }
        while($row = mysql_fetch_assoc($result)) {
            $yearShort = $row["Year"];
            $yearShort = $yearShort - 2000;
            $mon = $row["Month"];
            $day = $row["MonthDay"];
            $timeOnline = $row["TimeOnline"];
            $time["$mon/$day/$yearShort"] = $timeOnline;
        }
    }

    if($num) {
        $graph = new PHPGraphLib(800,300, "graphTimeToday.png");
        $graph->addData($time);
        $graph->setTitle("Time Online (in seconds) for the Past 30 Days From $mon/$day/$yearShort");
        $graph->setBars(false);
        $graph->setLine(true);
        $graph->setDataPoints(true);
        $graph->setDataPointColor("blue");
        $graph->setDataValues(false);
        $graph->setDataValueColor("blue");
        $graph->setupXAxis(20);
        $graph->createGraph();
    }
    else {
        print("No data for today yet! <br>");
    }

    // Now generate hourly graphs

    $sql="SELECT * FROM Xantrex_Hourly WHERE Month = '$monthNum' AND MonthDay ='$mday' AND Year='$year' ORDER BY YearDay, Hour, Minute";
    $result = mysql_query($sql) or die('Query failed: ' . mysql_error());

    hourly_graph($result, $dayroll, false, "Current In & Out for $monthNum/$mday/$yearShort", "Iin", "current_in.png");
    hourly_graph($result, $dayroll, false, "Voltage In & Out for $monthNum/$mday/$yearShort", "Vin", "voltage_in.png");
    hourly_graph($result, $dayroll, false, "Power In & Out for $monthNum/$mday/$yearShort", "Pin", "power_in.png"); 
    hourly_graph($result, $dayroll, false, "Inverter Heatsink Temp for $monthNum/$mday/$yearShort", "temp", "sinktemp.png");


    $time_end = microtime(true);
    $time = round($time_end - $time_start,4);
    print("$num rows affected.  Page generated in {$time}s");

function hourly_graph($result, $dayroll, $datavals, $title, $type, $image) {
    $num = mysql_num_rows($result);
    if($result) {
        if(!mysql_data_seek($result, 0)) {
            print("Cannot seek to row location");
        } 
        $rowCount = 0;
        while($row = mysql_fetch_assoc($result)) {
            if($rowCount++ % 5 == 0) 
                $showXValue = true;
            else
                $showXValue = false;

            $day = $row["MonthDay"];
            $hour = $row["Hour"];
            $min = $row["Minute"];
            $current_in = $row["Iin"];
            $voltage_in = $row["Vin"];
            $power_in = $row["Pin"];
            $current_out = $row["Iout"];
            $voltage_out = $row["Vout"];
            $power_out = $row["Pout"];
            if($row["Temp"] > 50) {
                $sinktemp = $row["Temp"];
            }
            else {
                $sinktemp = 60;
            }
            if($showXValue) {
                $IinArray["$hour:$min"] = $current_in;
                $VinArray["$hour:$min"] = $voltage_in;
                $PinArray["$hour:$min"] = $power_in;
                $IoutArray["$hour:$min"] = $current_out;
                $VoutArray["$hour:$min"] = $voltage_out;
                $PoutArray["$hour:$min"] = $power_out;
                $TempFArray["$hour:$min"] = $sinktemp;
            }
            else {
                $IinArray["$hour:$min          "] = $current_in;
                $VinArray["$hour:$min          "] = $voltage_in;
                $PinArray["$hour:$min          "] = $power_in;
                $IoutArray["$hour:$min          "] = $current_out;
                $VoutArray["$hour:$min          "] = $voltage_out;
                $PoutArray["$hour:$min          "] = $power_out;
                $TempFArray["$hour:$min          "] = $sinktemp;
            }
        }
    }

    if($num) {
        $graph = new PHPGraphLib(800,300, $image);
	switch($type) {
	    case "Iin":
	        $graph->addData($IinArray);
	        $graph->addData($IoutArray);
		$graph->setLegendTitle("Current In", "Current Out");
		break;
	    case "Vin":
		$graph->addData($VinArray);
		$graph->addData($VoutArray);
		$graph->setLegendTitle("Voltage In", "Voltage Out");
                break;
	    case "Pin":
		$graph->addData($PinArray);
		$graph->addData($PoutArray);
		$graph->setLegendTitle("Power In", "Power Out");
                break;
	    case "Iout":
		$graph->addData($IoutArray);
                break;
	    case "Vout":
		$graph->addData($VoutArray);
                break;
	    case "Pout":
		$graph->addData($PoutArray);
                break;
	    case "temp":
		$graph->addData($TempFArray);
		$graph->setLegendTitle("Degrees F");
                break;
	}
        $graph->setTitle($title);
        $graph->setBars(false);
        $graph->setLine(true);
        $graph->setDataPoints(false);
        $graph->setDataPointColor("blue");
	$graph->setLineColor("blue", "red");
        $graph->setDataValues($datavals);
        $graph->setDataValueColor("blue");
	$graph->setLegend(true);
	$graph->setLegendColor("205,183,158");
        $graph->createGraph();
    }
    else {
        print("No data for today yet! <br>");
    }


}

    mysql_close();
?>

<html>
<body>
<center>
<img src="graphKWHtoday.png" alt="Kilowatt Hours Today" />
<br>
<img src="graphTimeToday.png" alt="Time Online Today" />
<br>
<img src="voltage_in.png" alt="Time Online Today" />
<br>
<img src="current_in.png" alt="Current Input" />
<br>
<img src="power_in.png" alt="Current Input" />
<br>
<img src="sinktemp.png" alt="Heatsink Temp" />
</center>
</body>
</html>
