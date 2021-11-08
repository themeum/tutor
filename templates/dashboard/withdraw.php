<?php

/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$earning_sum = tutor_utils()->get_earning_sum();
$min_withdraw = tutor_utils()->get_option('min_withdraw_amount');
$formatted_min_withdraw_amount = tutor_utils()->tutor_price($min_withdraw);

$saved_account = tutor_utils()->get_user_withdraw_method();
$withdraw_method_name = tutor_utils()->avalue_dot('withdraw_method_name', $saved_account);

$user_id = get_current_user_id();
$balance_formatted = tutor_utils()->tutor_price($earning_sum->balance);
$is_balance_sufficient = true; //$earning_sum->balance >= $min_withdraw;
$all_histories = tutor_utils()->get_withdrawals_history($user_id, array('status' => array('pending', 'approved', 'rejected')));

$image_base = tutor()->url . '/assets/images/';
$method_icons = array(
    'bank_transfer_withdraw' => $image_base . 'icon-bank.svg',
    'echeck_withdraw' => $image_base . 'icon-echeck.svg',
    'paypal_withdraw' => $image_base . 'icon-paypal.svg'
);

$status_message = array(
    'rejected' => __('Please contact the site administrator for more information.', 'tutor'),
    'pending' => __('Withdrawal request is pending for approval, please hold tight.', 'tutor')
);

$currency_symbol = '';
if(function_exists('get_woocommerce_currency_symbol')){
    $currency_symbol=get_woocommerce_currency_symbol();
}
else if(function_exists('edd_currency_symbol')){
    $currency_symbol=edd_currency_symbol();
}

?>

<div class="tutor-dashboard-content-inner tutor-frontend-dashboard-withdrawal color-text-primary">
    <h4><?php echo __('Withdrawal', 'tutor'); ?></h4>

    <div class="tutor-component-three-col-action">
        <img src="<?php echo esc_url($image_base); ?>wallet.svg" />
        
        <div>
            <small><?php esc_html_e('Current Balance', 'tutor'); ?></small>
            <p>
                <?php
                if ($is_balance_sufficient) {
                    echo sprintf(__('You currently have %s %s %s ready to withdraw', 'tutor'), "<strong class='available_balance'>", $balance_formatted, '</strong>');
                } else {
                    echo sprintf(__('You currently have %s %s %s and this is insufficient balance to withdraw', 'tutor'), "<strong class='available_balance'>", $balance_formatted, '</strong>');
                }
                ?>
            </p>
        </div>

        <?php
            if ($is_balance_sufficient && $withdraw_method_name) { 
                ?>
                <button class="tutor-btn open-withdraw-form-btn">
                    <?php esc_html_e('Withdrawal Request', 'tutor'); ?>
                </button> 
                <?php
            }
        ?>
    </div>

    <div class="current-withdraw-account-wrap withdrawal-preference inline-image-text">
        <img src="<?php echo esc_url($image_base); ?>info-icon-question.svg" />
        <span>
            <?php
            $my_profile_url = tutor_utils()->get_tutor_dashboard_page_permalink('settings/withdraw-settings');
            echo $withdraw_method_name ?  sprintf(__('The preferred payment method is selected as %s. ', 'tutor'), $withdraw_method_name) : '';
            echo sprintf(__('You can change your %s withdrawal preference %s', 'tutor'), "<a href='{$my_profile_url}'>", '</a>');
            ?>
        </span>
    </div>

    <?php
    if ($is_balance_sufficient && $withdraw_method_name) {
    ?>

        <div class="tutor-earning-withdraw-form-wrap">
            <div>
                <div class="tutor-withdrawal-pop-up-success">
                    <div>
                        <i class="tutor-icon-line-cross close-withdraw-form-btn" data-reload="yes"></i>
                        <br />
                        <br />
                        <div style="text-align:center">
                            <img src="<?php echo $image_base; ?>icon-cheers.svg" />
                            <h3><?php esc_html_e('Your withdrawal request has been successfully accepted', 'tutor'); ?></h3>
                            <p><?php esc_html_e('Please check your transaction notification on your connected withdrawal method', 'tutor'); ?></p>
                        </div>
                        <br />
                        <br />
                        <div class="tutor-withdraw-form-response"></div>
                    </div>
                </div>
                <div class="tutor-withdrawal-op-up-frorm">
                    <div>
                        <i class="tutor-icon-line-cross close-withdraw-form-btn"></i>
                        <img src="<?php echo $image_base; ?>wallet.svg" />
                        <div class="text-medium-h5 color-text-primary tutor-mt-20">
                            <?php esc_html_e('Withdrawal Request', 'tutor'); ?>
                        </div>
                        <p class="tutor-mb-40"><?php esc_html_e('Please enter withdrawal amount and click the submit request button', 'tutor'); ?></p>
                        <table>
                            <tbody>
                                <tr>
                                    <td>
                                        <span><?php esc_html_e('Current Balance', 'tutor'); ?></span><br />
                                        <b><?php echo wp_kses_post($balance_formatted); ?></b>
                                    </td>
                                    <td>
                                        <span><?php esc_html_e('Selected Payment Method', 'tutor'); ?></span><br />
                                        <b><?php esc_html_e($withdraw_method_name); ?></b>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                    <?php
                        /**
                         * @since 1.8.1
                         * set min value for withdraw input field as per settings
                         * field req step .01
                         */
                     
                    ?>
                        <form id="tutor-earning-withdraw-form" action="" method="post">
                            <?php wp_nonce_field(tutor()->nonce_action, tutor()->nonce); ?>
                            <input type="hidden" value="tutor_make_an_withdraw" name="action" />
                            <?php do_action('tutor_withdraw_form_before'); ?>
                            <div class="withdraw-form-field-row">
                                <label for="tutor_withdraw_amount"><?php esc_html_e('Amount', 'tutor') ?></label>
                                <div class="withdraw-form-field-amount">
                                    <span>
                                        <span><?php echo esc_attr($currency_symbol); ?></span>
                                    </span>
                                    <input type="number" min="<?php echo esc_attr($min_withdraw); ?>" name="tutor_withdraw_amount" id="tutor_withdraw_amount" step=".01" required>
                                </div>
                                <div class="inline-image-text">
                                    <img src="<?php echo $image_base; ?>info-icon-question.svg" />
                                    <span>
                                        <?php echo __( 'Minimum withdraw amount is', 'tutor' ).' '.strip_tags($formatted_min_withdraw_amount); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="tutor-withdraw-button-container">
                                <button class="tutor-btn tutor-btn-secondary close-withdraw-form-btn"><?php esc_html_e('Cancel', 'tutor'); ?></button>
                                <button class="tutor-btn" type="submit" id="tutor-earning-withdraw-btn" name="withdraw-form-submit"><?php esc_html_e('Submit Request', 'tutor'); ?></button>
                            </div>

                            <div class="tutor-withdraw-form-response"></div>

                            <?php do_action('tutor_withdraw_form_after'); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php
    }
    ?>

    <div class="withdraw-history-table-wrap tutor-tooltip-inside">
        <div class="withdraw-history-table-title color-text-primary">
            <h4> <?php esc_html_e('Withdrawal History', 'tutor'); ?></h4>
        </div>

        <?php
        if (tutor_utils()->count($all_histories->results)) {
        ?>
            <table class="tutor-ui-table tutor-ui-table-responsive">
                <thead>
                    <tr>
                        <th>
                            <span class="text-regular-small color-text-subsued">
                                <?php esc_html_e('Withdrawal Method', 'tutor') ?>
                            </span>
                        </th>
                        <th width="30%">
                            <span class="text-regular-small color-text-subsued">
                                <?php esc_html_e('Requested On', 'tutor') ?>
                            </span>
                        </th>
                        <th width="15%">
                            <span class="text-regular-small color-text-subsued">
                                <?php esc_html_e('Amount', 'tutor') ?>
                            </span>
                        </th>
                        <th width="15%">
                            <span class="text-regular-small color-text-subsued">
                                <?php esc_html_e('Status', 'tutor') ?>
                            </span>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($all_histories->results as $withdraw_history) {
                    ?>
                        <tr>
                            <td>
                                <?php
                                $method_data = maybe_unserialize($withdraw_history->method_data);
                                $method_key = $method_data['withdraw_method_key'];
                                $method_title = '';

                                switch($method_key) {
                                    case 'bank_transfer_withdraw': 
                                        $method_title = $method_data['account_number']['value']; 
                                        $method_title = substr_replace($method_title, '****', 2, strlen($method_title)-4);
                                        break;
                                    case 'paypal_withdraw': 
                                        $method_title = $method_data['paypal_email']['value']; 
                                        $email_base = substr($method_title, 0, strpos($method_title, '@'));
                                        $method_title = substr_replace($email_base, '****', 2, strlen($email_base)-3) . substr($method_title, strpos($method_title, '@'));
                                        break;
                                }
                                ?>
                                <div class="inline-image-text is-inline-block">
                                    <img src="<?php echo esc_url(isset($method_icons[$method_key]) ? $method_icons[$method_key] : ''); ?>" />
                                    &nbsp;
                                    <span>
                                        <?php
                                        echo '<strong class="withdraw-method-name">', tutor_utils()->avalue_dot('withdraw_method_name', $method_data), '</strong>';
                                        echo '<small>', $method_title, '</small>';
                                        ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php
                                    echo esc_attr(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($withdraw_history->created_at)));
                                ?>
                            </td>
                            <td>
                                <strong><?php echo tutor_utils()->tutor_price($withdraw_history->amount); ?></strong>
                            </td>
                            <td>
                                <span class="inline-image-text is-inline-block">
                                    <span class="tutor-badge-label 
                                    <?php if($withdraw_history->status == 'approved'){ echo 'label-success'; } ?>
                                    <?php if($withdraw_history->status == 'pending'){ echo 'label-warning'; } ?>
                                    <?php if($withdraw_history->status == 'rejected'){ echo 'label-danger'; } ?>">
                                        <?php echo __(ucfirst($withdraw_history->status), 'tutor'); ?>
                                    </span>
                                </span>
                            </td>
                            <td>
                                <?php
                                if ($withdraw_history->status !== 'approved' && isset($status_message[$withdraw_history->status])) {
                                ?>
                                    <span class="tutor-status-text-container">
                                        <span class="tool-tip-container">
                                            <img src="<?php echo esc_url($image_base); ?>info-icon.svg" />
                                            <span class="tooltip tip-left" role="tooltip">
                                                <?php esc_html_e($status_message[$withdraw_history->status]); ?>
                                            </span>
                                        </span>
                                    </span>
                                <?php
                                }
                                ?>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        <?php
        } else {
        ?>
            <p><?php esc_html_e('No withdrawal yet', 'tutor'); ?></p>
        <?php
        }
        ?>
    </div>
</div>