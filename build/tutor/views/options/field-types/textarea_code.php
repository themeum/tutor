<div class="tutor-option-field-input code">
    <textarea name="email-settings-textarea-code" class="tutor-form-control" placeholder="Mailer Native Server Cron" readonly="">yes="{\"call_again\":\"yes\"}"
call_again="$yes"
while [[ "$call_again" == "$yes" ]]; do
call_again=$(curl -L "site_url_base/?tutor_cd_cron_type=os_native")
done</textarea>
    <!-- <span class="code-copy-btn"><i class="las la-clipboard-list"></i>Copy</span> -->
    <button class="tutor-btn tutor-is-outline tutor-is-xs code-copy-btn">
        <span class="tutor-btn-icon las la-clipboard-list"></span>
        <span>Copy</span>
    </button>
</div>