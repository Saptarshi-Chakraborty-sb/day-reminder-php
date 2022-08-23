<?php
date_default_timezone_set("Asia/Kolkata");

// Open data file
$file = fopen("./data/.htac_data", "r");
$fileData = fread($file, filesize("./data/.htac_data"));
fclose($file);

if (($fileData == "") || ($fileData == null)) die;

$data = json_decode($fileData, true);
if ($data == null) die;
$numOfElements = count($data);

// var_dump($data);

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once '../../PHPMailer/src/Exception.php';
require_once '../../PHPMailer/src/PHPMailer.php';
require_once '../../PHPMailer/src/SMTP.php';

// Include all Global variables
require_once "../../_GLOBAL.php";

$db = mysqli_connect($G_DB_HOST, $G_DB_USERNAME, $G_DB_PASSWORD, $G_DB_DATABASE_NAME);
if ($db === false) die;

for ($i = 0; $i < $numOfElements; $i++) {
    $element = $data[$i];

    $done = $element['done'];
    if ($done == true) continue;

    $id = $element['id'];
    $userId = $element['user_id'];
    $type = $element['type'];
    $day = $element['day'];
    $month = $element['month'];
    $title = $element['title'];
    $description = $element['description'];
    $isToday = $element['isToday'];


    $userQuery = "SELECT `username`,`active` FROM `users` WHERE `id` = '$userId';";
    $result = mysqli_query($db, $userQuery);

    if ($result === false) continue;
    if (mysqli_num_rows($result) === 0) continue;

    $userData = mysqli_fetch_assoc($result);
    $isActive = $userData['active'];
    $email = $userData['username'];

    if ($isActive != 0) continue;

    $emailSubject = make_subject($type, $title, $day, $month);
    $emailBody = make_body($type, $title, $description, $day, $month);
    $emailAltBody = make_altBody($type, $title, $description, $day, $month);

    $j = $i + 1;
    echo "$j. Reminder Data = \n";
    echo "reminder_id: $id\n";
    echo "type: $type \nday: $day \nmonth: $month \ntitle: $title\ndescription: $description\n";
    echo "email: $email";
    echo "---------------------------------\n";

    $email = sendReminderEmail($email, $emailSubject, $emailBody, $emailAltBody);
    echo "\n Email result = ";
    var_dump($email);
    if ($email === false) continue;
    elseif ($email !== true) continue;

    // echo "isToday: " . print_r($isToday);
    $successQuery = null;
    if ($isToday === true) {
        $successQuery = "UPDATE `reminders` SET `done` = '0' WHERE `reminders`.`id` = '$id';";
    } else {
        $successQuery = "UPDATE `reminders` SET `t_done` = '0' WHERE `reminders`.`id` = '$id';";
    }

    if ($successQuery != null) {
        $result = mysqli_query($db, $successQuery);
        if ($result === false) continue;
        else if ($result === true) {
            $data[$i]['done'] = true;
        }
    }

    if ($i >= 2) break;
}

mysqli_close($db);

$file = fopen("./data/.htac_data", "w");
$fileData = json_encode($data);
fwrite($file, $fileData);
fclose($file);


// Log in file
$file = fopen("./data/step2.txt", "a+");
if ($file == false) die;
$local = date("l d F Y  h:i:s a", time());
fwrite($file, "Step 2 ran at: $local\n");
fclose($file);



/*    Functions    */

// Function to send email
function sendReminderEmail($to, $emailSubject, $emailBody, $emailAltBody)
{
    global $G_SERVICE_GMAIL_USERNAME, $G_SERVICE_GMAIL_PASSWORD, $G_GMAIL_HOST, $G_GMAIL_PORT;

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
        return false;
    }
}

// Makes email Subject
function make_subject($type, $title, $day, $month)
{
    $subject = "";
    $today = date('j');
    $tenseOfDay = ($today == $day) ? "Today" : "Tomorrow";
    $subject .= "$tenseOfDay - ";

    if ($type === 'birthday') {
        $subject .= "Birthday of $title";
    } else if ($type === 'anniversary') {
        $subject .= "Anniversary of $title";
    } else if ($type === 'trip') {
        $subject .= "Trip at $title";
    } else if ($type === 'party') {
        $subject .= "Party at $title";
    } else {
        $subject = "Today|$type - $title";
    }
    $subject .= " ($day $month)";
    return $subject;
}

// Make Email Body
function make_body($type, $title, $description, $day, $month)
{
    $body = "";
    $firstPart = "";
    $middlePart = "";
    $lastPart = "";
    $today = date('j');
    $year = date('Y');
    $tenseOfDay = ($today == $day) ? "Today" : "Tomorrow";

    if ($type === 'birthday') {
        $firstPart = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Day Reminder</title></head><body><center><h3><font face="monospace">(' . $day . ' ' . $month . ' ' . $year . ')</font></h3><p>' . $tenseOfDay . ' is the <b>Birthday</b> of</p><h2>' . $title . '</h2><hr></center>';

        if ($description !== "") {
            $middlePart = '<h3><u>Can you remember him/her ?</u></h3><p>' . $description . '</p><hr>';
        }

        if ($today == $day) {
            $lastPart = '<h4>Oh god, at last your remembered 😀, Now give him/her a warm wish</h4>
            <blockquote style="border: 2px solid black; padding: 10px;"><i>Happy Birthday - <b>' . $title . '</b>,<br></i><i>This birthday, I wish you abundant happiness and love. May all your dreams turn into reality and may lady luck visit your home today. Happy birthday to one of the sweetest people I’ve ever known.</i></blockquote><small style="color:#4f4f4f;">(Copy the above text & use it)</small></body></html>';
        }
    } elseif ($type === 'anniversary') {
        $firstPart = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Day Reminder</title></head><body><center><h3><font face="monospace">(' . $day . ' ' . $month . ' ' . $year . ')</font></h3><p>' . $tenseOfDay . ' is the <b>Anniversary</b> of</p><h2>' . $title . '</h2><hr></center>';

        if ($description !== "") {
            $middlePart = '<h3><u>Can you remember him/her ?</u></h3><p>' . $description . '</p><hr>';
        }

        $lastPart = "</body></html>";
    } else if ($type == 'trip') {
        $firstPart = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Day Reminder</title></head><body><center><h3><font face="monospace">(' . $day . ' ' . $month . ' ' . $year . ')</font></h3><p>' . $tenseOfDay . ' is the <b>Trip</b> at</p><h2>' . $title . '</h2><hr></center>';

        if ($description !== "") {
            $middlePart = '<h3><u>More information about this trip...</u></h3><p>' . $description . '</p><hr>';
        }

        $lastPart = "</body></html>";
    } else if ($type === 'party') {
        $firstPart = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Day Reminder</title></head><body><center><h3><font face="monospace">(' . $day . ' ' . $month . ' ' . $year . ')</font></h3><p>' . $tenseOfDay . ' is <b>Party</b> of</p><h2>' . $title . '</h2><hr></center>';

        if ($description !== "") {
            $middlePart = '<h3><u>More information about this party...</u></h3><p>' . $description . '</p><hr>';
        }

        $lastPart = "</body></html>";
    } else {
        $firstPart = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Day Reminder</title></head><body><center><h3><font face="monospace">(' . $day . ' ' . $month . ' ' . $year . ')</font></h3><p>' . $tenseOfDay . ' is <b>' . $type . '</b></p><h2>' . $title . '</h2><hr></center>';

        if ($description !== "") {
            $middlePart = '<h3><u>More information about this day...</u></h3><p>' . $description . '</p><hr>';
        }

        $lastPart = "</body></html>";
    }


    $body = $firstPart . $middlePart . $lastPart;
    return $body;
}

// Makes Email Alternative Body (for, non-HTML clients)
function make_altBody($type, $title, $description, $day, $month)
{
    $today = date('j');
    $year = date('Y');
    $altBody = "";
    $firstPart = "";
    $middlePart = "";
    $lastPart = "";

    if ($today == $day)
        $firstPart .= "Today is ";
    else
        $firstPart .= "Tomorrow is ";

    if ($type === 'birthday') {
        $middlePart = "Birthday of $title. ";
    } else if ($type === 'anniversary') {
        $middlePart = "Anniversary of $title. ";
    } else if ($type === 'trip') {
        $middlePart = "Trip at $title. ";
    } else if ($type === 'party') {
        $middlePart = "Party at $title. ";
    } else {
        $middlePart = "$type - $title.";
    }

    if ($description != "") {
        $lastPart = " Details: $description";
        if ($lastPart[(strlen($lastPart) - 1)] !== ".")
            $lastPart .= ".";
    }
    $lastPart .= " --- Date: $day $month $year";

    $altBody = $firstPart . $middlePart . $lastPart;
    return $altBody;
}

// Function to log the email that is sent successfully
function log_email()
{
    return true;
}
