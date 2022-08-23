console.log("%clogin.js", "color:yellow;font-size:18px;");

let body = document.getElementById('b');
let emailField = document.getElementById('email');
let btn = document.getElementById('submitBtn');

document.addEventListener('DOMContentLoaded', () => {
    checkLogin();
});


btn.addEventListener('click', () => {
    let email = emailField.value.trim();

    if (email === "") return;
    console.log(`email: (${email})`);

    let primaryFormData = { username: email };
    let formData = new FormData();

    for (key in primaryFormData) {
        formData.append(key, primaryFormData[key]);
    }

    const params = {
        method: 'POST',
        body: formData
    }

    fetch('/api/login.php', params).then(response => response.text()).then((result) => {
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
            console.info("Successfully logged in");
            let token = data.data;
            loginSuccess(token, email);
            emailField.value = "";
        } else if (statusCode == 1) {
            showAlert('Incorrect Request. Please check that you filled the requeired fields correctly', message);
        } else if (statusCode == 2) {
            showAlert(`You don't have and account. Please contact with admin to get an Entry Code`, message);
        } else if (statusCode == 3) {
            showAlert(`Internal Server Error. Please try later`, message);
        } else if (statusCode == 4) {
            showAlert(`Failed to send a email. Contact Admin`, message);
        }
    }).catch((err) => {
        alert(`${err}`);
        console.log(err);
    });



});


function showAlert(type, message, additional = null) {
    alert(message);
    if (additional !== null)
        console.log("ERROR: " + additional);
}

function loginSuccess(token, email) {
    console.log(`email:(${email}) , token:(${token})`);
    // return;

    let form = document.createElement('form');
    form.method = 'POST';
    form.action = '/otp.php';
    form.style.display = 'none';

    let tokenInput = document.createElement('input');
    tokenInput.type = "text";
    tokenInput.name = "token";
    tokenInput.value = token;

    let emailInput = document.createElement('input');
    emailInput.type = 'email';
    emailInput.name = "email";
    emailInput.value = email;

    let btn = document.createElement('input');
    btn.type = 'submit';


    form.appendChild(emailInput);
    form.appendChild(tokenInput);
    form.append(btn);

    body.appendChild(form);

    btn.click();
}

// Checks if User is logged in
function checkLogin() {
    let allCookies = document.cookie;
    let haveCookie = false;

    if (allCookies.includes("token")) {
        let a = allCookies.indexOf("token");

        a = String('token').length + 1;

        let tempStr1 = allCookies.slice(a);

        let b = tempStr1.indexOf(";");

        if (b === -1) {
            b = tempStr1.length;
        }

        if ((b - a) > 20) {
            haveCookie = true;
        }
    }

    if (haveCookie === true) {
        let body = document.getElementsByTagName('body')[0];
        let box = body.firstElementChild;
        console.log(box);

        box.innerHTML = "";

        box.innerHTML = `
        <h1 class="text-center text-decoration-underline">&nbsp; You are already Logged In &nbsp;</h1>
        <h3 class="text-center my-4">You can now go to the dashboard and access all you reminders</h3>
        <h4 class="text-center text-primary text-decoration-underline" onclick="window.location.replace('/')" style="cursor:pointer;">Click here to go to Dashboard</h4>
        `;

        window.location.replace('/');
    }
}