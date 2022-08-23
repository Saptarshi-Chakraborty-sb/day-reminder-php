<?php
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header("HTTP/1.1 404");
    die;
}

if ((!isset($_POST['username'])) || ($_POST['username'] === "") || (!isset($_POST['token'])) || ($_POST['token'] === "") || (!isset($_POST['otp'])) || ($_POST['otp'] === "")) {
    header("HTTP/1.1 400");
    die(json_encode(['status' => 1, 'msg' => "Incorrect Request"]));
}

// Include Global Variables
require_once "../_GLOBAL.php";

$username = $_POST['username'];
$token = urldecode($_POST['token']);
$otp = $_POST['otp'];

// $token .= "adc";
$data = check_auth_token($token);
if ($data === false) {
    die(json_encode(['status' => 2, 'msg' => "Authentication Error. Please login again"]));
}

if (($data['email'] !== $username) || ($data['otp'] != $otp)) {
    die(json_encode(['status' => 4, 'msg' => "Authentication Error. Please login again"]));
}

$db = mysqli_connect($G_DB_HOST, $G_DB_USERNAME, $G_DB_PASSWORD, $G_DB_DATABASE_NAME);

if ($db === false) {
    die(json_encode(['status' => 3, 'msg' => 'Internal Server Error 1']));
}

$existsQuery = "SELECT `id`,`active`,`password` FROM  `users` WHERE `username` = '$username';";
$result = mysqli_query($db, $existsQuery);
mysqli_close($db);

if ($result === false) {
    die(json_encode(['status' => 3, 'msg' => 'Internal Server Error 2']));
} else if (mysqli_num_rows($result) !== 1) {
    die(json_encode(['status' => 2, 'msg' => "You don't have an account. Contact Admin for entry code"]));
}

$dbData = mysqli_fetch_assoc($result);
$isActive = (int)$dbData['active'];
$password = (int)$dbData['password'];
$id = (int)$dbData['id'];

if ($isActive !== 0) {
    die(json_encode(['status' => 5, 'msg' => "Your account is suspended. Please contact Admin"]));
}

if ($password != $otp) {
    die(json_encode(['status' => 6, 'msg' => "OTP for your account has been changed. Please try to login again"]));
} else if ($password == $otp) {
    $userToken = make_token($username, $id);
    setcookie('token', $userToken, (time() + (60 * 60 * 24 * 30 * 12)), "/");
    die(json_encode(['status' => 0, 'msg' => 'Validation successfull']));
}


// Functions
function make_token($email, $id)
{
    global $G_ENCRYPTION_ALGORITHM, $G_ENCRYPTION_SECRET_KEY, $G_ENCRYPTION_IV;
    $data = openssl_encrypt("dayreminder-$email=$id", $G_ENCRYPTION_ALGORITHM, $G_ENCRYPTION_SECRET_KEY, 0, $G_ENCRYPTION_IV);
    if ($data === false) return false;
    return $data;
}

function check_auth_token($auth_token)
{
    global $G_ENCRYPTION_ALGORITHM, $G_ENCRYPTION_SECRET_KEY, $G_ENCRYPTION_IV;
    $decoded = null;
    try {
        $decoded = openssl_decrypt($auth_token, $G_ENCRYPTION_ALGORITHM, $G_ENCRYPTION_SECRET_KEY, 0, $G_ENCRYPTION_IV);
    } catch (\Throwable $th) {
        $decoded = null;
    }

    if ($decoded === false || $decoded === null) {
        return false;
    } else if ($decoded !== false) {
        $otp = strchr($decoded, "-", true);
        $email = substr(strchr($decoded, "-"), 1);
        return ['otp' => $otp, 'email' => $email];
    }
    return false;
}
