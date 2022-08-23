<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 404");
    die;
}

if ((!isset($_POST['action'])) || ($_POST['action'] === "")) {
    header("HTTP/1.1 404");
    die;
}

if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();

if ((!isset($_SESSION['admin'])) || ($_SESSION['admin'] !== 'saptarshi')) {
    die(json_encode(['status' => 1, "msg" => "Please login again"]));
}


date_default_timezone_set("Asia/Kolkata");

if ($_POST['action'] === 'show') {

    $file = fopen("../../_GLOBAL.php", "r");
    $fileData = fread($file, filesize("../../_GLOBAL.php"));
    fclose($file);

    $globalVars = [];

    $firstPosition = 0;

    $int1 = strpos($fileData, "\nfunction");
    $str1 = ltrim(substr($fileData, 5, $int1 - 5));

    $i = 0;
    while (true) {
        $int0 = strpos($str1, "$");
        if ($int0 === false) {
            break;
        }
        $str1 = substr($str1, $int0);

        $int2 = strpos($str1, "\n");
        $str2 = trim(substr($str1, 0, $int2));

        $int2 = strpos($str2, "=");
        $key = trim(substr($str2, 1, $int2 - 1));

        $str3 = trim(substr($str2, $int2));
        // echo "str3:($str3)\n";
        $int3 = strpos($str3, ";");
        $value = trim(substr($str3, 1, $int3 - 1));

        //check the datatype of the value
        $strlen = strlen($value);
        $dataType = "string";

        if ($value === "") {
            $do = "Nothing";
            $dataType = "empty string";
        } else if (($value[0] === '"') && ($value[($strlen - 1)] === '"')) {
            $value = substr($value, 1, $strlen - 2);
        } else if ($value === "true" || $value === "false" || $value === "TRUE" || $value === "FALSE") {
            $value = boolval($value);
            $dataType = 'bool';
        } elseif ($value == "null" || $value == "NULL") {
            $value = null;
            $dataType = 'null';
        } else if (strpos($value, ".") !== false) {
            $value = floatval($value);
            $dataType = 'float';
        } else {
            $value = intval($value);
            $dataType = 'int';
        }

        // echo "key: ($key)\n";
        // echo "value: ($value)\n";
        // echo "Datatype: $dataType\n";
        // echo "-----------------------\n";

        $globalVars[$key] = $value;

        $str1 = ltrim(substr($str1, $int2));
    }

    if (count($globalVars) === 0) {
        die(json_encode(['status' => 2, 'msg' => "No variable found"]));
    }
    die(json_encode(['status' => 0, 'msg' => "Successfully got all variables", 'data' => $globalVars]));


    // main in end
    die;
}
header("HTTP/1.1 400");
die;
