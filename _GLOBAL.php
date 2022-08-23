<?php

$G_SERVER_DOMAIN = "localhost";
$G_SERVER_PATH = "";

// Server Details
$G_DB_HOST = "localhost";
$G_DB_USERNAME = "root";
$G_DB_PASSWORD = "";
$G_DB_DATABASE_NAME = "day_reminder";

$G_GLOBAL_SIGNUP_PASSWORD = "a634rgvrggvg89asdD8f";

// Encryption
$G_ENCRYPTION_ALGORITHM = "aes-256-cbc";
$G_ENCRYPTION_SECRET_KEY = "asdcf4r9r8re14r4rv948ce";
$G_ENCRYPTION_IV = "c187vggttgtg649r";


// Gmail
$G_GMAIL_HOST = "smtp.gmail.com";
$G_GMAIL_PORT = 465;
$G_SERVICE_GMAIL_USERNAME = "example@gmail.com";
$G_SERVICE_GMAIL_PASSWORD = "password";



function get_token_data($token)
{
    global $G_ENCRYPTION_ALGORITHM, $G_ENCRYPTION_SECRET_KEY, $G_ENCRYPTION_IV;

    $data = openssl_decrypt($token, $G_ENCRYPTION_ALGORITHM, $G_ENCRYPTION_SECRET_KEY, 0, $G_ENCRYPTION_IV);

    if ($data === false) return false;

    $id = substr((strchr($data, "=")), 1);
    if ($id === false) return false;

    $tempStr = strchr($data, "=", true);
    if ($tempStr === false) return false;

    $email = substr(strchr($tempStr, "-"), 1);
    if ($email === false) return false;

    if (strchr($data, "-", true) !== "dayreminder") return false;

    return ['email' => $email, 'id' => $id];
}
