<?php
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header("HTTP/1.1 404");
    die;
}

if (!isset($_COOKIE['token'])) {
    die(json_encode(['status' => 1, 'msg' => "Authentication Error. Please login again"]));
}

date_default_timezone_set("Asia/Kolkata");
// Include all Global Variables
require_once "../_GLOBAL.php";

$token = $_COOKIE['token'];
$userData = get_token_data($token);
if ($userData === false) {
    die(json_encode(['status' => 1, 'msg' => "Authentication Error. Please login again"]));
}

$db = mysqli_connect($G_DB_HOST, $G_DB_USERNAME, $G_DB_PASSWORD, $G_DB_DATABASE_NAME);

if ($db === false) {
    die(json_encode(['status' => 3, 'msg' => "Internal Server Error 1"]));
}

$id = $userData['id'];
$getQuery = "SELECT `id`,`type`,`day`,`month`,`title`,`description`,`hour`,`apm`,`wr`,`created_at`,`active` FROM `reminders` WHERE `user_id` = '$id';";

$result = mysqli_query($db, $getQuery);
if ($result === false) {
    die(json_encode(['status' => 3, 'msg' => "Internal Server Error 2"]));
}

$numOfResults = mysqli_num_rows($result);
if ($numOfResults == 0) {
    die(json_encode(['status' => 2, 'msg' => "You do not have any reminder"]));
}
mysqli_close($db);

$data = mysqli_fetch_all($result, MYSQLI_ASSOC);
for ($i = 0; $i < $numOfResults; $i++) {
    $timestamp = (int)$data[$i]['created_at'];
    $data[$i]['created_at'] = date("D d M Y h:i a", $timestamp);
}

$t = time();
$local = date("l d F Y  h:i:s a");


die(json_encode(['status' => 0, 'msg' => "Successfully got data", 'time' => $local, 'data' => $data, 'numOfResults' => $numOfResults]));


// Functions
