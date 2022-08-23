<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('HTTP/1.1 404');
die;
}

if (!isset($_POST['email']) && !isset($_POST['token'])) {
header('HTTP/1.1 404');
die;
}

$email = $_POST['email'];
$token = $_POST['token'];

// $email = "sc.linkshortener.1@gmail.com";
// $token = "aceyfggyrvgfrwyurgvfrfwrfvgrfwufvfwgf";

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="shortcut icon" href="./img/favicon.jpg" type="image/jpg">
    <title>OTP - Day Notifier</title>
</head>

<body id="b">
    <div class="container my-2 mt-4 p-4">
        <h2 class=" text-center text-decoration-underline">Enter the OTP sent in your email address</h2>
        <p class="text-danger text-center"> (Don't reload this page)</p>

        <div class="my-4">
            <div class="mb-3">
                <label for="otp" class="form-label">OTP Code</label>
                <input type="text" class="form-control" id="otp" maxlength="5" placeholder="Enter OTP here" required>
                <div id="emailHelp" class="form-text">We have sent an OTP Code in your email address <b>(<?php echo $email ?>)</b> ,which is valid for 2 hours. <u>Also check spam/junk folder in your email</u></div>
            </div>
            <button id="submitBtn" class="btn btn-primary">Submit</button>

            <br>
            <br>
            <span class="font-monospace text-decoration-underline text-success fs-4" style="cursor: pointer;" id="goBackBtn">Return to previous page</span>
        </div>
    </div>


</body>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>

<!-- Custom JS -->
<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    let authToken = "<?php echo $token ?>";
    let email = "<?php echo $email ?>";
</script>
<script src="./views/js/otp.js"></script>

</html>