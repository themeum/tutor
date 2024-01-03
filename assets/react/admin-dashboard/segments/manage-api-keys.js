import tutorFormData from "../../helper/tutor-formdata";
import ajaxHandler from "./filter";

const { __ } = wp.i18n;

document.addEventListener("DOMContentLoaded", async function() {
    const defaultErrMsg = __("Something went wrong, please try again after refreshing page", "tutor");
    const keysListWrapper = document.querySelector(".tutor-rest-api-keys-wrapper");
    const listTable = document.querySelector(".tutor-rest-api-keys-wrapper tbody");
    const apiKeysForm = document.getElementById("tutor-generate-api-keys");
    const submitBtn = document.querySelector("#tutor-generate-api-keys button[type=submit]");
    const modal = document.getElementById("tutor-add-new-api-keys");
    const noRecordElem = document.getElementById("tutor-api-keys-no-record");
    
    const updatePermissionForm = document.querySelector("#tutor-update-permission-form");
    const updateSubmitBtn = document.querySelector("#tutor-update-permission-modal button[type=submit]");
    const updatePermissionModal = document.querySelector("#tutor-update-permission-modal");

    if (!keysListWrapper) {
        return;
    }

    // Add API keys
    if (apiKeysForm) {
        apiKeysForm.onsubmit = async (e) => {
            e.preventDefault();
    
            const formData = new FormData(apiKeysForm);
    
            try {
                // Show loading
                submitBtn.classList.add("is-loading");
                submitBtn.setAttribute("disabled", true);
    
                const post = await ajaxHandler(formData);
                const res = await post.json();
                const { success, data } = res;
    
                if (success) {
                    listTable.insertAdjacentHTML("afterbegin", `${data}`);
                    tutor_toast(__("Success", "tutor"), __("API key & secret generated successfully"), "success");
                } else {
                    tutor_toast(__("Failed", "tutor"), data, "error");
                }
            } catch (error) {
                tutor_toast(__("Failed", "tutor"), defaultErrMsg, "error");
            } finally {
                submitBtn.classList.remove("is-loading");
                submitBtn.removeAttribute("disabled");
                modal.classList.remove("tutor-is-active");
                document.body.classList.remove('tutor-modal-open');
                if (noRecordElem) {
                    noRecordElem.remove();
                }
                // Reset form
                apiKeysForm.reset();
            }
        };
    }

    // Edit API keys
    if (updatePermissionForm) {
        updatePermissionForm.onsubmit = async (e) => {
            e.preventDefault();
    
            const formData = new FormData(updatePermissionForm);
    
            try {
                updateSubmitBtn.classList.add("is-loading");
                updateSubmitBtn.setAttribute("disabled", true);
    
                const post = await ajaxHandler(formData);
                const res = await post.json();
                const { success, data } = res;
    
                if (success) {
                    document.getElementById(formData.get("meta_id")).innerHTML = data;
                    tutor_toast(__("Success", "tutor"), __("API key permission updated successfully"), "success");
                } else {
                    tutor_toast(__("Failed", "tutor"), data, "error");
                }
            } catch (error) {
                tutor_toast(__("Failed", "tutor"), defaultErrMsg, "error");
            } finally {
                updateSubmitBtn.classList.remove("is-loading");
                updateSubmitBtn.removeAttribute("disabled");
                updatePermissionModal.classList.remove("tutor-is-active");
                document.body.classList.remove('tutor-modal-open');
                updatePermissionForm.reset();
            }
        };
    }

    if (listTable) {
        listTable.addEventListener("click", async (e) => {
            const target = e.target;

            // Revoke API keys
            if (target.hasAttribute("data-meta-id")) {
                const metaId = target.dataset.metaId;
                const formData = tutorFormData([{ action: "tutor_revoke_api_keys", meta_id: metaId }]);

                try {
                    target.classList.add("is-loading");
                    target.setAttribute("disabled", true);

                    const post = await ajaxHandler(formData);
                    const res = await post.json();
                    const { success, data } = res;

                    if (success) {
                        target.closest("tr").remove();
                        tutor_toast(__("Success", "tutor"), data, "success");
                    } else {
                        tutor_toast(__("Failed", "tutor"), data, "error");
                    }
                } catch (error) {
                    tutor_toast(__("Failed", "tutor"), defaultErrMsg, "error");
                    target.classList.remove("is-loading");
                    target.removeAttribute("disabled");
                }
            }

            // Add meta id to forms input value
            if (target.hasAttribute("data-update-id")) {
                const metaId = target.dataset.updateId;
                const permission = target.dataset.permission;
                const description = target.dataset.description;
                if (metaId) {
                    updatePermissionForm.querySelector("input[name=meta_id]").value = metaId;
                    updatePermissionForm.querySelector("select[name=permission]").value = permission;
                    updatePermissionForm.querySelector("textarea[name=description]").value = description;
                }
            }
        });
    }
});
