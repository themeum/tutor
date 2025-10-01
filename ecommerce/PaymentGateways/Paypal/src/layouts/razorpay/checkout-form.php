<form action="<?php echo esc_url( $checkout->razorpay_url ); ?>" id="razorpay-form">
    <input type="hidden" name="key_id" value="<?php echo esc_attr( $checkout->key_id ); ?>" />
    <input type="hidden" name="amount" value="<?php echo esc_attr( $checkout->amount ); ?>" />
    <input type="hidden" name="order_id" value="<?php echo esc_attr( $checkout->razorpay_order_id ); ?>" />
    <input type="hidden" name="name" value="<?php echo esc_attr( $checkout->store_name ); ?>" />
    <input type="hidden" name="notes[order_id]" value="<?php echo esc_attr( $checkout->order_id ); ?>" />
    <input type="hidden" name="callback_url" value="<?php echo esc_url( $checkout->callback_url ); ?>" />
    <input type="hidden" name="cancel_url" value="<?php echo esc_url( $checkout->cancel_url ); ?>" />

    <?php if($checkout->username): ?>
    <input type="hidden" name="profile[name]" value="<?php echo esc_attr( $checkout->username ); ?>" />
    <?php endif; ?>
    <?php if($checkout->email): ?>
    <input type="hidden" name="profile[email]" value="<?php echo esc_attr( $checkout->email ); ?>" />
    <?php endif; ?>
</form>

<script defer>
window.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('razorpay-form');
    form.submit();
});
</script>
