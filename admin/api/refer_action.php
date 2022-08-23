<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 404");
    die;
}

if ((!isset($_POST['action'])) || ($_POST['action'] === "")) {
    header("HTTP/1.1 404");
    die;
}

if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();

if ((!isset($_SESSION['admin'])) || ($_SESSION['admin'] !== 'saptarshi')) {
    die(json_encode(['status' => 1, "msg" => "Please login again"]));
}

require_once "../../_GLOBAL.php";
date_default_timezone_set("Asia/Kolkata");

switch ($_POST['action']) {
    case 'add':
        $name = $_POST['name'];
        $details = $_POST['description'];
        $ref_code = $_POST['refCode'];

        $db = connect_db();
        $checkQuery = "SELECT `id` FROM `referrer` WHERE `ref_code` = '$ref_code';";

        $result = mysqli_query($db, $checkQuery);
        if ($result === false) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 2"]));
        }

        if (mysqli_num_rows($result) !== 0) {
            die(json_encode(['status' => 2, 'msg' => "This refer code already exists", 'code' => "$ref_code"]));
        }

        $timestamp = time();
        $addQuery = "INSERT INTO `referrer` (`name`, `details`, `ref_code`, `done`, `created_at`) VALUES ('$name', '$details', '$ref_code', '1', '$timestamp');";

        $result = mysqli_query($db, $addQuery);
        mysqli_close($db);
        if ($result === false) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 3"]));
        }
        die(json_encode(['status' => 0, 'msg' => "Successfully added new refferal code"]));
        break;

    case 'edit':
        $id = $_POST['id'];
        $name = $_POST['name'];
        $details = $_POST['description'];
        $ref_code = $_POST['refCode'];

        $db = connect_db();
        $checkQuery = "SELECT `done` FROM `referrer` WHERE `id` = '$id';";

        $result = mysqli_query($db, $checkQuery);
        if ($result === false) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 2"]));
        } else if (mysqli_num_rows($result) === 0) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 3"]));
        }

        $isDone =  mysqli_fetch_assoc($result)['done'];
        if ($isDone == 0 || $isDone == 2) {
            die(json_encode(['status' => 2, 'msg' => "You can't edit this ref code",]));
        }

        $updateQuery = "UPDATE `referrer` SET `ref_code` = '$ref_code',`name` = '$name',`details` = '$details' WHERE `id` = '$id';";

        $result = mysqli_query($db, $updateQuery);
        mysqli_close($db);
        if ($result === false) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 4"]));
        }

        $arr = ['id' => $id, 'refCode' => $ref_code, 'name' => $name, 'details' => $details];
        die(json_encode(['status' => 0, 'msg' => "Successfully edited refferal code", 'data' => $arr]));
        break;

        break;

    case 'delete':
        $id = $_POST['id'];
        $db = connect_db();

        $checkQuery = "SELECT `done` FROM `referrer` WHERE `id` = '$id';";
        $result = mysqli_query($db, $checkQuery);
        if ($result === false) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 2"]));
        }

        if (mysqli_num_rows($result) === 0) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 3"]));
        }

        $done = (int)mysqli_fetch_assoc($result)['done'];
        if ($done === 0) {
            die(json_encode(['status' => 4, 'msg' => "This code is already used. No meaning to delete this now"]));
        } else if ($done === 2) {
            die(json_encode(['status' => 2, 'msg' => "This code is already deleted"]));
        } else if ($done === 1) {
            // We will do soft delete only
            $deleteQuery = "UPDATE `referrer` SET `done` = '2' WHERE `referrer`.`id` = '$id';";

            $result = mysqli_query($db, $deleteQuery);
            mysqli_close($db);
            if ($result === false) {
                die(json_encode(['status' => 3, 'msg' => "Internal Server Error 4"]));
            }
            // After success
            die(json_encode(['status' => 0, 'msg' => "This code is successfully deleted"]));
        }
        break;

    case 'show':
        if (!isset($_POST['key'])) {
            die(json_encode(['status' => 2, 'msg' => "Bad Request"]));
        }

        if ($_POST['key'] !== "aStdYf234y*3rdf52") {
            die(json_encode(['status' => 2, 'msg' => "Incorrect Key"]));
        }

        $db = connect_db();
        $getAllQuery = "SELECT * FROM `referrer`;";

        $result = mysqli_query($db, $getAllQuery);
        mysqli_close($db);
        if ($result === false) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 2"]));
        }

        $numOfResults = mysqli_num_rows($result);
        if ($numOfResults === 0) {
            die(json_encode(['status' => 4, 'msg' => "There is no refer code. Create one first"]));
        }

        $all = mysqli_fetch_all($result, MYSQLI_ASSOC);
        for ($i = 0; $i < $numOfResults; $i++) {
            $timestamp = (int)$all[$i]['created_at'];
            $all[$i]['created_at'] = date("D d M Y h:i a", $timestamp);
        }

        die(json_encode(['status' => 0, 'msg' => "All results got successfully", 'numOfResult' => $numOfResults, 'data' => $all]));
        break;
    default:
        header("HTTP/1.1 400");
        break;
}






/*          Functions          */

function connect_db()
{
    global $G_DB_HOST, $G_DB_USERNAME, $G_DB_PASSWORD, $G_DB_DATABASE_NAME;
    $db = mysqli_connect($G_DB_HOST, $G_DB_USERNAME, $G_DB_PASSWORD, $G_DB_DATABASE_NAME);
    if ($db === false || $db === null)
        die(json_encode(['status' => 3, "msg" => "Internal Server Error 1"]));
    return $db;
}
