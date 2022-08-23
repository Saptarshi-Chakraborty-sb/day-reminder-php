<?php

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header("HTTP/1.1 404");
    die;
}

if (!isset($_COOKIE['token'])) {
    die(json_encode(['status' => 1, 'msg' => "Authentication Error. Please login again"]));
}

if (
    (!isset($_POST['type'])) || ($_POST['type'] === "") ||
    (!isset($_POST['isCustomType'])) || ($_POST['isCustomType'] === "") ||
    (!isset($_POST['title'])) || ($_POST['title'] === "") ||
    (!isset($_POST['description'])) ||
    (!isset($_POST['hour'])) || ($_POST['hour'] === "") ||
    (!isset($_POST['portionOfDay'])) || ($_POST['portionOfDay'] === "") ||
    (!isset($_POST['day'])) || ($_POST['day'] === "") ||
    (!isset($_POST['month'])) || ($_POST['month'] === "") ||
    (!isset($_POST['willRepeat'])) || ($_POST['willRepeat'] === "")
) {
    header("HTTP/1.1 400");
    die(json_encode(['status' => 2, 'msg' => "Invalid Request"]));
}

// Include all Global Variables
require_once "../_GLOBAL.php";

$authToken = $_COOKIE['token'];
$tokenData = get_token_data($authToken);
if ($tokenData === false) {
    die(json_encode(['status' => 1, 'msg' => 'Authentication Error. Please login again']));
}
$id = $tokenData['id'];
// $id = 1;

// Assign all POST variables
$type = trim($_POST['type']);
$isCustomType = trim($_POST['isCustomType']);
$title = trim($_POST['title']);
$description = trim($_POST['description']);
$hour = trim($_POST['hour']);
$amORpm = trim($_POST['portionOfDay']);
$day = trim($_POST['day']);
$month = trim($_POST['month']);
$willRepeat = trim($_POST['willRepeat']);

// All checkings
if (check_type($type, $isCustomType) !== true) {
    // die(json_encode(['status' => 4, 'msg' => "Incorrect Value. Check the value of type"]));
    die(json_encode(['status' => 4, 'msg' => "Incorrect Value. Check the value of type", 'a' => "" . var_dump($_POST) . ""]));
} else if (check_title($title) !== true) {
    die(json_encode(['status' => 4, 'msg' => "Incorrect Value. Check the value of title"]));
} else if (check_description($description) !== true) {
    die(json_encode(['status' => 4, 'msg' => "Incorrect Value. Check the value of description"]));
} else if (check_time($hour, $amORpm) !== true) {
    die(json_encode(['status' => 4, 'msg' => "Incorrect Value. Check the value of time"]));
} else if (check_date($day, $month) !== true) {
    die(json_encode(['status' => 4, 'msg' => "Incorrect Value. Check the value of date"]));
}

// Connect Database
$db = mysqli_connect($G_DB_HOST, $G_DB_USERNAME, $G_DB_PASSWORD, $G_DB_DATABASE_NAME);
if ($db === false) {
    die(json_encode(['status' => 3, 'msg' => 'Internal Server Error 1']));
}

// Prepare values to insert
$timestamp = strval(time());
$ip = $_SERVER['REMOTE_ADDR'];
$isRepeatOn = ($willRepeat == 'true') ? 'true' : 'false';
$type[0] = strtolower($type[0]);


$insertQuery = "INSERT INTO `reminders` (`user_id`, `type`, `month`, `day`, `title`, `description`, `hour`,`apm`, `wr`, `done`,`active`, `ip`, `created_at`) VALUES ('$id', '$type', '$month', '$day', '$title', '$description','$hour', '$amORpm', '$isRepeatOn', '1', '0', '$ip', '$timestamp');";
$result = mysqli_query($db, $insertQuery);
mysqli_close($db);

if ($result === false) {
    die(json_encode(['status' => 3, 'msg' => "Internal Server Error 2"]));
} else if ($result === true) {
    die(json_encode(['status' => 0, 'msg' => "Reminder added successfully"]));
}




// Functions 
function check_type($type, $isCustom)
{
    if ($isCustom == 'false') {
        if (($type === 'birthday') || ($type === 'anniversary') || ($type === 'trip') || ($type === 'party')) {
            return true;
        } else {
            return false;
        }
    } else if ($isCustom == 'true') {
        $type = str_ireplace(" ", "", $type);
        return ctype_alnum($type);
    }
}

function check_title($title)
{
    if (strlen($title < 3) && strlen($title) > 20) {
        return false;
    }
    return true;
}

function check_description($description)
{
    if (strlen($description) < 3 && strlen($description > 110)) {
        return false;
    }
    return true;
}

function check_time($hour, $amORpm)
{
    $valid = 1;
    try {
        $hour = (int)$hour;
        $valid = 0;
    } catch (\Throwable $th) {
        $valid = 1;
    }
    if ($valid === 1) return false;

    if ($hour < 1 && $hour > 12) return false;
    if ($amORpm !== 'am' && $amORpm !== 'pm') return false;
    return true;
}

function check_date($day, $month)
{
    $valid = 1;
    try {
        $day = (int) $day;
        $valid = 0;
    } catch (\Throwable $th) {
        $valid = 1;
    }
    if ($valid === 1) return false;

    $allMonths = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

    if (array_search($month, $allMonths) === false) return false;
    if ($day < 1 || $day > 31) return false;

    if ($month === 'february') {
        if ($day > 28) return false;
    } else if ($month === 'january' || $month === 'march' || $month === 'may' || $month === 'july' || $month === 'august' || $month === 'october' || $month === 'december') {
        if ($day > 31) return false;
    } else if ($month === 'april' || $month === 'june' || $month === 'september' || $month === 'november') {
        if ($day > 30) return false;
    }

    return true;
}
