// DOM Variables
let nameField = document.getElementById('name');
let detailsField = document.getElementById('details');
let refCodeField = document.getElementById("refCode");
let addBtn = document.getElementById('addBtn');
let showAllBtn = document.getElementById('showAllBtn');
let showBox = document.getElementById('showBox');
let buttonBox = document.getElementById('btnBox');
let label = document.getElementById('refLabel');

// Adding new refer code
addBtn.addEventListener("click", addNewReferrer);

// showing all refer code details
showAllBtn.addEventListener("click", showAllCodes);




function addNewReferrer() {
    let name = nameField.value.trim();
    let description = detailsField.value.trim();
    let refCode = refCodeField.value.trim();

    if (name === "") return;
    if (description === "") return;
    if (refCode === "") return;

    let primaryFormData = { name, description, refCode, action: "add" };
    let formData = new FormData();
    for (key in primaryFormData)
        formData.append(key, primaryFormData[key]);
    let params = { method: "POST", body: formData };

    alert("Processing");

    fetch("./api/refer_action.php", params).then(res => res.text()).then((_result) => {
        let result;
        try {
            result = JSON.parse(_result);
        } catch (error) {
            alert("An error occured");
            console.log(`ERROR json: ${error}`);
        }

        let statusCode = result.status;
        let message = result.msg;

        if (statusCode === 0) {
            showAlert('s', "New refer code added successfully");
            clearForm();
        } else if (statusCode === 1) {
            showAlert('d', "You are not logged in");
            window.location.replace('./login.html');
        } else if (statusCode === 2) {
            showAlert("d", "This refer code already exists. Try anoter one");
        } else if (statusCode === 3) {
            showAlert('w', "Internal Server Error", message);
        }
    }).catch((error) => {
        alert("There is an error");
        console.log(`ERROR: ${error}`);
    });
}


function showAllCodes() {
    let primaryFormData = { key: "aStdYf234y*3rdf52", action: "show" };
    let formData = new FormData();
    for (key in primaryFormData)
        formData.append(key, primaryFormData[key]);
    let params = { method: "POST", body: formData };
    fetch("./api/refer_action.php", params).then(res => res.text()).then((_result) => {
        let result;
        try {
            result = JSON.parse(_result);
        } catch (error) {
            console.log(error);
            alert("An error occured");
            return;
        }
        console.log(result);
        let statusCode = result.status;
        let message = result.msg;

        if (statusCode === 0) {
            displayResults(result.data);
        } else if (statusCode === 1) {
            showAlert('d', "You are not logged in");
            // window.location.replace("./login.html");
        } else if (statusCode === 2) {
            showAlert('d', "Incorrect Request", message);
        } else if (statusCode === 3) {
            showAlert('d', "Internal Server Error. Please try again", message);
        } else if (statusCode === 4) {
            displayResults(null);
        }
    });
}


function displayResults(data) {
    if (data === null) {
        showBox.innerHTML = "<h2 class='text-center my-5'>There is no refer code. Create one first</h2>";
        window.scrollTo(0, document.body.scrollHeight);
        return;
    }

    let table = document.createElement('table');
    let thead = document.createElement('thead');
    let tbody = document.createElement('tbody');
    let tr = document.createElement('tr');

    thead.innerHTML = `
    <tr><th>Id</th><th>Refer Code</th><th>Name/Label</th><th>Details</th><th>Status</th><th>Created At</th><th>Action</th></tr>
    `;
    table.classList.add('text-center');

    data.forEach(element => {
        let isDone = element.done;
        let doneColor = "danger";
        let doneText = "Deleted";
        let deleteDisable = "disabled";

        if (isDone == '0') {
            doneColor = 'warning';
            doneText = 'Used';
        } else if (isDone == '1') {
            doneColor = 'success';
            doneText = 'Not Used';
            deleteDisable = "";
        }

        tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${element.id}</td>
            <td title="click the code to copy"><span onclick="copy('${element.ref_code}')" >${element.ref_code}</span></td>
            <td>${element.name}</td>
            <td>${element.details}</td>
            <td><span class="m-1 p-0 pb-1 text-white bg-${doneColor} rounded">&nbsp; ${doneText} &nbsp;</span></td>
            <td>${element.created_at}</td>
            <td>
            <button type="button" onclick="deleteCode('${element.id}')" title="Delete refer code" class="btn btn-outline-danger px-1 pt-0 pb-1" ${deleteDisable}>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z"></path></svg>
            </button>
            &nbsp;
            <button type="button" onclick="editCode(this)" title="Edit refer code" class="btn btn-outline-primary px-1 pt-0 pb-1" ${deleteDisable}>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/></svg>
            </button>
            </td>
            `;

        tbody.appendChild(tr);
    });

    table.appendChild(thead);
    table.appendChild(tbody);
    table.classList.add('table');

    showBox.innerHTML = "";
    showBox.appendChild(table);

    window.scrollTo(0, document.body.scrollHeight);
}


function showAlert(type, message, additional = null) {
    alert(message);
    if (additional !== null)
        console.log("ERROR: " + additional);
}


function copy(text) {
    let input = document.createElement('input');
    input.value = text;

    input.select();
    input.setSelectionRange(0, 99999);

    input.focus();
    navigator.clipboard.writeText(input.value).then(() => {
        alert(`Code copied  =  ${input.value}`);
        input.remove();
    }).catch((error) => {
        alert("Please copy it manually");
        console.log(`Copy Error: ${error}`);
        input.remove();
    })
    return true;
}


function deleteCode(id) {
    if (confirm("Do you really want to delete this refer code ?") === false) return;
    else {
        if (prompt(`Write ' id:${id} '  to confirm`, "") !== `id:${id}`) return;
    }

    let primaryFormData = { action: "delete", id };
    console.log(primaryFormData);
    let formData = new FormData();
    for (key in primaryFormData)
        formData.append(key, primaryFormData[key]);
    let params = { method: 'POST', body: formData };

    fetch("./api/refer_action.php", params).then(res => res.text()).then((_result) => {
        let result;
        try {
            result = JSON.parse(_result);
        } catch (error) {
            alert("An error occured");
            console.log(`ERROR: ${error}`);
        }

        let statusCode = result.status;
        let message = result.msg;

        if (statusCode === 0) {
            showAlert('s', "This refer code deleted successfully");
            showAllCodes();
        } else if (statusCode === 1) {
            showAlert('d', "You are not logged in");
            window.location.assign('./login.html');
        } else if (statusCode === 2) {
            showAlert('d', "This refer code is already deleted");
        } else if (statusCode === 3) {
            showAlert("d", "Internal Server Error. Try again", message);
        } else if (statusCode === 4) {
            showAlert("d", "This code is already Used by user. No meaning to delete now");
        }

    }).catch((error) => {
        alert("An error occured");
        console.log(`ERROR: ${error}`);
    })
}


function editCode(_t) {
    let id, name, details, refCode;
    let parent = _t.parentElement;
    parent = parent.parentElement;
    let child = parent.childNodes;

    id = child[1].innerHTML;
    name = child[5].innerHTML;
    details = child[7].innerHTML;
    refCode = child[3].firstChild.innerHTML;
    console.log(`id:(${id}), name:(${name}), details:(${details}), refCode:(${refCode})`);

    nameField.value = name;
    detailsField.value = details;
    refCodeField.value = refCode;

    label.innerText = `Edit Refer Code (${id}) :`;

    buttonBox.innerHTML = `
        <button class="btn btn-lg btn-primary" onclick="confirmEdit('${id}')">Edit Fields</button>
        &nbsp;
        <button class="btn btn-lg btn-danger" onclick="resetEdit()">Cancet Edit</button>
    `;

    window.scrollTo(0, 0);
}


function confirmEdit(id) {
    let name = nameField.value.trim();
    let description = detailsField.value.trim();
    let refCode = refCodeField.value.trim();

    if (name === "") return;
    if (description === "") return;
    if (refCode === "") return;

    let primaryFormData = { id, name, description, refCode, action: "edit" };
    let formData = new FormData();
    for (key in primaryFormData)
        formData.append(key, primaryFormData[key]);
    let params = { method: "POST", body: formData };

    alert("Processing");

    fetch("./api/refer_action.php", params).then(res => res.text()).then((_result) => {
        let result;
        try {
            result = JSON.parse(_result);
        } catch (error) {
            alert("An error occured");
            console.log(`ERROR json: ${error}`);
        }

        let statusCode = result.status;
        let message = result.msg;

        if (statusCode === 0) {
            showAlert('s', "Refer Code is successfully Updated");
            resetEdit();
            showAllCodes();
        } else if (statusCode === 1) {
            showAlert('d', "You are not logged in");
            window.location.replace('./login.html');
        } else if (statusCode === 2) {
            showAlert("d", "This refer code is not editable");
        } else if (statusCode === 3) {
            showAlert('w', "Internal Server Error", message);
        }
    }).catch((error) => {
        alert("There is an error");
        console.log(`ERROR: ${error}`);
    });
}


function resetEdit() {
    buttonBox.innerHTML = `<button class="btn btn-lg btn-primary" id="addBtn">Create Refer Code</button>`;
    label.innerText = "Create a new Referrer Code :";
    clearForm();
}


function clearForm() {
    nameField.value = "";
    detailsField.value = "";
    refCodeField.value = "";
}