
// document.addEventListener('DOMContentLoaded', () => {
//     // checkLogin();
//     console.log("Loaded DOM");
// });





/*     Functions   */
async function sayhello() {
    let primaryFormData = { hello: "world" };
    let formData = new FormData();
    for (key in primaryFormData)
        formData.append(key, primaryFormData[key]);
    let parameters = { method: 'POST', body: formData };
    let result = await fetch('./api/say_hello.php', parameters);
    result = await result.text();
    console.log(`Returned: [${result}]`);

    if (result !== "Hello World") {
        console.log("Not logged in");
        window.location.replace('./admin/login.html');
    }
}

// Checks if User is logged in
function checkLogin() {
    let allCookies = document.cookie;
    let haveCookie = false;
    let cookieName = "admin";

    if (allCookies.includes(cookieName)) {
        let a = allCookies.indexOf(cookieName);

        a = cookieName.length + 1;

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
        console.log("Not logged in");
        window.location.replace('http://localhost/admin/login.html');
    } else {
        console.log("Admin logged in");
    }
}