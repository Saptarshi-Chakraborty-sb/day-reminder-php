<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="shortcut icon" href="./img/favicon.jpg" type="image/jpg">
    <title>Signup - Day Notifier</title>
</head>

<body id="b">
    <div class="container my-2 mt-4 p-4">
        <h2 class=" text-center text-decoration-underline">Create an account to use Day Reminders</h2>

        <div class="my-4">
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" autocomplete="email" class="form-control" id="email" aria-describedby="emailHelp" required>
                <div id="emailHelp" class="form-text">Use your primary email address, which you check daily. Turn on notifications for the email app in your mobile</div>
            </div>

            <div class="mb-3">
                <label for="code" class="form-label">Entry Code (*provided by Admin)</label>
                <input type="text" autocomplete="off" class="form-control" id="code" aria-describedby="emailHelp" required>
            </div>

            <button id="submitBtn" class="btn btn-primary">Create Account</button>
        </div>

        <br>
        <h5 class="font-monospace">Already have an account? <a href="/login.php">Login Here</a></h5>
    </div>


</body>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>

<!-- Custom JS -->
<script src="./views/js/signup.js"></script>

</html>