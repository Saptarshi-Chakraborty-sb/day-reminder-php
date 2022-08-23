<?php

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header("HTTP/1.1 404");
    die;
}

if ((!isset($_POST['username'])) || ($_POST['username'] === "")) {
    header("HTTP/1.1 400");
    die(json_encode(['status' => 1, 'msg' => "Incorrect Request"]));
}

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

// Include Global Variables
require_once "../_GLOBAL.php";

$username = $_POST['username'];

// Connect Database
$db = mysqli_connect($G_DB_HOST, $G_DB_USERNAME, $G_DB_PASSWORD, $G_DB_DATABASE_NAME);

if ($db === false) {
    die(json_encode(['status' => 3, 'msg' => 'Internal Server Error 1']));
}

$existsQuery = "SELECT `active`,`password` FROM  `users` WHERE `username` = '$username';";
$result = mysqli_query($db, $existsQuery);

if ($result === false) {
    die(json_encode(['status' => 3, 'msg' => 'Internal Server Error 2']));
} else if (mysqli_num_rows($result) !== 1) {
    die(json_encode(['status' => 2, 'msg' => "You don't have an account. Contact Admin for entry code"]));
}

$dbData = mysqli_fetch_assoc($result);
$password = (int) $dbData['password'];
$isActive = (int) $dbData['active'];

// Check if the user account is suspended
if ($isActive === 1) {
    die(json_encode(['status' => 0, 'msg' => "Your account is suspended. Contact Admin for this issue"]));
}

// Generate a new otp
do {
    $otp = strval(rand(10000, 99999));

    if ($otp !== $password) {
        break;
    }
} while (1);

$editQuery = "UPDATE `users` SET `password` = '$otp' WHERE `users`.`username` = '$username';";
$result = mysqli_query($db, $editQuery);

if ($result === false) {
    die(json_encode(['status' => 3, 'msg' => "Internal Server Error 3"]));
} else if ($result === true) {
    $mail = sendEmail($username, $otp);
    if ($mail === true) {
        // Send Success Message
        $data = urlencode(openssl_encrypt("$otp-$username", $G_ENCRYPTION_ALGORITHM, $G_ENCRYPTION_SECRET_KEY, 0, $G_ENCRYPTION_IV));
        die(json_encode(["status" => 0, 'msg' => "Entry Code Sent Successfully", 'data' => $data]));
    } else {
        die(json_encode(["status" => 4, 'msg' => "Email Sending Failed. Contact Admin"]));
    }
}


// Functions
function sendEmail($to, $otp)
{
    global $G_SERVICE_GMAIL_USERNAME, $G_SERVICE_GMAIL_PASSWORD, $G_GMAIL_HOST, $G_GMAIL_PORT;

    $emailSubject = "Your OTP is $otp";
    $emailBody = "<h3>Hello, User ($to)</h3><p>Your OTP for <i>Day Reminder</i> is <h1>$otp</h1></p><p>Enter this otp in the website to login to your account.</p><hr><p>We use OTP for login, so you don't have to remember a password. If your are getting error, feel free to contact with Admin.</p>";
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
