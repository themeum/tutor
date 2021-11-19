import './tutor-date-picker';
import './tutor-date-range-picker';
import '../lib/common';
import './qna';
// import './general';
// Select your input element.
var numbers = document.querySelectorAll('input[type="number"]');

// Listen for input event on numInput.
numbers.forEach((number) => {
    number.value = number.value <= 0 ? 0 : number.value;
    number.onkeydown = function (e) {
        if (e.keyCode === 109 || e.keyCode === 189) {
            return false
        }
        number.value = number.value <= 0 ? 0 : number.value;
    }
})