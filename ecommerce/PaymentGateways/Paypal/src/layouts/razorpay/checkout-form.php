<form action="<?php echo $checkout->razorpay_url; ?>" id="razorpay-form">
    <input type="hidden" name="key_id" value="<?php echo $checkout->key_id; ?>" />
    <input type="hidden" name="amount" value="<?php echo $checkout->amount; ?>" />
    <input type="hidden" name="order_id" value="<?php echo $checkout->razorpay_order_id; ?>" />
    <input type="hidden" name="name" value="<?php echo $checkout->store_name; ?>" />
    <input type="hidden" name="notes[order_id]" value="<?php echo $checkout->order_id; ?>" />
    <input type="hidden" name="callback_url" value="<?php echo $checkout->callback_url; ?>" />
    <input type="hidden" name="cancel_url" value="<?php echo $checkout->cancel_url; ?>" />

    <?php if($checkout->username): ?>
    <input type="hidden" name="profile[name]" value="<?php echo $checkout->username; ?>" />
    <?php endif; ?>
    <?php if($checkout->email): ?>
    <input type="hidden" name="profile[email]" value="<?php echo $checkout->email; ?>" />
    <?php endif; ?>
</form>

<script defer>
window.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('razorpay-form');
    form.submit();
});
</script>