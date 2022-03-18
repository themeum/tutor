
const multipleEmailInput = document.querySelectorAll('.multiple_email_input');
multipleEmailInput.forEach((item) => {
    let itemArray = item.value.split(',');
    let emailItem = '';
    itemArray.forEach((arrItem) => {
        // console.log(arrItem.trim());
        emailItem += '<span class="item_email">' + arrItem.trim() + '<span class="delete tutor-icon-line-cross-line"></span></span>';
    })
    item.insertAdjacentHTML('beforebegin', '<div class="receipient_input">' + emailItem + '<input type="email"></div>');

    setTimeout(() => {
        console.log(item.previousElementSibling.querySelectorAll('.item_email'));
        item.previousElementSibling.querySelectorAll('.item_email').forEach((item) => {
            item.querySelector('.delete').onclick = () => {
                console.log(item);
                item.remove();
            }
        })

        let inputField = item.previousElementSibling.querySelector('input[type=email]');
        inputField.addEventListener('keydown', function (event) {
            const key = event.key; // const {key} = event; ES6+
            if (key === "Backspace") {
                if ('' === inputField.value) {
                    inputField.previousElementSibling.remove();
                }
            }
        });

    }, 10)


})