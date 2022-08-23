<?php
date_default_timezone_set("Asia/Kolkata");

require_once "../../_GLOBAL.php";
$curl = curl_init("http://" . $G_SERVER_DOMAIN . "/cron/steps/step1.php");

// Log into a file
$file = fopen("./data/init1.txt", "a+");
if ($file == false) die;
$local = date("l d F Y  h:i:s a", time());
fwrite($file, "Init 1 ran at: $local\n");
fclose($file);

// execute request
curl_exec($curl);
curl_close($curl);

?>