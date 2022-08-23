console.log("%cotp.js", "color:yellow;font-size:18px;");

let btn = document.getElementById('submitBtn');
let otpField = document.getElementById('otp');
let returnBtn = document.getElementById('goBackBtn');

btn.addEventListener('click', () => {
    let otp = otpField.value.trim();
    let token = authToken;

    if (otp === "") return;

    let primaryFormData = { username: email, token, otp };
    let formData = new FormData();

    for (key in primaryFormData) {
        formData.append(key, primaryFormData[key]);
    }

    const params = {
        method: 'POST',
        body: formData
    }

    console.log(primaryFormData);


    fetch('/api/validate_otp.php', params).then(response => response.text()).then((result) => {
        console.log(result);

        let data;
        try {
            data = JSON.parse(result);
        } catch (error) {
            console.error(`JSON Parse Error: ${error}`);
            return;
        }

        let statusCode = data.status;
        let message = data.msg;



        if (statusCode == 0) {
            showAlert('s', "You are successfully logged in");
            nextRedirect("/", 2000);
            otpField.value = '';
        } else if (statusCode == 1) {
            showAlert('s', "Incorrect Request. Please check your OTP and try again", message);
        } else if (statusCode == 2) {
            showAlert('s', "Your account is not found. Contact Admin");
            nextRedirect('/signup.php');
        } else if (statusCode == 3) {
            showAlert('s', "Internal Server Error. Try again", message);
        } else if (statusCode == 4) {
            showAlert('s', "Authentication Error. Please login again", message);
            nextRedirect('/login.php');
        } else if (statusCode == 5) {
            showAlert('s', "Your account is suspended. Please contact to Admin");
            nextRedirect('/login.php');
        } else if (statusCode == 6) {
            showAlert('s', "OTP for your account has been changed. Please login again");
            nextRedirect('/login.php');
        }
    });
});


// return back button
returnBtn.addEventListener('click', goBack);






// Functions
function goBack() {
    let lastURL = document.referrer;
    window.location.replace(lastURL);
}

function showAlert(type, message, additional = null) {
    alert(message);
    if (additional !== null)
        console.log("ERROR: " + additional);
}

function nextRedirect(location, time = 3000) {
    setTimeout(() => {
        window.location.replace(location);
    }, time);
}

function sel(e) {
    alert("selecting");
}