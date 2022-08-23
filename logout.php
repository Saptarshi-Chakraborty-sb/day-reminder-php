<?php
if (isset($_COOKIE['token'])) {
    setcookie('token', null, (time() - 9999), "/");
    header("Location: ./login.php");
    echo "Logged in";
} else {
    header("HTTP/1.1 404");
}
