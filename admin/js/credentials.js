let submitBtn = document.getElementById('submitBtn');
let oldEmailField = document.getElementById('oldEmail');
let oldPasswordField = document.getElementById('oldPassword');
let newEmailField = document.getElementById('newEmail');
let newPasswordField = document.getElementById('newPassword');
let showUsernameField = document.getElementById('showUsername');
let showPasswordField = document.getElementById('showPassword');
let showButtton = document.getElementById('showButton');
let showTime = document.getElementById('showTime');


submitBtn.addEventListener("click", changeCredentials);

showButtton.addEventListener("click", getCredentials);



function changeCredentials() {
    let oldEmail = oldEmailField.value.trim();
    let oldPassword = oldPasswordField.value.trim();
    let newEmail = newEmailField.value.trim();
    let newPassword = newPasswordField.value.trim();

    let primaryFormData = { action: 'change', oldEmail, oldPassword, newEmail, newPassword };
    let formData = new FormData();
    for (key in primaryFormData)
        formData.append(key, primaryFormData[key]);
    let params = { method: 'POST', body: formData };

    fetch('./api/credentials.php', params).then(res => res.text()).then((_result) => {
        let result;
        try {
            result = JSON.parse(_result);
        } catch (error) {
            alert("There is an error");
            console.log(`ERROR json: ${error}`);
        }

        let statusCode = result.status;
        let message = result.msg;

        if (statusCode === 0) {
            showAlert('s', "Login credentials are changed now");
            clearForm();
        } else if (statusCode === 1) {
            showAlert('d', "You are not logged in", message);
            window.location.replace("./login.html");
        } else if (statusCode === 2) {
            showAlert('d', "Data can't be accessed", message)
        } else if (statusCode === 3) {
            showAlert('d', "Internal Server Error", message);
        } else if (statusCode === 4) {
            showAlert('d', "Incorrect credentials", message);
        }
    }).catch((error) => {
        alert("there is an error");
        console.log(`ERROR fetch: ${error}`);
    })
}


function showAlert(type, message, additional = null) {
    alert(message);
    if (additional !== null)
        console.log(additional);
}


function clearForm() {
    oldEmailField.value = "";
    oldPasswordField.value = "";
    newEmailField.value = "";
    newPasswordField.value = "";
}


function getCredentials() {
    let formData = new FormData();
    formData.append('action', 'show');
    let params = { method: "POST", body: formData };

    fetch('./api/credentials.php', params).then(res => res.text()).then((_result) => {
        let result;
        try {
            result = JSON.parse(_result);
        } catch (error) {
            alert("An erro occured");
            console.log(`ERROR fetch: ${error}`);
        }
        let statusCode = result.status;
        let message = result.msg;

        if (statusCode === 0) {
            let data = result.data;

            showUsernameField.value = data['username'];
            showPasswordField.value = data['password'];
            showTime.innerText = data['time'];
            
            setTimeout(() => {
                showUsernameField.value = "";
                showPasswordField.value = "";
                showTime.innerText = "";
            }, (1000 * 60 * 2));

        } else if (statusCode === 1) {
            showAlert('d', 'You are not logged in');
            window.location.replace("./login.html");
        } else if (statusCode === 2) {
            showAlert('w', "Data file not found", message);
        } else if (statusCode === 3) {
            showAlert('w', "Internal Server Error", message);
        } else if (statusCode === 4) {
            showAlert('w', "Can not decode data", message);
        }

        showButtton.blur();
    }).catch((error) => {
        alert("An erro occured");
        console.log(`ERROR fetch: ${error}`);
    });
}