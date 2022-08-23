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
    case 'change':
        if ((!isset($_POST['oldPassword'])) || (!isset($_POST['newEmail']))) {
            header("HTTP/1.1 404");
            die;
        }

        $oldEmail = $_POST['oldEmail'];
        $oldPassword = $_POST['oldPassword'];
        $newEmail = $_POST['newEmail'];
        $newPassword = $_POST['newPassword'];

        $fileName = "../.htac_data";
        $file = fopen($fileName, "r");
        if ($file === false) {
            die(json_encode(['status' => 2, 'msg' => "Data file missing"]));
        }
        $fileData = fread($file, filesize($fileName));
        fclose($file);

        $data;
        try {
            $data = json_decode($fileData, true);
            if ($data === false)
                die(json_encode(['status' => 3, 'msg' => "Internal Server Error 1"]));
        } catch (\Throwable $th) {
            die(json_encode(['status' => 3, 'msg' => "Internal Server Error 1"]));
        }

        if (($data['adminUsername'] === $oldEmail) && ($data['adminPassword'] === $oldPassword)) {
            $file = fopen($fileName, "w");
            $data = ['adminUsername' => $newEmail, 'adminPassword' => $newPassword, 'time' => strval(time())];
            $done = fwrite($file, json_encode($data));
            if ($done === false) {
                die(json_encode(['status' => 3, 'msg' => "Internal Server Error 2"]));
            }
            fclose($file);
            die(json_encode(['status' => 0, 'msg' => "Successfully changed credentials"]));
        } else {
            die(json_encode(['status' => 4, 'msg' => "Incorrect credentials"]));
        }
        break;

    case 'show':

        $fileName = "../.htac_data";
        $file = fopen($fileName, "r");
        if ($file === false) {
            die(json_encode(['status' => 2, 'msg' => "Data file missing"]));
        }
        $fileData = fread($file, filesize($fileName));
        fclose($file);
        $arr = json_decode($fileData, true);
        if ($arr === null) {
            die(json_encode(['status' => 4, 'msg' => "Can not get data"]));
        }

        $timestamp = (int)$arr['time'];
        $time = date("D d M Y h:i a", $timestamp);

        $data = ['username' => $arr['adminUsername'], 'password' => $arr['adminPassword'], 'time' => $time];

        die(json_encode(['status' => 0, 'msg' => "Successfully fot data", 'data' => $data]));

        break;

    default:
        header("HTTP/1.1 404");
        break;
}
