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
            const post = await ajaxHandler(formData);
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
            const post = await ajaxHandler(formData);
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
     async function ajaxHandler(formData) {
        formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
        try {
            const post = await fetch(window._tutorobject.ajaxurl, {
                method: "POST",
                body: formData,
            });
            return post;
        } catch (error) {
            tutor_toast(__("Operation failed", "tutor"), error, "error")
        }
    }   
});