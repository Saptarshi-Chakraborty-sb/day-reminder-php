<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header("HTTP/1.1 404");
    die;
}

if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();

if (!isset($_SESSION['admin'])) {
    header("HTTP/1.1 404");
    die;
}

session_unset();
session_destroy();

setcookie("admin", null, (time() - 9999), "/");
if (isset($_COOKIE['PHPSESSID']))
    setcookie("PHPSESSID", null, (time() - 9999), "/");


header("Location: ./");
