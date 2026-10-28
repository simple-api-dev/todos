//*************************************** */
//Requests a key by submitting an email
//*************************************** */
function register(api_url)
{
    try{
        let email = document.querySelector("#email").value;

        var mailformat = /^[a-zA-Z0-9.!#$%&'+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:.[a-zA-Z0-9-]+)$/;
        if(!email.match(mailformat))
        {   
            showToast("Invalid email address!", "error");
            return false;
        }

        let cap = grecaptcha.getResponse();

        //captcha is empty if not done
        if (cap.length < 1) 
        {
            showToast("Please fill in the captcha!", "error");
            return;
        }
        
        //hide the contact form
        let form = document.querySelector("#contact_form");
        form.style.display = "none";
        let responseText = document.createTextNode("Requesting key...");
        form.parentElement.appendChild(responseText);

        //send Ajax request
        fetch(api_url + '/api/keys/register', {
            method: 'POST',
            body: JSON.stringify({
                'email' : email,
                'product_url': "https://todos.simpleapi.dev",
                'captcha': cap
            }),
            headers: {
                "Content-type": "application/json",
            }
        })
        .then(response =>
            //retrieve json while keeping status
            response.json().then(data => ({
                data: data,
                status: response.status
            })
        )   
        //gets the response
        .then(response => {
            //good response
            if (response.status == 200)
            {
                //display success message
                responseText.textContent = response.data.message;
            }    
            else
            {
                showToast(response.data.message, "error");

                //reshow form
                form.style.display = "grid";
                responseText.remove();
                grecaptcha.reset();
            }            
        })
        //catches errors
        .catch(error =>
        {
            showToast(error, "error");

            //reshow form
            form.style.display = "grid";
            responseText.remove();
            grecaptcha.reset();
        }));
    }
    catch(e)
    {
        showToast(error, "error");

        //reshow form
        form.style.display = "grid";
        responseText.remove();
        grecaptcha.reset();

        console.log(e);
    }
    finally
    {
        //don't actually submit form
        return false;
    }
}

//*************************************** */
//Requests a resend of user's api key
//*************************************** */
function forgot(api_url)
{
    try{
        let email = document.querySelector("#email").value;

        var mailformat = /^[a-zA-Z0-9.!#$%&'+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:.[a-zA-Z0-9-]+)$/;
        if(!email.match(mailformat))
        {   
            showToast("Invalid email address!", "error");
            return false;
        }

        let cap = grecaptcha.getResponse();

        //captcha is empty if not done
        if (cap.length < 1) 
        {
            showToast("Please fill in the captcha!", "error");
            return;
        }
        
        //hide the contact form
        let form = document.querySelector("#contact_form");
        form.style.display = "none";
        let responseText = document.createTextNode("Requesting key...");
        form.parentElement.appendChild(responseText);

        //send Ajax request
        fetch(api_url + '/api/keys/forgot', {
            method: 'POST',
            body: JSON.stringify({
                'email' : email,
                'captcha': cap
            }),
            headers: {
                "Content-type": "application/json",
            }
        })
        .then(response =>
            //retrieve json while keeping status
            response.json().then(data => ({
                data: data,
                status: response.status
            })
        )   
        //gets the response
        .then(response => {
            //good response
            if (response.status == 200)
            {
                //display success message
                responseText.textContent = response.data.message;
            }    
            else
            {
                showToast(response.data.message, "error");

                //reshow form
                form.style.display = "grid";
                responseText.remove();
                grecaptcha.reset();
            }            
        })
        //catches errors
        .catch(error =>
        {
            showToast(error, "error");

            //reshow form
            form.style.display = "grid";
            responseText.remove();
            grecaptcha.reset();
        }));
    }
    catch(e)
    {
        showToast(error, "error");

        //reshow form
        form.style.display = "grid";
        responseText.remove();
        grecaptcha.reset();

        console.log(e);
    }
    finally
    {
        //don't actually submit form
        return false;
    }
}

//*************************************** */
//Requests account deletion
//*************************************** */
function deregister(api_url)
{
    try{
        let email = document.querySelector("#email").value;

        var mailformat = /^[a-zA-Z0-9.!#$%&'+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:.[a-zA-Z0-9-]+)$/;
        if(!email.match(mailformat))
        {   
            showToast("Invalid email address!", "error");
            return false;
        }

        let code = document.querySelector('#code').value;
        let cap = grecaptcha.getResponse();

        //captcha is empty if not done
        if (cap.length < 1) 
        {
            showToast("Please fill in the captcha!", "error");
            return;
        }
        
        //hide the contact form
        let form = document.querySelector("#contact_form");
        form.style.display = "none";
        let responseText = document.createTextNode("Requesting account deletion...");
        form.parentElement.appendChild(responseText);

        //build body here to allow code vs no code
        let ajaxBody = {};
        ajaxBody['email'] = email;
        if (code) ajaxBody['code'] = code;
        ajaxBody['captcha'] = cap;

        //send Ajax request
        fetch(api_url + '/api/keys/deregister', {
            method: 'POST',
            body: JSON.stringify(ajaxBody),
            headers: {
                "Content-type": "application/json",
            }
        })
        .then(response =>
            //retrieve json while keeping status
            response.json().then(data => ({
                data: data,
                status: response.status
            })
        )   
        //gets the response
        .then(response => {
            //good response
            if (response.status == 200)
            {
                //display success message
                responseText.textContent = response.data.message;
            }   
            //info response regarding deletion code 
            else if (response.status == 201)
            {
                showToast(response.data.message, "info");

                //reshow form
                form.style.display = "grid";
                responseText.remove();
                grecaptcha.reset();
            }   
            else
            {
                showToast(response.data.message, "error");

                //reshow form
                form.style.display = "grid";
                responseText.remove();
                grecaptcha.reset();
            }            
        })
        //catches errors
        .catch(error =>
        {
            showToast(error, "error");

            //reshow form
            form.style.display = "grid";
            responseText.remove();
            grecaptcha.reset();
        }));
    }
    catch(e)
    {
        showToast(error, "error");

        //reshow form
        form.style.display = "grid";
        responseText.remove();
        grecaptcha.reset();

        console.log(e);
    }
    finally
    {
        //don't actually submit form
        return false;
    }
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