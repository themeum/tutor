<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
    crossorigin="anonymous">

<script src="<?php echo $config->get('payment_script') ?>"></script>

<script type="text/javascript" charset="utf-8">
document.addEventListener("DOMContentLoaded", () => {

    amazon.Pay.renderButton('#AmazonPayButton', {

        // set checkout environment
        merchantId: '<?php echo $config->get('merchant_id'); ?>',
        publicKeyId: '<?php echo $config->get('public_key_id'); ?>',
        ledgerCurrency: '<?php echo $config->get('ledger_currency'); ?>',

        // customize the buyer experience
        productType: 'PayOnly',
        placement: 'Cart',
        buttonColor: 'Gold',
        estimatedOrderAmount: {
            "amount": '<?php echo $total_amount; ?>',
            "currencyCode": '<?php echo $currency; ?>'
        },

        // configure Create Checkout Session request
        createCheckoutSessionConfig: {
            payloadJSON: '<?php echo $payload; ?>',
            signature: '<?php echo $signature; ?>',
            algorithm: 'AMZN-PAY-RSASSA-PSS-V2'
        }
    });
})
</script>

<!-- AmazonPay Button -->
<div class="d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div>
        <p> Click the button to initiate the Amazon Pay payment.</p>

        <div id="AmazonPayButton"></div>
        <a href="<?php echo $config->get('cancel_url'); ?>" class="d-flex align-items-center justify-content-center m-5">
            Cancel
        </a>
    </div>
</div>