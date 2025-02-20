<div id="easystore-moneris-checkout" style="z-index:3"></div>

<input type="hidden" name="ticket" value="<?php echo $checkout->ticket; ?>" />
<input type="hidden" name="environment" value="<?php echo $checkout->environment; ?>" />
<input type="hidden" name="orderId" value="<?php echo $checkout->order_id; ?>" />
<input type="hidden" name="notifyUrl" value="<?php echo $checkout->notify_url; ?>" />
<input type="hidden" name="cancelUrl" value="<?php echo $checkout->cancel_url; ?>" />


<?php echo $checkout->scripts; ?>