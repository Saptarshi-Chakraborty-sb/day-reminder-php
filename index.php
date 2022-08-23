<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="./views/css/index.css">
    <link rel="shortcut icon" href="./img/favicon.jpg" type="image/jpg">
    <title>Day Reminer</title>
</head>

<body>
    <div class="container mb-4 fs-4">
        <p class="fs-5 text-end text-primary text-decoration-underline m-0 p-0">
            <span class="m-0 p-0" style="cursor: pointer;" onclick="if(confirm('Do you really want to log out ?') === false) return;window.location.replace('./logout.php');">Logout</span>
        </p>

        <h2 class=" text-center text-decoration-underline" id="pageHeading">Schedule your Reminder for a Special Day</h2>

        <div class="my-4">
            <!-- Type of Day-->
            <div class="mb-3">
                <label for="type" class="form-label">Select type of Day</label>
                <select id="type" class="form-select form-select-lg mb-3" aria-label=".form-select-lg example">
                    <option value="birthday" selected>Birthday</option>
                    <option value="anniversary">Anniversary</option>
                    <option value="trip">Trip</option>
                    <option value="party">Party</option>
                    <option value="custom">Custom...</option>
                </select>
            </div>

            <!-- Custom Type Field -->
            <div class="mb-3" id="cTypeBox">
                <label for="ctype" class="form-label">Set a custom label for this day (eg. Exam, Library Book, Go to date)</label>
                <input type="text" class="form-control" maxlength="20" id="ctype">
                <div class="form-text">Only alphabets. numbers and spaces are allowed</div>
            </div>

            <!-- Date Field -->
            <div class="mb-3">
                <label for="date" class="form-label">Select the date </label> &nbsp;
                <select id="monthSelect" class="ps-3" style="min-width: 20%;">
                    <option value="january" selected>January</option>
                    <option value="february">February</option>
                    <option value="march">March</option>
                    <option value="april">April</option>
                    <option value="may">May</option>
                    <option value="june">June</option>
                    <option value="july">July</option>
                    <option value="august">August</option>
                    <option value="september">September</option>
                    <option value="october">October</option>
                    <option value="november">November</option>
                    <option value="december">December</option>
                </select>
                <select id="daySelect" class="ps-2" style="min-width: 10%;"></select>
            </div>

            <!-- Title Field -->
            <div class="mb-3">
                <label for="title" id="tlabel" class="form-label">Whose Birthday is it 🎂</label>
                <input type="text" class="form-control fs-5" maxlength="20" placeholder="max 20 characters" id="title">
            </div>

            <!-- Description Field -->
            <div class="mb-3">
                <label for="description" class="form-label">Description about this day (*optional)</label>
                <input type="text" class="form-control fs-5" maxlength="120" placeholder="max 100 characters" id="description">
            </div>

            <!-- Time -->
            <div class="mb-3">
                <label for="time" class="form-label">Select the time when the reminder email will be sent <small><i>(Indian Standard Time)</i></small></label>
                <select id="time" style="width:50vw !important ;" dropzone="20" class="form-select form-select-lg" aria-label=".form-select-lg example">
                    <option value="12:00am">12:00 AM</option>
                    <option value="01:00am">01:00 AM</option>
                    <option value="02:00am">02:00 AM</option>
                    <option value="03:00am">03:00 AM</option>
                    <option value="04:00am">04:00 AM</option>
                    <option value="05:00am">05:00 AM</option>
                    <option value="06:00am">06:00 AM</option>
                    <option value="07:00am">07:00 AM</option>
                    <option value="08:00am">08:00 AM</option>
                    <option value="09:00am">09:00 AM</option>
                    <option value="10:00am">10:00 AM</option>
                    <option value="11:00am">11:00 AM</option>
                    <option value="12:00pm">12:00 PM</option>
                    <option value="01:00pm">01:00 PM</option>
                    <option value="02:00pm">02:00 PM</option>
                    <option value="03:00pm">03:00 PM</option>
                    <option value="04:00pm">04:00 PM</option>
                    <option value="05:00pm">05:00 PM</option>
                    <option value="06:00pm">06:00 PM</option>
                    <option value="07:00pm">07:00 PM</option>
                    <option value="08:00pm">08:00 PM</option>
                    <option value="09:00pm">09:00 PM</option>
                    <option value="10:00pm">10:00 PM</option>
                    <option value="11:00pm">11:00 PM</option>
                </select>
                <div class="form-text">Reminder email will be sent at any time in a range of 30 minutes after the time</div>
            </div>

            <!-- Repeat -->
            <div class="mb-3">
                <div class="form-check">
                    <label for="repeat" class="form-check-label">Repeat this reminder every year</label>
                    <input class="form-check-input" type="checkbox" value="yes" id="repeat" checked>
                </div>
            </div>

            <!-- Submit Btn -->
            <div class="mb-3" id="submitBtnBox">
                <button id="addReminderBtn" class=" btn btn-lg btn-primary mt-3">Set Reminder</button>
            </div>
        </div>

        <div>
            <hr>
            <h2 class="text-center text-decoration-underline m-0">Your Reminders</h2>
            <button class="btn btn-warning" id="showAllBtn">Show all Reminder</button>
            <div id="showBox" class="my-3 py-2"></div>
        </div>
    </div>

    <p class="text-muted text-end m-0 p-0" style="font-size:12px !important;"><a class="text-muted" target="_blank" href="./changelog.html">Changelog</a></p>
</body>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
<script src="./views/js/index.js"></script>

</html>