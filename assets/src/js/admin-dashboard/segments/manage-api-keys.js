import ajaxHandler from "../../helper/ajax-handler";
import tutorFormData from "../../helper/tutor-formdata";

document.addEventListener("DOMContentLoaded", async function() {
    const { __ } = wp.i18n;
    const defaultErrMsg = __("Something went wrong, please try again after refreshing page", "tutor");
    const keysListWrapper = document.querySelector(".tutor-rest-api-keys-wrapper");
    const listTable = document.querySelector(".tutor-rest-api-keys-wrapper tbody");
    const apiKeysForm = document.getElementById("tutor-generate-api-keys");
    const submitBtn = document.getElementById("tutor-generate-api-keys-submit");
    const cancelBtn = document.getElementById("tutor-generate-api-keys-cancel");
    const doneBtn = document.getElementById("tutor-generate-api-keys-done");
    const modal = document.getElementById("tutor-add-new-api-keys");
    const formFields = document.getElementById("tutor-generate-api-keys-fields");
    const credentialsPanel = document.getElementById("tutor-generated-credentials");
    const generatedKeyInput = document.getElementById("tutor-generated-api-key");
    const generatedSecretInput = document.getElementById("tutor-generated-api-secret");
    const copyKeyBtn = document.getElementById("tutor-copy-generated-api-key");
    const copySecretBtn = document.getElementById("tutor-copy-generated-api-secret");
    const noRecordElem = document.getElementById("tutor-api-keys-no-record");
    const tokenSettingsForm = document.getElementById("tutor-rest-api-token-settings");
    const tokenSettingsSubmitBtn = document.querySelector("#tutor-rest-api-token-settings button[type=submit]");
    
    const updatePermissionForm = document.querySelector("#tutor-update-permission-form");
    const updateSubmitBtn = document.querySelector("#tutor-update-permission-modal button[type=submit]");
    const updatePermissionModal = document.querySelector("#tutor-update-permission-modal");

    if (!keysListWrapper && !tokenSettingsForm) {
        return;
    }

    const isSuccessResponse = (res) => Number(res?.status_code) === 200;
    const getErrorMessage = (res) => res?.message || defaultErrMsg;

    const copyToClipboard = async (text) => {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(text);
            return;
        }

        const temp = document.createElement("textarea");
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand("copy");
        document.body.removeChild(temp);
    };

    const resetGenerateModal = () => {
        if (!apiKeysForm) {
            return;
        }

        apiKeysForm.reset();
        formFields?.classList.remove("tutor-d-none");
        credentialsPanel?.classList.add("tutor-d-none");
        cancelBtn?.classList.remove("tutor-d-none");
        submitBtn?.classList.remove("tutor-d-none");
        doneBtn?.classList.add("tutor-d-none");

        if (generatedKeyInput) {
            generatedKeyInput.value = "";
        }
        if (generatedSecretInput) {
            generatedSecretInput.value = "";
        }
    };

    const showGeneratedCredentials = (key, secret) => {
        if (generatedKeyInput) {
            generatedKeyInput.value = key || "";
        }
        if (generatedSecretInput) {
            generatedSecretInput.value = secret || "";
        }

        formFields?.classList.add("tutor-d-none");
        credentialsPanel?.classList.remove("tutor-d-none");
        cancelBtn?.classList.add("tutor-d-none");
        submitBtn?.classList.add("tutor-d-none");
        doneBtn?.classList.remove("tutor-d-none");
    };

    // Reset generate modal when reopened from Add New.
    document.querySelector('[data-tutor-modal-target="tutor-add-new-api-keys"]')?.addEventListener("click", () => {
        resetGenerateModal();
    });

    modal?.addEventListener("click", (e) => {
        if (e.target.closest("[data-tutor-modal-close]")) {
            // Allow close to finish, then restore form state for next open.
            setTimeout(resetGenerateModal, 0);
        }
    });

    copyKeyBtn?.addEventListener("click", async () => {
        try {
            await copyToClipboard(generatedKeyInput?.value || "");
            tutor_toast(__("Success", "tutor"), __("API key copied to clipboard", "tutor"), "success");
        } catch (error) {
            tutor_toast(__("Failed", "tutor"), defaultErrMsg, "error");
        }
    });

    copySecretBtn?.addEventListener("click", async () => {
        try {
            await copyToClipboard(generatedSecretInput?.value || "");
            tutor_toast(__("Success", "tutor"), __("Secret copied to clipboard", "tutor"), "success");
        } catch (error) {
            tutor_toast(__("Failed", "tutor"), defaultErrMsg, "error");
        }
    });

    // Save token lifetime settings
    if (tokenSettingsForm) {
        tokenSettingsForm.onsubmit = async (e) => {
            e.preventDefault();

            const formData = new FormData(tokenSettingsForm);

            try {
                tokenSettingsSubmitBtn.classList.add("is-loading");
                tokenSettingsSubmitBtn.setAttribute("disabled", true);

                const post = await ajaxHandler(formData);
                const res = await post.json();

                if (isSuccessResponse(res)) {
                    tutor_toast(__("Success", "tutor"), res.message, "success");
                } else {
                    tutor_toast(__("Failed", "tutor"), getErrorMessage(res), "error");
                }
            } catch (error) {
                tutor_toast(__("Failed", "tutor"), defaultErrMsg, "error");
            } finally {
                tokenSettingsSubmitBtn.classList.remove("is-loading");
                tokenSettingsSubmitBtn.removeAttribute("disabled");
            }
        };
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
    
                if (isSuccessResponse(res)) {
                    const payload = res.data || {};
                    const rowHtml = typeof payload === "object" && payload.html ? payload.html : payload;

                    if (rowHtml) {
                        listTable.insertAdjacentHTML("afterbegin", `${rowHtml}`);
                    }

                    showGeneratedCredentials(payload.key, payload.secret);
                    tutor_toast(__("Success", "tutor"), res.message, "success");

                    if (noRecordElem) {
                        noRecordElem.remove();
                    }
                } else {
                    tutor_toast(__("Failed", "tutor"), getErrorMessage(res), "error");
                }
            } catch (error) {
                tutor_toast(__("Failed", "tutor"), defaultErrMsg, "error");
            } finally {
                submitBtn.classList.remove("is-loading");
                submitBtn.removeAttribute("disabled");
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
    
                if (isSuccessResponse(res)) {
                    const row = document.getElementById(formData.get("meta_id"));
                    if (row) {
                        row.outerHTML = res.data;
                    }
                    tutor_toast(__("Success", "tutor"), res.message, "success");
                } else {
                    tutor_toast(__("Failed", "tutor"), getErrorMessage(res), "error");
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

                    if (isSuccessResponse(res)) {
                        target.closest("tr").remove();
                        tutor_toast(__("Success", "tutor"), res.message, "success");
                    } else {
                        tutor_toast(__("Failed", "tutor"), getErrorMessage(res), "error");
                        target.classList.remove("is-loading");
                        target.removeAttribute("disabled");
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
