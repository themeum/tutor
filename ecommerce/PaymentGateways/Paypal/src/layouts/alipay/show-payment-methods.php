<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
    crossorigin="anonymous">

<div class="container mt-3 mb-3">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <form class="row" method="post" action="<?php echo esc_url( $form_action_url ); ?>">
                        <!-- Form title -->
                        <div class="mb-3">
                            <span><b>Available Payment Methods</b></span>
                        </div>

                        <div class="mb-3">
                            <label class="radio-label">
                                <input type="radio" name="payment_method" value="CARD" class="radio-input">
                                <span class="radio-text">Card</span>
                            </label>
                        </div>

                        <?php foreach ($available_payment_methods as $type) : ?>
                        <div class=" mb-3">
                            <label class="radio-label">
                                <input type="radio" name="payment_method" value="<?php echo esc_attr( $type->paymentMethodType ); ?>" class="radio-input" required>

                                <span class="radio-text"><?php echo esc_html( $type->logo->logoName ); ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>

                        <input type="hidden" name="payment_data" value="<?php echo esc_attr( $payment_data ); ?>">

                        <div class="col align-self-center">
                            <button type="submit" class="btn btn-success">
                                Submit
                            </button>
                            <button type="button" class="btn btn-danger" onclick="window.location.href='<?php echo esc_url( $cancel_url ); ?>'">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
