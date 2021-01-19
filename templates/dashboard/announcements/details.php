
<div class="tutor-dashboard-content-inner tutor-frontend-dashboard-withdrawal">
    <div class="tutor-earning-withdraw-form-wrap">
        <div class="tutor-withdrawal-op-up-frorm">
            <div>
                <i class="tutor-icon-line-cross close-withdraw-form-btn"></i>
                <img src="<?php echo $image_base; ?>wallet.svg" />
                <h3><?php _e('Make a Withdrawal', 'tutor'); ?></h3>
                <p><?php _e('Please enter withdrawal amount and click the submit request button', 'tutor'); ?></p>
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <span><?php _e('Current Balance', 'tutor'); ?></span><br />
                                
                            </td>
                            <td>
                                <span><?php _e('Selected Payment Method', 'tutor'); ?></span><br />
                               
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div>
                <form id="tutor-earning-withdraw-form" action="" method="post">

                
                    <div class="withdraw-form-field-row">
                        <label for="tutor_withdraw_amount"><?php _e('Amount', 'tutor') ?></label>
                        <div class="withdraw-form-field-amount">
                            <span>
                                
                            </span>
                            <input type="number" min="1" name="tutor_withdraw_amount">
                        </div>
                        <div class="inline-image-text">
                            <img src="<?php echo $image_base; ?>info-icon-question.svg" />
                            <span>Minimum withdraw amount is </span>
                        </div>
                    </div>

                    <div class="tutor-withdraw-button-container">
                        <button class="tutor-btn tutor-btn-secondary close-withdraw-form-btn"><?php _e('Cancel', 'tutor'); ?></button>
                        <button class="tutor-btn" type="submit" id="tutor-earning-withdraw-btn" name="withdraw-form-submit"><?php _e('Submit Request', 'tutor'); ?></button>
                    </div>

                    <div class="tutor-withdraw-form-response"></div>

                   
                </form>
            </div>
        </div>

    </div>

</div> 