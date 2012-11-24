<?php 

    include("phpgraphlib.php");

    $yday = date("z");
    $year = date("Y");
    $month = date("F");
    $mday = date("d");

    $retArgs = array();


    if(!$fileLine = file('http://ad7zj-kingman.no-ip.org:82/cgi-bin/xanquery.sh')) {
        die("Failed to contact remote inverter");
    }

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

    $kwh_today = trim($kwh_today);
    $kwh_life = trim($kwh_life);

    preg_match('/^C:(.+?)\sF:(.+?)\s/', $fileLine[4], $matches);
    $tempC = $matches[1];
    $tempF = $matches[2];
    $TimeOnline = $fileLine[5];

    $freq = round($freq, 2);
    $kwh_today = round($kwh_today, 2);
    // define background image
    $backGndImg = '/var/www/pv/LCD.jpg';
    // directory to save the images to without a trailing slash
    $savePath = '/var/www/pv';

    // Load the background image for each "display"
    $dispvin = imagecreatefromjpeg($backGndImg);
    $dispiin = imagecreatefromjpeg($backGndImg);
    $disppin = imagecreatefromjpeg($backGndImg);
    $dispvout = imagecreatefromjpeg($backGndImg);
    $dispiout = imagecreatefromjpeg($backGndImg);
    $disppout = imagecreatefromjpeg($backGndImg);
    $dispfreq = imagecreatefromjpeg($backGndImg);
    $dispkwh_today = imagecreatefromjpeg($backGndImg);
    $dispkwh_life = imagecreatefromjpeg($backGndImg);
    $dispTempF = imagecreatefromjpeg($backGndImg);

    // Declare and Define colors
    $white = imagecolorallocate($dispvin, 255, 255, 255);
    $grey = imagecolorallocate($dispiin, 128, 128, 128);
    $black = imagecolorallocate($dispiin, 35, 35, 35);

    // Font to use
    //$font = '/usr/share/fonts/truetype/LCD/Digire__.ttf';
    //$font = '/usr/share/fonts/truetype/LCD/Digireu_.ttf';
    //$font = '/usr/share/fonts/truetype/LCD/Digir___.ttf';
    //$font = '/usr/share/fonts/truetype/LCD/DIGIRT__.TTF';
    $font = '/usr/share/fonts/truetype/LCD/DIGIRU__.TTF';

    // We'll start with the input voltage
    $text = "$vin V";
    // Add the text to the image
    imagettftext($dispvin, 20, 0, 20, 42, $black, $font, $text);
    // Output the image to file
    imagepng($dispvin, "$savePath/vin.png");

    // Now iin
    $text = "$iin A";
    // Add the text to the image
    imagettftext($dispiin, 20, 0, 20, 42, $black, $font, $text);
    // Output the image to file
    imagepng($dispiin, "$savePath/iin.png");

    // Now pin
    $text = "$pin W";
    // Add the text to the image
    imagettftext($disppin, 20, 0, 20, 42, $black, $font, $text);
    // Output the image to file
    imagepng($disppin, "$savePath/pin.png");

    // Now vout
    $text = "$vout V";
    // Add the text to the image
    imagettftext($dispvout, 20, 0, 20, 42, $black, $font, $text);
    // Output the image to file
    imagepng($dispvout, "$savePath/vout.png");

    // Now iout
    $text = "$iout A";
    // Add the text to the image
    imagettftext($dispiout, 20, 0, 20, 42, $black, $font, $text);
    // Output the image to file
    imagepng($dispiout, "$savePath/iout.png");

    // Now pout
    $text = "$pout W";
    // Add the text to the image
    imagettftext($disppout, 20, 0, 20, 42, $black, $font, $text);
    // Output the image to file
    imagepng($disppout, "$savePath/pout.png");

    // Now frequency
    $text = "$freq Hz";
    // Add the text to the image
    imagettftext($dispfreq, 20, 0, 20, 42, $black, $font, $text);
    // Output the image to file
    imagepng($dispfreq, "$savePath/freq.png");

    // Now kwh_today
    $text = "$kwh_today Kh";
    // Add the text to the image
    imagettftext($dispkwh_today, 20, 0, 15, 42, $black, $font, $text);
    // Output the image to file
    imagepng($dispkwh_today, "$savePath/kwh_today.png");
 
    // Now kwh_life
    $text = "$kwh_life Kh";
    // Add the text to the image
    imagettftext($dispkwh_life, 20, 0, 15, 42, $black, $font, $text);
    // Output the image to file
    imagepng($dispkwh_life, "$savePath/kwh_life.png");

    // Now Temp
    $text = "$tempF F";
    // Add the text to the image
    imagettftext($dispTempF, 20, 0, 15, 42, $black, $font, $text);
    // Output the image to file
    imagepng($dispTempF, "$savePath/TempF.png");


    imagedestroy($dispvin);
    imagedestroy($dispiin);
    imagedestroy($disppin);
    imagedestroy($dispvout);
    imagedestroy($dispiout);
    imagedestroy($disppout);
    imagedestroy($dispfreq);
    imagedestroy($dispkwh_today);
    imagedestroy($dispkwh_life);
    imagedestroy($dispTempF);

//    print("<center>Current PV Input Voltage<br><img src=\"test.png\"></center>"); 
?>

<html>
<head>
<style type="text/css">

table,td,th {
    border:1px solid green;
}
table {
    width:100%;
}

td {
    text-align:center;
}

th {
    padding:15px;
}
</style>

</head>
<body>
<table>
<tr>
<th>PV Input Voltage<br><img src="vin.png"></th>
<th>PV Input Current<br><img src="iin.png"></th>
<th>PV Input Power<br><img src="pin.png"></th>
<td>Inverter Output Voltage<br><img src="vout.png"></td>
<td>Inverter Output Current<br><img src="iout.png"></td>
</tr>
<tr>
<td>Inverter Output Power<br><img src="pout.png"></td>
<td>Line frequency<br><img src="freq.png"></td>
<td>Kilowatt Hours Today<br><img src="kwh_today.png"></td>
<td>KWh For System Lifetime<br><img src="kwh_life.png"></td>
<td>Inverter Heatsink Temp<br><img src="TempF.png"></td>
</tr>
</table>
</body>
</html>
