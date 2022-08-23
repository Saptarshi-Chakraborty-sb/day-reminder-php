let showBox = document.getElementById('showBox');
let showButton = document.getElementById('showAllBtn');


showButton.addEventListener('click', fetchAllVariables);


function fetchAllVariables() {
    let formData = new FormData();
    formData.append('action', "show");
    let params = { method: 'POST', body: formData };

    fetch("./api/globals.php", params).then(res => res.text()).then((_result) => {
        let result;
        try {
            result = JSON.parse(_result);
        } catch (error) {
            alert("An error occured");
            console.log(`ERROR json: ${error}`);
            return;
        }

        let statusCode = result.status;
        let message = result.msg;

        if (statusCode === 0) {
            displayVariables(result.data);
        } else if (statusCode === 1) {
            alert("You are not logged in");
        } else if (statusCode === 2) {
            displayVariables(null);
        }
    }).catch((error) => {
        alert("An error occured");
        console.log(`ERROR fetch: ${error}`);
    });
}


function displayVariables(data) {
    if (data === null) {
        showBox.innerHTML = "<h2 class='text-center my-5'>There is no global variable</h2>";
        window.scrollTo(0, document.body.scrollHeight);
        return;
    }

    let table = document.createElement('table');
    let thead = document.createElement('thead');
    let tbody = document.createElement('tbody');
    let tr = document.createElement('tr');

    thead.innerHTML = `
    <tr><th>No.</th><th>Variable Name</th><th>Variable Value</th><th>Type</th></tr>
    `;
    table.classList.add('text-center');

    let i = 1;
    for (key in data) {

        tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${i++}</td>
            <td>${key}</td>
            <td>${data[key]}</td>
            <td>${typeof (data[key])}</td>
            `;

        tbody.appendChild(tr);
    }

    table.appendChild(thead);
    table.appendChild(tbody);
    table.classList.add('table');

    showBox.innerHTML = "";
    showBox.appendChild(table);
}


function copy(text) {
    let input = document.createElement('input');
    input.value = text;

    input.select();
    input.setSelectionRange(0, 99999);

    input.focus();
    navigator.clipboard.writeText(input.value).then(() => {
        // alert(`Value copied  =  ${input.value}`);
        input.remove();
    }).catch((error) => {
        alert("Please copy it manually");
        console.log(`Copy Error: ${error}`);
        input.remove();
    })
    return true;
}