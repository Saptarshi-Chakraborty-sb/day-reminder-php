<?php

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header("HTTP/1.1 404");
    die;
} else if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['refer_code'])) {
    header("HTTP/1.1 400");
    die(json_encode(['status' => 1, 'msg' => "Invalid Request"]));
}

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

// Include Global Variables Page
require_once "../_GLOBAL.php";

$username = $_POST['username'];
$password = $_POST['password'];
$refer_code = $_POST['refer_code'];
if ($password !== $G_GLOBAL_SIGNUP_PASSWORD) {
    die(json_encode(['status' => 1, 'msg' => "Invalid Request"]));
}

$data = null;
$db = mysqli_connect($G_DB_HOST, $G_DB_USERNAME, $G_DB_PASSWORD, $G_DB_DATABASE_NAME);

// Check if the user already exists
$existsQuery = "SELECT `active` FROM `users` WHERE `username` = '$username';";
$result = mysqli_query($db, $existsQuery);
if ($result === false) {
    die(json_encode(["status" => 3, "msg" => "Internal Server Error 1"]));
}

if (mysqli_num_rows($result) == 1) {
    die(json_encode(["status" => 4, "msg" => "You already have an account. Try to login"]));
}

// Check refer code
$refCodeQuery = "SELECT `id` FROM `referrer` WHERE `ref_code` = '$refer_code' AND `done` = '1';";
$result = mysqli_query($db, $refCodeQuery);

if ($result === false) {
    die(json_encode(["status" => 3, "msg" => "Internal Server Error 1"]));
}

if (mysqli_num_rows($result) != 1) {
    die(json_encode(["status" => 2, "msg" => "You don't have an refer code. Contact with Admin"]));
}
$refCodeId = mysqli_fetch_assoc($result)['id'];


// Now Create User
$ip = $_SERVER['REMOTE_ADDR'];
$timestamp = strval(time());

do {
    $otp = strval(rand(10000, 99999));
    $checkQuery = "SELECT `id` FROM `users` WHERE `password` = '$otp' AND `username` = '$username';";

    $result = mysqli_query($db, $checkQuery);
    if ($result === false) {
        die(json_encode(["status" => 3, "msg" => "Internal Server Error 2"]));
    }

    if (mysqli_num_rows($result) == 0) {
        break;
    }
} while (1);

$query = "INSERT INTO `users` (`username`, `password`, `entry_code`, `ip`, `active`, `created_at`) VALUES ('$username', '$otp', '$refCodeId', '$ip', '0', '$timestamp');";
$result = mysqli_query($db, $query);

if ($result === false) {
    die(json_encode(["status" => 3, 'msg' => "Internal Server Error 3"]));
} else if ($result === true) {
    // Send Email
    $email  = sendEmail($username, $otp);
    if ($email === false) {
        die(json_encode(["status" => 5, 'msg' => "Failed to send email. Contact Admin"]));
    } else if ($email === true) {
        // Send Success Message
        $data = urlencode(openssl_encrypt("$otp-$username", $G_ENCRYPTION_ALGORITHM, $G_ENCRYPTION_SECRET_KEY, 0, $G_ENCRYPTION_IV));
        echo (json_encode(["status" => 0, 'msg' => "OTP Sent Successfully", 'data' => $data]));
        // Now mark the refer code as used
        $usedRefQuery = "UPDATE `referrer` SET `done` = '0' WHERE `referrer`.`ref_code` = '$refer_code' AND `referrer`.`id` = '$refCodeId';";
        $result = mysqli_query($db, $usedRefQuery);
        mysqli_close($db);
    } else {
        die(json_encode(["status" => 3, 'msg' => "Internal Server Error 4"]));
    }
}



// Functions
function sendEmail($to, $otp)
{
    global $G_SERVICE_GMAIL_USERNAME, $G_SERVICE_GMAIL_PASSWORD, $G_GMAIL_HOST, $G_GMAIL_PORT;

    $emailSubject = "Your OTP is $otp";
    $emailBody = "<h3>Hello, User ($to)</h3><p>Your OTP for <i>Day Reminder</i> is <h1>$otp</h1></p><p>Enter this otp in the website to create your account.</p><hr><p>We use OTP for login, so you don't have to remember a password. If your are getting error, feel free to contact with Admin.</p>";
    $emailAltBody = "Your otp for Day Reminder is = $otp";


    $mail = new PHPMailer(true);
    try {
        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = $G_GMAIL_HOST;                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $G_SERVICE_GMAIL_USERNAME;                     //SMTP username
        $mail->Password   = $G_SERVICE_GMAIL_PASSWORD;                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = $G_GMAIL_PORT;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom($G_SERVICE_GMAIL_USERNAME, 'Day Reminder');
        $mail->addAddress($to);     //Add a recipient
        $mail->addReplyTo($G_SERVICE_GMAIL_USERNAME, 'Day Reminder');

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $emailSubject;
        $mail->Body    = $emailBody;
        $mail->AltBody = $emailAltBody;

        $mail->send();
        return true;
    } catch (Exception $e) {
        die(json_encode(['status' => 3, 'msg' => "Email Send Error", 'error' => $e]));
        return false;
    }
}
