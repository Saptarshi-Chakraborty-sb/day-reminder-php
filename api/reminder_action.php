<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 404");
    die;
}

if ((!isset($_COOKIE['token'])) || ($_COOKIE['token'] === "")) {
    header("HTTP/1.1 401");
    die(json_encode(['status' => 1, 'msg' => "Authentication error. Please login again"]));
}

if ((!isset($_POST['action']) || ($_POST['action'] === "")) || !isset($_POST['id'])) {
    header("HTTP/1.1 404");
    die;
}

$id = $_POST['id'];
require_once "../_GLOBAL.php";

$user_id = get_token_data($_COOKIE['token'])['id'];
if ($user_id === false) {
    die(json_encode(['status' => 1, 'msg' => "Authentication error. Please login again"]));
}
// $user_id = '18';
date_default_timezone_set("Asia/Kolkata");

switch ($_POST['action']) {
    case 'edit':
        if (count($_POST) === 0) {
            header("HTTP/1.1 400");
            die;
        }

        $arr = [];

        if (isset($_POST['type']) && isset($_POST['isCustomType'])) {
            if (check_type($_POST['type'], $_POST['isCustomType']) === false) {
                die(json_encode(['status' => 4, 'msg' => "Incorrect Value. Check the value of type"]));
            }
            $arr['type'] = trim($_POST['type']);
        }

        if (isset($_POST['title'])) {
            if (check_title($_POST['title']) === false) {
                die(json_encode(['status' => 4, 'msg' => "Incorrect Value. Check the value of title"]));
            }
            $arr['title'] = trim($_POST['title']);
        }

        if (isset($_POST['description'])) {
            if (check_description($_POST['description']) === false) {
                die(json_encode(['status' => 4, 'msg' => "Incorrect Value. Check the value of description"]));
            }
            $arr['description'] = trim($_POST['description']);
        }

        if (isset($_POST['day']) && isset($_POST['month'])) {
            if (check_date($_POST['day'], $_POST['month']) === false) {
                die(json_encode(['status' => 4, 'msg' => "Incorrect Value. Check the value of date"]));
            }
            $arr['day'] = trim($_POST['day']);
            $arr['month'] = trim($_POST['month']);
        }

        if (isset($_POST['hour']) && isset($_POST['portionOfDay'])) {
            if (check_time($_POST['hour'], $_POST['portionOfDay']) === false) {
                die(json_encode(['status' => 4, 'msg' => "Incorrect Value. Check the value of time"]));
            }
            $arr['hour'] = trim($_POST['hour']);
            $arr['apm'] = trim($_POST['portionOfDay']);
        }

        if (isset($_POST['willRepeat'])) {
            $repeat = $_POST['willRepeat'];
            if ($repeat !== 'true' && $repeat !== 'false') {
                die(json_encode(['status' => 4, 'msg' => "Incorrect Value. Check the value of repeat"]));
            }
            $arr['wr'] = $repeat;
        }

        if (count($arr) < 1) {
            die(json_encode(['status' => 2, 'msg' => "Edit value un-available"]));
        }


        $db = connect_db();
        $checkQuery = "SELECT `active`,`user_id` FROM `reminders` WHERE `id` = '$id';";

        $result  = mysqli_query($db, $checkQuery);
        if ($result === false) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 2"]));
        }

        $dbData = mysqli_fetch_assoc($result);
        $isActive = $dbData['active'];
        if (($isActive !== '0')) {
            die(json_encode(['status' => 5, 'msg' => "You can not edit this reminder. Inactive Reminder", 'active' => $isActive]));
        } else if ($dbData['user_id'] != $user_id) {
            die(json_encode(['status' => 5, 'msg' => "You can not edit this reminder. Permission Error", 'user' => $user_id]));
        }

        $queryFields = "";
        foreach ($arr as $key => $value) {
            $queryFields .= "`$key` = '$value', ";
        }
        $timestamp = strval(time());
        $ip = $_SERVER['REMOTE_ADDR'];
        $queryFields .= "`ip` = '$ip', `created_at` = '$timestamp'";

        $insertQuery = "UPDATE `reminders` SET $queryFields WHERE `reminders`.`id` = '$id' AND `reminders`.`user_id` = '$user_id';";

        // echo "Insert Query:\n$insertQuery";

        $result = mysqli_query($db, $insertQuery);
        mysqli_close($db);
        if ($result === false) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 3"]));
        }

        if ($result == true) {
            die(json_encode(['status' => 0, 'msg' => "Successfully edited reminder"]));
        }

        header("HTTP/1.1 400");

        break;

    case 'deactive':
    case 'deactivate':
        $db = connect_db();

        $checkQuery = "SELECT `active` FROM `reminders` WHERE `id` = $id AND `user_id` = '$user_id';";
        $result = mysqli_query($db, $checkQuery);
        if ($result === false) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 2"]));
        }

        $active = mysqli_fetch_assoc($result)['active'];
        if ($active == '1') {
            die(json_encode(['status' => 2, 'msg' => "This reminder is already deactivated"]));
        }

        $deactivateQuery = "UPDATE `reminders` SET `active` = '1' WHERE `reminders`.`id` = $id AND `reminders`.`user_id` = '$user_id';";
        $result = mysqli_query($db, $deactivateQuery);
        if ($result === false) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 3"]));
        }
        mysqli_close($db);

        if ($result == true) {
            die(json_encode(['status' => 0, 'msg' => "Reminder is deactivated successfully"]));
        }
        break;


    case 'active':
        $db = connect_db();

        $checkQuery = "SELECT `active` FROM `reminders` WHERE `id` = $id AND `user_id` = '$user_id';";
        $result = mysqli_query($db, $checkQuery);
        if ($result === false) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 2"]));
        }

        $active = mysqli_fetch_assoc($result)['active'];
        if ($active == '0') {
            die(json_encode(['status' => 2, 'msg' => "This reminder is already active"]));
        }

        $deactivateQuery = "UPDATE `reminders` SET `active` = '0' WHERE `reminders`.`id` = $id AND `reminders`.`user_id` = '$user_id';";
        $result = mysqli_query($db, $deactivateQuery);
        if ($result === false) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 3"]));
        }
        mysqli_close($db);

        if ($result == true) {
            die(json_encode(['status' => 0, 'msg' => "Reminder is activated successfully"]));
        }
        break;

    default:
        header('HTTP/1.1 400');
        break;
}



/*    Functions    */

// Makes connection with database
function connect_db()
{
    global $G_DB_HOST, $G_DB_USERNAME, $G_DB_PASSWORD, $G_DB_DATABASE_NAME;
    $db = mysqli_connect($G_DB_HOST, $G_DB_USERNAME, $G_DB_PASSWORD, $G_DB_DATABASE_NAME);
    if ($db === false) {
        die(json_encode(['status' => 3, 'msg' => "Internal Server Error 1"]));
    }
    return $db;
}

// Validates a given reminder type
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

// Validates a given description
function check_title($title)
{
    if (strlen($title < 3) && strlen($title) > 20) {
        return false;
    }
    return true;
}

// Validates a given description
function check_description($description)
{
    if ($description == "") return true;
    else if (strlen($description) < 3 && strlen($description > 110)) {
        return false;
    }
    return true;
}

// Validates a given time
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

// Validates a given date
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
