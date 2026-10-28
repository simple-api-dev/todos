//*************************************** */
//Getting a key (and deleting an account) is two steps:
//1. email address + captcha -> a 6 digit code is emailed
//2. the code -> the key is shown on this page
//*************************************** */
var MAIL_FORMAT = /^[a-zA-Z0-9.!#$%&'+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)+$/;

function keysApi(api_url, path, body)
{
    return fetch(api_url + '/api/keys/' + path, {
        method: 'POST',
        body: JSON.stringify(body),
        headers: { "Content-type": "application/json" }
    }).then(response => response.json().then(data => ({ data: data, status: response.status })));
}

function setStep(step)
{
    ['step1', 'step2', 'step3'].forEach(function(id)
    {
        let el = document.querySelector('#' + id);
        if (el) { el.style.display = (id == step) ? (id == 'step3' ? 'block' : 'grid') : 'none'; }
    });
}

//step 1: ask for a code. purpose is 'request' (get a key) or 'delete-request' (delete the account)
function requestCode(api_url, purpose)
{
    let email = document.querySelector("#email").value.trim();
    if (!email.match(MAIL_FORMAT)) { showToast("Invalid email address!", "error"); return false; }

    let cap = grecaptcha.getResponse();
    if (cap.length < 1) { showToast("Please fill in the captcha!", "error"); return false; }

    let button = document.querySelector("#Submit1");
    button.disabled = true;
    keysApi(api_url, purpose, { 'email': email, 'product_url': window.PRODUCT_URL, 'captcha': cap })
        .then(function(response)
        {
            button.disabled = false;
            if (response.status == 200)
            {
                document.querySelector("#sent_to").textContent = email;
                setStep('step2');
                document.querySelector("#code").focus();
                showToast(response.data.message, "success");
            }
            else
            {
                showToast(response.data.message, "error");
                grecaptcha.reset();
            }
        })
        .catch(function(error) { button.disabled = false; showToast("Something went wrong, please try again.", "error"); grecaptcha.reset(); });

    return false;
}

//step 2: enter the code. purpose is 'verify' (shows the key) or 'delete' (deletes the account)
function submitCode(api_url, purpose)
{
    let email = document.querySelector("#email").value.trim();
    let code = document.querySelector("#code").value.trim();
    if (!code.match(/^[0-9]{6}$/)) { showToast("The code is 6 digits.", "error"); return false; }

    let button = document.querySelector("#Submit2");
    button.disabled = true;
    keysApi(api_url, purpose, { 'email': email, 'product_url': window.PRODUCT_URL, 'code': code })
        .then(function(response)
        {
            button.disabled = false;
            if (response.status == 200)
            {
                if (purpose == 'verify')
                {
                    document.querySelector("#apikey").textContent = response.data.apikey;
                    document.querySelector("#key_note").textContent = response.data.created ? "Your key is ready." : "You already had a key, here it is again.";
                    setStep('step3');
                }
                else
                {
                    document.querySelector("#done").textContent = response.data.message;
                    setStep('step3');
                }
            }
            else
            {
                showToast(response.data.message, "error");
            }
        })
        .catch(function(error) { button.disabled = false; showToast("Something went wrong, please try again.", "error"); });

    return false;
}

function startOver()
{
    document.querySelector("#code").value = "";
    grecaptcha.reset();
    setStep('step1');
    return false;
}

function copyKey()
{
    let key = document.querySelector("#apikey").textContent;
    if (navigator.clipboard) { navigator.clipboard.writeText(key).then(function() { showToast("Copied!", "success"); }); }
    return false;
}

//*************************************** */
//Displays a toast
//*************************************** */
function showToast(message, type)
{
    //set up toast
    let toast = document.querySelector('.toast').cloneNode(true);
    toast.querySelector('.toast-message').innerHTML = message;
    toast.classList.remove('hidden');

    switch(type)
    {
        case 'warning':
            toast.classList.add('bg-yellow-500');
            break;
        case 'error':
            toast.classList.add('bg-red-500');
            break;
        case 'info':
            toast.classList.add('bg-blue-500');
            break;
        case 'success':
            toast.classList.add('bg-green-500');
            break;
    }

    //show toast
    let toastsDiv = document.querySelector('#toasts');
    toastsDiv.appendChild(toast);

    //set to delete automatically
    setTimeout(function()
    {
        fadeDelete(toast);
    }, 5000);
}

//*************************************** */
//Fades out and deletes the element
//*************************************** */
function fadeDelete(elem)
{
    elem.classList.add('fade-out');

    setTimeout(function()
    {
        elem.remove();
    }, 1200);
}