<?php
date_default_timezone_set("Asia/Kolkata");

// Inlude all Global variables
require_once "../../_GLOBAL.php";

$month = date("F");
$month[0] = strtolower($month[0]);
$day = date("j");
$amORpm = date("a");
$hour = date("g");
$allResults = [];

$db = mysqli_connect($G_DB_HOST, $G_DB_USERNAME, $G_DB_PASSWORD, $G_DB_DATABASE_NAME);
if ($db === false) {
    die;
}

$checkQuery = "SELECT `id`,`user_id`,`type`,`title`,`description` FROM `reminders` WHERE `active` = '0' AND `done` = '1' AND `month` = '$month' AND `day` = '$day' AND `hour` = '$hour' AND `apm` = '$amORpm';";
$result = mysqli_query($db, $checkQuery);

if ($result === false) {
    die;
}

$numOfResults = mysqli_num_rows($result);
if ($numOfResults === 0) {
    echo "Today has no remiders\n";
} else {

    for ($i = 0; $i < $numOfResults; $i++) {
        $row = mysqli_fetch_assoc($result);
        $data = [
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'type' => $row['type'],
            'month' => "$month",
            'day' => "$day",
            'title' => $row['title'],
            'description' => $row['description'],
            'isToday' => true,
            'done' => false
        ];
        $allResults[] = $data;
    }
}

// get reminders for next day
$tomorrow = get_tomorrow();
echo "time: $hour $amORpm\n";
if ($tomorrow !== false) {
    // if ($hour === '11' && $amORpm === 'am' && $tomorrow !== false) {

    $day = $tomorrow['day'];
    $month = $tomorrow['month'];

    $checkQuery = "SELECT `id`,`user_id`,`type`,`title`,`description` FROM `reminders` WHERE `active` = '0' AND `done` = '1' AND `t_done` = '1' AND `month` = '$month' AND `day` = '$day';";
    echo $checkQuery . "\n";
    $result = mysqli_query($db, $checkQuery);

    if ($result === false) {
        goto exitTomorrow;
    }

    $numOfResults = mysqli_num_rows($result);
    if ($numOfResults === 0) {
        echo "Tomorrow has no reminders\n";
        goto exitTomorrow;
    }

    for ($i = 0; $i < $numOfResults; $i++) {
        $row = mysqli_fetch_assoc($result);
        $data = [
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'type' => $row['type'],
            'month' => "$month",
            'day' => "$day",
            'title' => $row['title'],
            'description' => $row['description'],
            'isToday' => false,
            'done' => false
        ];
        $allResults[] = $data;
    }
}
exitTomorrow:

mysqli_close($db);

$data = json_encode($allResults);

// Write the data in the data file
$file = fopen("./data/.htac_data", "w");
if ($file == false) die;
fwrite($file, $data);
fclose($file);


// Log into a file
$file = fopen("./data/step1.txt", "a+");
if ($file == false) die;
$local = date("l d F Y  h:i:s a", time());
fwrite($file, "Step 1 ran at: $local\n");
fclose($file);

echo $data;


/*   Functions   */

// Function to check if the date is tomorrow
function get_tomorrow()
{
    $today = date('j');
    $thisMonth = strtolower(date('F'));
    $isLeapYear = (date('L') == '1') ? true : false;
    $nextDay = '';
    $nextMonth = '';

    // If last date of february of a leap year
    if ($today == '29' && $thisMonth == 'february' && $isLeapYear === true) {
        return ['day' => '1', 'month' => 'march'];
    } else if ($today == '28' && $thisMonth == 'february' && $isLeapYear === false) {
        return ['day' => '1', 'month' => 'march'];
    } else if (($today === '31')) {
        if ($thisMonth === 'january' || $thisMonth === 'march' || $thisMonth === 'may' || $thisMonth === 'july' || $thisMonth === 'august' || $thisMonth === 'october' || $thisMonth === 'december') {
            $nextMonth = strtolower(date('F', (time() + 86400)));
            return ['day' => '1', 'month' => $nextMonth];
        }
    } else if ($today === '30') {
        if ($thisMonth === 'april' || $thisMonth === 'june' || $thisMonth === 'september' ||  $thisMonth === 'november') {
            $nextMonth = strtolower(date('F', (time() + 86400)));
            return ['day' => '1', 'month' => $nextMonth];
        } else {
            $nextDay = date('j', (time() + 86400));
            return ['day' => $nextDay, 'month' => $thisMonth];
        }
    } else {
        $nextDay = date('j', (time() + 86400));
        return ['day' => $nextDay, 'month' => $thisMonth];
    }
    return false;
}
