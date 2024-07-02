// Alignment field functions 
(function tutorAlignmentField() {
    const alignmentInputs = document.querySelectorAll('.tutor-form-alignment');
    alignmentInputs.forEach(input => {
        const formInput = input.querySelector('input');
        const inputButtons = input.querySelectorAll('button');
        inputButtons.forEach(button => {
            if (button.dataset.position === formInput.value) {
                button.classList.remove('tutor-btn-secondary');
                button.classList.add('tutor-btn-primary');
            }

            button.addEventListener('click', function(e) {
                const position = button.dataset.position;
                formInput.value = position;
                formInput.dispatchEvent(new Event('input'));

                inputButtons.forEach(btn => btn.classList.remove('tutor-btn-primary'));
                inputButtons.forEach(btn => btn.classList.add('tutor-btn-secondary'));
                button.classList.remove('tutor-btn-secondary');
                button.classList.add('tutor-btn-primary');
            });
        });
    });
})();
