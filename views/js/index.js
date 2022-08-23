console.log("%cindex.js", "color:yellow;font-size:18px;");

// DOM Variables
let selectType = document.getElementById('type');
let titleFieldLabel = document.getElementById('tlabel');
let customTypeBox = document.getElementById('cTypeBox');
let titleField = document.getElementById('title');
let selectTime = document.getElementById('');
let addBtn = document.getElementById('addReminderBtn');
let daySelect = document.getElementById('daySelect');
let monthSelect = document.getElementById('monthSelect');
let customTypeField = document.getElementById('ctype');
let descriptionField = document.getElementById('description');
let timeField = document.getElementById('time');
let repeatCheckbox = document.getElementById('repeat');
let showAllRemindersBtn = document.getElementById('showAllBtn');
let submitBtnBox = document.getElementById('submitBtnBox');
let pageHeading = document.getElementById('pageHeading');
let showBox = document.getElementById('showBox');


// Data Variables
let isCustomTypeVisible = false;
let allReminders, numberOfReminders, currentEditingReminder, currentEditingId;


// Do work when the HTML is loaded
document.addEventListener('DOMContentLoaded', () => {
    infinityfreeSpecific();

    customTypeBox.style.display = 'none';
    addDays(31);
    // Check if the user is already logged in or not
    checkLogin();

});

// Click listner for Type of Days dropdown
selectType.addEventListener('change', typeChange);

// Add Reminder Button Clicked
addBtn.addEventListener('click', () => {
    console.log("Adding...");

    let typeOfDay = selectType.value;
    let customType = customTypeField.value.trim();
    let reminderDay = daySelect.value;
    let reminderMonth = monthSelect.value;
    let title = titleField.value.trim();
    let description = descriptionField.value.trim();
    let time = timeField.value.trim();
    let repeat = repeatCheckbox.checked;
    let isCustomType = (typeOfDay === "custom") ? true : false

    if (title === "") return;

    if (typeOfDay === "custom") {
        if (customType === "") {
            showalert('s', 'Please enter a Type of your day');
            return;
        }
        typeOfDay = customType;
    }

    let reminderHour = parseInt(time.substr(0, 2));
    let amOrPm = time.substr(5);
    console.log(`typeOfDay (${typeOfDay})`);

    let primaryFormData = {
        type: typeOfDay,
        isCustomType: isCustomType,
        title: title,
        description: description,
        hour: reminderHour,
        portionOfDay: amOrPm,
        day: reminderDay,
        month: reminderMonth,
        willRepeat: repeat
    }

    console.log("primary form data:");
    console.log(primaryFormData);

    let formData = new FormData();
    for (key in primaryFormData) {
        formData.append(key, primaryFormData[key])
    }

    const params = {
        method: 'POST',
        body: formData
    }

    fetch('/api/set_reminder.php', params).then(response => response.text()).then((result) => {
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
            showalert('s', "Reminder Added Successfully");
            showAllRemindersBtn.click();
            formReset();
        } else if (statusCode == 1) {
            showalert('s', "Authentication Error. Login again");
            document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
            setTimeout(() => {
                window.location.replace('/login.php');
            }, 3000);
        } else if (statusCode == 2) {
            showalert('s', "Invalid request.");
        } else if (statusCode == 3) {
            showalert('s', "Internal Server Error. Try again", message);
        } else if (statusCode == 4) {
            showalert('s', "Incorrect Value", message);
        }
    });
});


// Change dates whend the month changes
monthSelect.addEventListener('change', () => {
    let month = monthSelect.value;
    if (month === 'february') {
        addDays(28);
    } else if (month === 'january' || month === 'march' || month === 'may' || month === 'july' || month === 'august' || month === 'october' || month === 'december') {
        addDays(31);
    } else if (month === 'april' || month === 'june' || month === 'september' || month === 'november') {
        addDays(30);
    }
});


// Show Reminder button click
showAllRemindersBtn.addEventListener('click', () => {
    fetch('/api/get_all_reminders.php', { method: 'POST' }).then(response => response.text()).then((result) => {
        // console.log(result);
        let data;
        try {
            data = JSON.parse(result);
        } catch (error) {
            showalert("d", 'Internal Server Error. Try again');
            return;
        }

        let statusCode = data.status;
        let message = data.msg;

        if (statusCode == 0) {
            let allResults = data.data;
            let numOfResults = data.numOfResults;
            showAllReminders(allResults, numOfResults);
            allReminders = allResults;
            numberOfReminders = numOfResults;
        } else if (statusCode == 1) {
            showalert('d', "Authentication Error. Please login again");
        } else if (statusCode == 2) {
            showAllReminders(null);
        } else if (statusCode == 3) {
            showalert('w', "Internal Server Error. Try again", message);
        }
    });
});






// Functions
function typeChange() {
    let value = selectType.value;

    if (value === "custom") {
        titleFieldLabel.innerText = "Give a short title your Day 👀";
        customTypeBox.style.display = 'block';
    } else {
        customTypeBox.style.display = 'none';
    }

    if (value === 'birthday') {
        titleFieldLabel.innerText = "Whose Birthday is it 🎂"
    } else if (value === 'anniversary') {
        titleFieldLabel.innerText = "Name whose Anniversary 💑";
    } else if (value === 'trip') {
        titleFieldLabel.innerText = "Location to visit 🗺";
    } else if (value === 'party') {
        titleFieldLabel.innerText = "Where is the party 🥳";
    }
};

function addDays(numberOfDays) {
    daySelect.innerText = "";

    for (let i = 1; i <= numberOfDays; i++) {
        const o = document.createElement('option');
        o.innerText = i;
        o.value = i;
        if (i == 1)
            o.setAttribute('selected', true);
        daySelect.appendChild(o);
    }
}

function showalert(type, message, additional = null) {
    alert(message);

    if (additional !== null)
        console.log(`Alert: ${additional}`);
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

    if (haveCookie === false) {
        let body = document.getElementsByTagName('body')[0];
        let box = body.firstElementChild;
        console.log(box);

        box.innerHTML = "";

        box.innerHTML = `
        <h1 class="text-center text-decoration-underline">&nbsp; You are currently Logged Out &nbsp;</h1>
        <h3 class="text-center my-4">Login first to access your reminders</h3>
        <p class="text-center"><a href="/login.php">Click here to Login<a/></p>
        `;
    }
}

function formReset() {
    if (selectType.value === 'custom') {
        customTypeField.value = "";
        customTypeBox.style.display = "none";
    }
    selectType.value = "birthday";
    monthSelect.value = "january";
    addDays(31);
    titleField.value = "";
    titleFieldLabel.innerText = "Whose Birthday is it 🎂";
    descriptionField.value = "";
    timeField.value = "12:00am";
    repeatCheckbox.checked = true;
}

function showAllReminders(data, numOfResults = 0) {
    let div;

    // If no reminder is available
    if (data == null) {
        let heading = document.createElement('h3');
        heading.innerText = "You don't have any reminder. Create first to get your reminders details";
        heading.classList.add('text-center');
        heading.classList.add('mt-3');

        showBox.innerHTML = "";
        showBox.appendChild(heading);
        window.scrollTo(0, document.body.scrollHeight);

        return;
    }


    // If reminders are available
    showBox.innerHTML = "";
    for (let i = 0; i < numOfResults; i++) {
        let remindDateText;
        let element = data[i];

        div = document.createElement('div');
        div.classList.add('card');
        div.classList.add('my-2');

        let type = element.type;
        type = String(type[0]).toUpperCase().concat(type.slice(1));

        remindDateText = (element.wr == 'true') ? "Every year, " : "Upcoming ";
        let month = element.month;
        month = String(month[0]).toUpperCase().concat(month.slice(1));

        remindDateText += `${element.day} ${month}`;

        let a = '', b = '', c = '', d = '';
        if (element.active === '1') {
            a = " disabled";
            b = 'success';
            d = "Activate";
            c = ' onclick="js:activeReminder(this)"';

        } else {
            c = ' onclick="js:deactiveReminder(this)"';
            b = "danger";
            d = "Deactivate";
        }


        let editButton = `<button class="btn btn-sm btn-primary" onclick="js:editReminder(this)"${a}>Edit</button>`;
        let activeButton = `<button class="btn btn-sm btn-${b}"${c}>${d}</button>`;

        let status = "Active", statusColor = "success";
        if (a === ' disabled') {
            editButton = '';
            status = "Inactive";
            statusColor = "danger";
        }

        // write the content of the card    
        div.innerHTML = `<div class="card-body py-0"><div class="d-flex flex-row justify-content-between align-items-center"><h6 class="card-subtitle text-muted">Id: ${element.id}</h6><div class="p-0 m-0">${editButton}&nbsp;${activeButton}</div></div><h5 class="card-title">${type} - ${element.title}</h5><p class="card-text mb-1 fs-6">${element.description}</p><div class="d-flex flex-wrap justify-content-start align-items-center "><p class='fs-6 mb-0'><u>Reminds on</u> : <span class="fs-6 fw-bold">${remindDateText}</span></p><div class="mx-5"></div><p class='fs-6 mb-0'><u>At</u> : <span class="fs-6 fw-bold">${element.hour} ${element.apm}</span></p></div><div class="d-flex flex-row justify-content-between align-items-center my-1"><span class="fs-6">Status: <span class="fw-bold rounded text-white bg-${statusColor}">&nbsp;${status}&nbsp;</span></span><p class="fs-6 font-monospace mb-0">Created/Last-edited at: <span class="font-cursive">${element.created_at}</span></p></div></div>`;


        showBox.appendChild(div);
    }

    window.scrollTo(0, showBox.scrollHeight + 80);
    return true;
}

function editReminder(a) {
    let id = get_id_of_target_element(a);
    console.log(`id = ${id}`);

    let all = allReminders;
    all.forEach(element => {
        if (element.id == id) {
            console.log(element);

            let isCustomType = false;
            if ((element.type !== 'birthday') && (element.type !== 'anniversary') && (element.type !== 'trip') && (element.type !== 'party')) {
                isCustomType = true;
            }

            // set type fields values
            if (isCustomType === true) {
                selectType.value = 'custom';
                customTypeField.value = element.type;
                typeChange();
            } else {
                selectType.value = element.type;
            }

            // Set title field value
            titleField.value = element.title;

            // Set description field value
            if (element.description !== "")
                descriptionField.value = element.description;

            // Set date field value
            monthSelect.value = element.month;
            daySelect.value = element.day;

            // Set time field value
            let time = element.hour;
            if (time.length === 1)
                time = `0${time}`;
            time = `${time}:00${element.apm}`;
            timeField.value = time;

            // Set repeat filed value
            if (element.wr === 'false')
                repeatCheckbox.checked = false;


            pageHeading.innerText = "Edit your Reminder";
            submitBtnBox.innerHTML = `
            <button id="addReminderBtn" class=" btn btn-lg btn-primary mt-3 me-3" onclick="confirmEdit('${id}')">Edit Reminder</button>
            <button id="addReminderBtn" class=" btn btn-lg btn-danger mt-3" onclick="resetEdit()">Cancel Edit</button>
            `;
            currentEditingId = element.id;
            currentEditingReminder = element;

            window.scrollTo(document.body.scrollHeight, 0);
        }
    });
}

function deactiveReminder(a) {
    if (confirm("Do you really want to Deactivate this reminder ? ") === false) return;

    let id = get_id_of_target_element(a);
    console.log(`Id = ${id}`);

    let primaryFormData = { action: 'deactive', id };
    let formData = new FormData();

    for (key in primaryFormData) {
        formData.append(key, primaryFormData[key]);
    }
    let params = { method: 'POST', body: formData };

    fetch('/api/reminder_action.php', params).then(res => res.text()).then((result) => {
        let data;
        try {
            data = JSON.parse(result);
        } catch (error) {
            console.log(`ERROR: ${error}`);
            return;
        }

        let statusCode = data.status;
        let message = data.msg;

        console.table(data);
        if (statusCode === 0) {
            showalert('s', 'This reminder is now deactivated');
            showAllRemindersBtn.click();
        } else if (statusCode === 1) {
            showalert('d', 'Authentication error. Please login again');
            window.location.replace('/login.php');
        } else if (statusCode === 2) {
            showalert('d', 'This reminder is already deactivated');
        } else if (statusCode === 3) {
            showalert('w', 'Internal Server Error', message);
        }
    }).catch((error) => {
        console.warn(`ERROR: ${error}`);
    });
}

function activeReminder(a) {
    let id = get_id_of_target_element(a);
    console.log(`Id = ${id}`);

    let primaryFormData = { action: 'active', id };
    let formData = new FormData();

    for (key in primaryFormData) {
        formData.append(key, primaryFormData[key]);
    }
    let params = { method: 'POST', body: formData };

    fetch('/api/reminder_action.php', params).then(res => res.text()).then((result) => {
        let data;
        try {
            data = JSON.parse(result);
        } catch (error) {
            console.log(`ERROR: ${error}`);
            return;
        }

        let statusCode = data.status;
        let message = data.msg;

        console.table(data);
        if (statusCode === 0) {
            showalert('s', 'This reminder is now re-activated');
            showAllRemindersBtn.click();
        } else if (statusCode === 1) {
            showalert('d', 'Authentication error. Please login again');
            window.location.assign('/login.php');
        } else if (statusCode === 2) {
            showalert('d', 'This reminder is already active');
        } else if (statusCode === 3) {
            showalert('w', 'Internal Server Error', message);
        }
    }).catch((error) => {
        console.warn(`ERROR: ${error}`);
    });
}

function get_id_of_target_element(a) {
    let parent = a.parentNode;
    parent = parent.parentNode;
    let child = parent.firstElementChild;
    let id = child.innerText;
    id = id.trim();
    id = id.replace("Id: ", "");
    return id;
}

function confirmEdit(reminderId) {
    console.log("Editing...");

    let typeOfDay = selectType.value;
    let customType = customTypeField.value.trim();
    let reminderDay = daySelect.value;
    let reminderMonth = monthSelect.value;
    let title = titleField.value.trim();
    let description = descriptionField.value.trim();
    let time = timeField.value.trim();
    let repeat = `${repeatCheckbox.checked}`;
    let isCustomType = (typeOfDay === "custom") ? true : false;
    let reminderHour = parseInt(time.substr(0, 2));
    let amOrPm = time.substr(5);

    if (title === "") {
        showalert("d", "Please enter a title to save changes");
        return;
    }

    if (typeOfDay === "custom") {
        if (customType === "") {
            showalert('s', 'Please enter a Type of your day');
            return;
        }
        typeOfDay = customType;
    }

    let primaryFormData = {
        type: typeOfDay,
        isCustomType: isCustomType,
        title: title,
        description: description,
        hour: reminderHour,
        portionOfDay: amOrPm,
        day: reminderDay,
        month: reminderMonth,
        willRepeat: repeat
    }

    if (primaryFormData.type === currentEditingReminder.type) {
        delete primaryFormData.type;
        delete primaryFormData.isCustomType;
    }

    if (primaryFormData.title === currentEditingReminder.title)
        delete primaryFormData.title;

    if (primaryFormData.description === currentEditingReminder.description)
        delete primaryFormData.description;

    if ((primaryFormData.hour == currentEditingReminder.hour) && (primaryFormData.portionOfDay == currentEditingReminder.apm)) {
        delete primaryFormData.hour;
        delete primaryFormData.portionOfDay;
    }


    if (primaryFormData.willRepeat == currentEditingReminder.wr) {
        delete primaryFormData.willRepeat;
    }

    if ((primaryFormData.day === currentEditingReminder.day) && (primaryFormData.month === currentEditingReminder.month)) {
        delete primaryFormData.day;
        delete primaryFormData.month;
    }


    // Check if nothing is changed
    let size = Object.keys(primaryFormData).length;
    if (size === 0) {
        resetEdit();
        alert("Nothing is changed");
        return;
    }

    primaryFormData['id'] = currentEditingId;
    primaryFormData['action'] = 'edit';

    let formData = new FormData();
    for (key in primaryFormData) {
        formData.append(key, primaryFormData[key])
    }

    const params = {
        method: 'POST',
        body: formData
    }

    fetch('/api/reminder_action.php', params).then(res => res.text()).then((result) => {
        let data;
        try {
            data = JSON.parse(result);
        } catch (error) {
            console.error(`JSON Parse Error: ${error}`);
            return;
        }
        console.log(data);

        let statusCode = data.status;
        let message = data.msg;

        if (statusCode === 0) {
            resetEdit();
            showalert('d', "Your reminder is Updated successfully");
            showAllRemindersBtn.click();
        } else if (statusCode === 1) {
            showalert('d', "Authentication Error. Please login again");
            window.location.assign("/login.php");
        } else if (statusCode === 2) {
            showalert('d', "No changes found in server");
            resetEdit();
        } else if (statusCode === 3) {
            showalert('w', "Internal Server Error. Try again", message);
            window.location.reload();
        } else if (statusCode === 4) {
            showalert('w', "Incorrect value given", message);
        } else if (statusCode === 5) {
            showalert('d', "You can not edit this reminder", message);
            resetEdit();
        }

    });
}

function resetEdit() {
    console.log(`Resetting edit...`);
    submitBtnBox.innerHTML = `<button id="addReminderBtn" class=" btn btn-lg btn-primary mt-3">Set Reminder</button>`;
    formReset();
    pageHeading.innerText = "Schedule your Reminder for a Special Day";
}

function infinityfreeSpecific() {
    let url_string = window.location.href;
    let url = new URL(url_string);

    if (url_string.includes("?i=1")) {
        window.location.replace(`${url.origin}${url.pathname}`);
    }
}