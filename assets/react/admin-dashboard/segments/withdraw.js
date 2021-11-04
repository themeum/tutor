document.addEventListener("DOMContentLoaded", function(){
   const { __, _x, _n, _nx } = wp.i18n; 
   // Approve and Reject button
   const approveButton = document.querySelectorAll('.tutor-admin-open-withdraw-approve-modal');
   const rejectButton = document.querySelectorAll('.tutor-admin-open-withdraw-reject-modal');
   let withdrawId;

   // Onclick button dynamically create content
   if (approveButton) {
       for (let button of approveButton) {
            button.onclick = (e) => {
                withdrawId = e.currentTarget.dataset.id;
                const amount = e.currentTarget.dataset.amount;
                const accountName = e.currentTarget.dataset.name;
                const content = document.getElementById('tutor-admin-withdraw-approve-content');
                content.innerHTML = `${__( 'You are approving '+ `<strong>${accountName}</strong>` + ' withdrawal request for '+ `<strong>${amount}</strong>` +'. Are you sure you want to approve?', 'tutor')}`;
            }
       }

   }
    // Onclick button dynamically create content
   if (rejectButton) {
        for (let button of rejectButton) {
            button.onclick = (e) => {
                withdrawId = e.currentTarget.dataset.id;
                const amount = e.currentTarget.dataset.amount;
                const accountName = e.currentTarget.dataset.name;
                const content = document.getElementById('tutor-admin-withdraw-reject-content');
                content.innerHTML = `${__( 'You are rejecting '+ `<strong>${accountName}</strong>` + ' withdrawal request for '+ `<strong>${amount}</strong>` +'. Are you sure you want to reject?', 'tutor')}`;
            }
        }
   }
   // Approve & Reject form
    const approveForm = document.getElementById('tutor-admin-withdraw-approve-form');
    const rejectForm = document.getElementById('tutor-admin-withdraw-reject-form');

    // Handle form submit
    if (approveForm) {
        approveForm.onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(approveForm);
            formData.set('withdraw-id', withdrawId);
            const post = await ajaxHandler(formData, e.currentTarget);
            if (post.ok) {
                const success = post.json();
                if (success) {
                    location.reload();
                } else {
                    tutor_toast(__('Failed', 'tutor'), __('Something went wrong, please try again!', 'tutor'), "error");
                }
            }
        }
    }

    if (rejectForm) {
        rejectForm.onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(rejectForm);
            formData.set('withdraw-id', withdrawId);
            const post = await ajaxHandler(formData, e.currentTarget);
            if (post.ok) {
                const success = post.json();
                if (success) {
                    location.reload();
                } else {
                    tutor_toast(__('Failed', 'tutor'), __('Something went wrong, please try again!', 'tutor'), "error");
                }
            }
        }
    }

    // Onchange reject reason if other is value then create input field for adding reason
    const rejectType = document.getElementById('tutor-admin-withdraw-reject-type');
    if (rejectType) {
        rejectType.onchange = (e) => {
            const type = e.target.value;
            if (type === 'Other') {
                document.getElementById('tutor-withdraw-reject-other').innerHTML = `<div class="tutor-input-group tutor-form-control-lg tutor-mb-15">
                <input type="text" name="reject-comment" class="tutor-form-control" placeholder="${__('Withdraw Reject Reason', 'tutor')}" required/>
              </div>`;
            } 
        }
    }

    /**
     * Handle ajax request show toast message on success | failure
     *
     * @param {*} formData including action and all form fields
     */
     async function ajaxHandler(formData, target) {
    
        formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
        try {
            // select loading button
            const loadingButton = target.querySelector(".tutor-btn-loading");
            // keep previous text
            let prevHtml = loadingButton.innerHTML;
            // add loading ball
            loadingButton.innerHTML = `<div class="ball"></div>
            <div class="ball"></div>
            <div class="ball"></div>
            <div class="ball"></div>`;

            const post = await fetch(window._tutorobject.ajaxurl, {
                method: "POST",
                body: formData,
            });
            // after network request get previous html
            loadingButton.innerHTML = prevHtml;
            return post;
        } catch (error) {
            tutor_toast(__("Operation failed", "tutor"), error, "error")
        }
    }   



    /*
    * function to copy 
    * @textToCopy string
    * return a promise
    */
    function copyToClipboard(textToCopy) {
        // navigator clipboard api needs a secure context (https)
        if (navigator.clipboard && window.isSecureContext) {
            // navigator clipboard api method'
            return navigator.clipboard.writeText(textToCopy);
        } else {
            // text area method
            let textArea = document.createElement("textarea");
            textArea.value = textToCopy;
            // make the textarea out of viewport
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            textArea.style.top = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            return new Promise((res, rej) => {
                // here the magic happens
                document.execCommand('copy') ? res() : rej();
                textArea.remove();
            });
        }
    }

    const withDrawCopyBtns = document.querySelectorAll('.withdraw-tutor-copy-to-clipboard');
    if(withDrawCopyBtns) {
        for (let withDrawCopyBtn of withDrawCopyBtns) {
            withDrawCopyBtn.addEventListener('click', (event) => {
                // console.log(withDrawCopyBtn.previousSibling);
                console.log(event.currentTarget.dataset.textCopy);
                copyToClipboard(event.currentTarget.dataset.textCopy).then(text => {
                    let html = withDrawCopyBtn.innerHTML;
                    withDrawCopyBtn.innerHTML = `${__('Copied', 'tutor')}`;
                    setTimeout(() => {withDrawCopyBtn.innerHTML = html }, 5000);
                })
            })
        }
    }

});