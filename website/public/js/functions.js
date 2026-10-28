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