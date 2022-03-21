
const validateEmailInput = (email) => {
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
};
const multipleEmailInput = document.querySelectorAll('.multiple_email_input');

multipleEmailInput.forEach((inputReceipient) => {
    let itemArray = inputReceipient.value.split(',');
    let emailItem = '';


    itemArray.forEach((arrItem) => {
        // console.log(arrItem.trim());
        emailItem += '<span class="item_email">' + arrItem.trim() + '<span class="delete tutor-icon-line-cross-line"></span></span>';
    })
    inputReceipient.insertAdjacentHTML('beforebegin', '<div class="receipient_input">' + emailItem + '<input type="email" placeholder="add receipient..."></div>');

    // inputReceipient.onchange

    setTimeout(() => {
        console.log(inputReceipient.previousElementSibling.querySelectorAll('.item_email'));

        inputReceipient.previousElementSibling.querySelectorAll('.item_email').forEach((item) => {
            item.querySelector('.delete').onclick = () => {
                console.log(item);
                item.remove();
            }
        })

        let inputField = inputReceipient.previousElementSibling.querySelector('input[type=email]');
        inputField.addEventListener('keyup', function (event) {

        });
        inputField.addEventListener('keydown', function (event) {
            const key = event.key; // const {key} = event; ES6+
            inputField.classList.remove('invalid');
            if (key === "Backspace") {
                if ('' === inputField.value) {
                    inputField.previousElementSibling.remove();
                }
            }
            if (key === "Tab" || event.keyCode === 188) {
                if (false === validateEmailInput(inputField.value)) {
                    tutor_toast('Invalid', 'Invalid email', 'warning');
                    event.preventDefault();
                    inputField.focus();
                    inputField.classList.add('invalid');
                    return false;
                } else {

                    // console.log(inputReceipient.value);
                    inputReceipient.value += ',' + inputField.value;
                    console.log(inputField.value);
                    inputField.insertAdjacentHTML('beforebegin', '<span class="item_email">' + inputField.value + '<span class="delete tutor-icon-line-cross-line"></span></span>');

                    inputField.style.borderColor = 'transparent';
                    inputField.value = '';
                    inputField.focus();

                    tutor_toast('Success', 'Valid email', 'success');
                }
            }
        });


    }, 10)


})