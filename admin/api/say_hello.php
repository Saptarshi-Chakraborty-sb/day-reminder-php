<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 404");
    die;
}

if ((!isset($_POST['hello'])) || ($_POST['hello'] !== 'world')) {
    header("HTTP/1.1 404");
    die;
}

if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();


if ((isset($_SESSION['admin'])) && ($_SESSION['admin'] === 'saptarshi')) {
    echo "Hello World";
} else {
    echo "Hello Stranger";
}
