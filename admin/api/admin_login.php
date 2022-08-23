<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 404");
    die;
}

if ((!isset($_POST['username'])) || (!isset($_POST['password']))) {
    header("HTTP/1.1 404");
    die;
}

$username = $_POST['username'];
$password = $_POST['password'];


$file = fopen("../.htac_data", "r");
$fileData = fread($file, filesize("../.htac_data"));
fclose($file);
$data = json_decode($fileData, true);

$adminUsername = $data['adminUsername'];
$adminPassword = $data['adminPassword'];

if (($adminUsername === $username) && ($adminPassword === $password)) {
    if (session_status() !== PHP_SESSION_ACTIVE)
        session_start();
    $_SESSION['admin'] = "saptarshi";
    setcookie("admin", "true", 0, "/");
    header("Location: ../");
    die;
} else {
    if (session_status() !== PHP_SESSION_NONE)
        session_start();
    session_unset();
    session_destroy();
    header("Location: ../login.html");
    die;
}
