<form id="redsys-form" action="<?php echo $form_url; ?>" method="POST">
    <input type="hidden" name="Ds_SignatureVersion" value="<?php echo $signature_version; ?>" />
    <input type="hidden" name="Ds_MerchantParameters" value="<?php echo $request_parameters; ?>" />
    <input type="hidden" name="Ds_Signature" value="<?php echo $signature; ?>" />
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('redsys-form');
    form.submit();
})
</script>