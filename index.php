<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<style type="text/css">

.center {
  margin-left: auto;
  margin-right: auto;
  text-align: center;
  padding: 6px;
  border: 2px solid rgb(0,0,0);
  border-radius: 20px;
  -moz-border-radius: 20px;
}

div.wrapper {
  margin-left: 4%;
  margin-right: 4%;
}

img {
  padding: 20px;
}

body {
  background-image: url(Clouds.jpg);
  text-align: center;
}

</style>
<title>Solar Monitor Homepage</title>
</head>

<body>
<div class="wrapper">
<h1>Kingman AZ Solar Output Monitor</h1><br>
<?php
    include("phpgraphlib.php");
    include("mysql_conn.php");
    include("execution_time.php");
    $startTime = slog_time();
    $time_start = microtime(true);

    $connection = new connect;
    $connection->database = "PVOutputNew";
    $connection->connect();

    $yday = date("z");
    $year = date("Y");
    $month = date("F");
    $monthNum = date("n");
    $mday = date("d");

    $KWH = array();
    $KWHtotal = array();
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

    $ydayminone = $yday - 1;
    $roll = 30;
    $dayroll = 250;
    $queryCount = 0;

    $sql="SELECT * FROM Xantrex_Daily WHERE (Month ='$monthNum' AND Year='$year') OR (Month = '$monthLessOne' AND Year = '$yearLessOne') ORDER BY Year, YearDay";
    $result = mysql_query($sql) or die('Query failed: ' . mysql_error());
    $queryCount++;

    $num = mysql_num_rows($result);
    if($result) {
	if($num > $roll) {
            if(!mysql_data_seek($result, ($num - $roll))) {
                print("Cannot seek to row location");
            }
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
        $graph->setTitle("Kilowatt Hours For The Past 30 Days");
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

    /************************  Generate a past history graph ****************************/
    $roll = 400;
    $sql="SELECT * FROM Xantrex_Daily ORDER BY Year, YearDay";
    $result = mysql_query($sql) or die('Query failed: ' . mysql_error());
    $queryCount++;

    $num = mysql_num_rows($result);
    if($result) {
	if($num > $roll) {
            if(!mysql_data_seek($result, ($num - $roll))) {
                print("Cannot seek to row location");
            }
	}
	$row = mysql_fetch_assoc($result);
        while($row) {
	    $KWHsum = 0;
	    $numDays = 0;
	    $prevMonth = $row["Month"];
	    $monthName = date( 'M', mktime(0, 0, 0, $prevMonth ));
	    while($prevMonth == $row["Month"]) {
	 	$numDays++;
            	$yearShort = $row["Year"];
            	$yearShort = $yearShort - 2000;
            	$mon = $row["Month"];
            	$day = $row["MonthDay"];
            	$KWHsum += $row["KWHtoday"];
		$row = mysql_fetch_assoc($result);
	    }
            $KWHtotal["$monthName $yearShort"] = round($KWHsum / $numDays,3);
        }
    }

    if($num) {
        $graph = new PHPGraphLib(800,300, "graphKWHtotal.png");
        $graph->addData($KWHtotal);
        $graph->setTitle("Monthly Averages for the Past 400 Days");
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


    // Now generate hourly graphs
    $sql="SELECT * FROM Xantrex_Hourly WHERE YearDay >='$ydayminone' AND Year='$year' ORDER BY YearDay, Hour, Minute";
    $result = mysql_query($sql) or die('Query failed: ' . mysql_error());
    $queryCount++;

    hourly_graph($result, $dayroll, false, "Power In & Out", "Pin", "power_in.png");


function hourly_graph($result, $dayroll, $datavals, $title, $type, $image) {
    $num = mysql_num_rows($result);
    if($result) {
        if($num > $dayroll) {
            if(!mysql_data_seek($result, ($num - $dayroll))) {
                print("Cannot seek to row location");
            }
        }

        $rowCount = 0;
        while($row = mysql_fetch_assoc($result)) {
            $rowCount++;
            if($rowCount % 5 == 0)
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
            $sinktemp = $row["Temp"];

            $min = str_pad($min, 2, "0", STR_PAD_LEFT);
            if($showXValue) {
                $IinArray["$day     $hour:$min"] = $current_in;
                $VinArray["$day     $hour:$min"] = $voltage_in;
                $PinArray["$day     $hour:$min"] = $power_in;
                $IoutArray["$day     $hour:$min"] = $current_out;
                $VoutArray["$day     $hour:$min"] = $voltage_out;
                $PoutArray["$day     $hour:$min"] = $power_out;
                $TempFArray["$day     $hour:$min"] = $sinktemp;
            }
            else {
                // since phpgraphlib doesn't have a way to filter the X values, to plot a lot of points and
                // still have the X values readable, we push the unwanted values off the canvas with spaces
                $IinArray["$day:$hour:$min          "] = $current_in;
                $VinArray["$day:$hour:$min          "] = $voltage_in;
                $PinArray["$day:$hour:$min          "] = $power_in;
                $IoutArray["$day:$hour:$min          "] = $current_out;
                $VoutArray["$day:$hour:$min          "] = $voltage_out;
                $PoutArray["$day:$hour:$min          "] = $power_out;
                $TempFArray["$day:$hour:$min          "] = $sinktemp;
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
        $graph->setXValues(true);
        $graph->createGraph();
    }
    else {
        print("No data for today yet! <br>");
    }


}

?>

<div class="center">
    <img src="graphKWHtoday.png" width="80%"><br>
    KWH For Today&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp KWH Lifetime<br>
    <img src="kwh_today.png"> <img src="kwh_life.png"><br>
</div><br>
<div class="center">
    <img src="power_in.png" width="80%"><br>
    DC Power In &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp AC Power Out<br>
    <img src="pin.png"> <img src="pout.png"><br>
</div><br>
<div class="center">
<img src="graphKWHtotal.png" width="80%"><br>
</div><br>
<div class="center">    
<h2>Historical Data</h2><br>
<?php
    print "<table><tr>";
    $sql = "SELECT * FROM Xantrex_Daily ORDER BY Year, YearDay";
    $result = mysql_query($sql);
    $queryCount++;

    $prevYear  = 0;
    $prevMonth = 0;
    $calsPrinted = 0;
    $row = mysql_fetch_assoc($result);
    while($row) {
	$prevMonth = $row["Month"];
	$prevYear  = $row["Year"];
	print "<td>&nbsp&nbsp&nbsp</td><td>";
	while($prevMonth == $row["Month"]) {
	    // There's an entry for this day, make a link
	    $days[$row["MonthDay"]] = array("history.php?day={$row["MonthDay"]}&month={$row["Month"]}&year={$row["Year"]}",'linked-day');   	
	    $row = mysql_fetch_assoc($result);
	    $rowPointer++;
	}	     

        echo generate_calendar($prevYear, ($prevMonth), $days);
	unset($days);
	$calsPrinted++;
        print "</td>";
	if(($calsPrinted % 4) == 0) {
	   print "</tr><tr>";
	}
    }

    print("</tr></table>");

function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array()){
    $first_of_month = gmmktime(0,0,0,$month,1,$year);

    $day_names = array(); #generate all the day names according to the current locale
            for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) #January 4, 1970 was a Sunday
        $day_names[$n] = ucfirst(gmstrftime('%A',$t)); #%A means full textual day name

    list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
    $weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day
    $title   = htmlentities(ucfirst($month_name)).'&nbsp;'.$year;  #note that some locales don't capitalize month and day names

    #Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03
    @list($p, $pl) = each($pn); @list($n, $nl) = each($pn); #previous and next links, if applicable
    if($p) $p = '<span class="calendar-prev">'.($pl ? '<a href="'.htmlspecialchars($pl).'">'.$p.'</a>' : $p).'</span>&nbsp;';
    if($n) $n = '&nbsp;<span class="calendar-next">'.($nl ? '<a href="'.htmlspecialchars($nl).'">'.$n.'</a>' : $n).'</span>';
    $calendar = '<table class="calendar">'."\n".
        '<caption class="calendar-month">'.$p.($month_href ? '<a href="'.htmlspecialchars($month_href).'">'.$title.'</a>' : $title).$n."</caption>\n<tr>";

    if($day_name_length){ #if the day names should be shown ($day_name_length > 0)
        #if day_name_length is >3, the full name of the day will be printed
        foreach($day_names as $d)
            $calendar .= '<th abbr="'.htmlentities($d).'">'.htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d).'</th>';
        $calendar .= "</tr>\n<tr>";
    }

    if($weekday > 0) $calendar .= '<td colspan="'.$weekday.'">&nbsp;</td>'; #initial 'empty' days
    for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
        if($weekday == 7){
            $weekday   = 0; #start a new week
            $calendar .= "</tr>\n<tr>";
        }
        if(isset($days[$day]) and is_array($days[$day])){
            @list($link, $classes, $content) = $days[$day];
            if(is_null($content))  $content  = $day;
            $calendar .= '<td'.($classes ? ' class="'.htmlspecialchars($classes).'">' : '>').
                ($link ? '<a href="'.htmlspecialchars($link).'">'.$content.'</a>' : $content).'</td>';
        }
        else $calendar .= "<td>$day</td>";
    }
    if($weekday != 7) $calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>'; #remaining "empty" days

    return $calendar."</tr>\n</table>\n";
}
$time_end = microtime(true);
mysql_close();
?>
</div><br>
<div class="center">
    <h2>Live Image</h2><br>
    <?php
	if($_SERVER['REMOTE_ADDR'] == gethostbyname('ad7zj-kingman.no-ip.org')) {
	    print("<img src=\"http://192.168.254.6/cams/motion/Backyard/Live_Snapshot_Backyard.jpg\"><br>");
	}
	else {
	    print("<img src=\"http://blue.kingmanmodelers.com/cams/motion/Backyard/Live_Snapshot_Backyard.jpg\"><br>");
	}
?>
</div><br>
<?php 
    $time = round($time_end - $time_start,4);
    print("Page generated with $queryCount queries in {$time}s");
?>
</div> <!-- End Wrapper Div -->
</body>
</html>
