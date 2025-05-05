<form method="POST" id="payfast-form" action="<?php echo $form_action_url; ?>">

    <?php foreach ($input_fields as $field => $value) : ?>
    <input type="hidden" name="<?php echo $field; ?>" value="<?php echo $value; ?>" />
    <?php endforeach; ?>

    <input type="hidden" name="signature" value="<?php echo md5(http_build_query($input_fields)); ?>" />
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('payfast-form');
    form.submit();
})
</script>