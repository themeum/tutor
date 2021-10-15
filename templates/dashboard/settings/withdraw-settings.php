<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$col_classes = array(
    1 => 'tutor-col-12',
    2 => 'tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6',
    3 => 'tutor-col-12 tutor-col-lg-4'
);
?>
<div class="tutor-dashboard-setting-withdraw tutor-dashboard-content-inner">
    <div class="tutor-dashboard-inline-links">
        <?php
            tutor_load_template('dashboard.settings.nav-bar', ['active_setting_nav'=>'withdrawal']);
        ?>
        <h3><?php _e('Select a withdraw method', 'tutor') ?></h3>
    </div>

    <form id="tutor-withdraw-account-set-form" action="" method="post">

        <?php
        $tutor_withdrawal_methods = apply_filters( 'tutor_withdrawal_methods_available', array() );
        
        if (tutor_utils()->count($tutor_withdrawal_methods)){
            $saved_account = tutor_utils()->get_user_withdraw_method();
            $old_method_key = tutor_utils()->avalue_dot('withdraw_method_key', $saved_account);
            $min_withdraw_amount = tutor_utils()->get_option('min_withdraw_amount');
            ?>
            <div class="tutor-row tutor-mb-30">
                <?php
                $method_count = count($tutor_withdrawal_methods);
                foreach ($tutor_withdrawal_methods as $method_id => $method){
                    ?>
                    <div class="<?php echo $col_classes[$method_count]; ?>" data-withdraw-method="<?php echo $method_id; ?>">
                        <label class="tutor-radio-select tutor-align-items-center tutor-mb-10">
                            <input class="tutor-form-check-input" type="radio" name="tutor_selected_withdraw_method" value="<?php echo $method_id; ?>" <?php checked($method_id, $old_method_key) ?>/>
                            <div class="tutor-radio-select-content">
                                <span class="tutor-radio-select-title">
                                    <?php echo tutor_utils()->avalue_dot('method_name', $method);  ?>
                                </span>
                                <?php _e('Min withdraw', 'tutor'); ?> <?php echo tutor_utils()->tutor_price($min_withdraw_amount);?>
                            </div>
                        </label>
                    </div>
                    <?php
                }
                ?>
            </div>

            <input type="hidden" value="tutor_save_withdraw_account" name="action"/>
            <?php 
                wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); 
                do_action('tutor_withdraw_set_account_form_before');
                
                foreach ($tutor_withdrawal_methods as $method_id => $method){
                    $form_fields = tutor_utils()->avalue_dot('form_fields', $method);
                    ?>

                    <div data-withdraw-form="<?php echo $method_id; ?>" class="tutor-row withdraw-method-form" style="<?php echo $old_method_key!=$method_id ? 'display: none;' : ''; ?>">
                        <?php 
                        do_action("tutor_withdraw_set_account_{$method_id}_before");
                        
                        $field_count = tutor_utils()->count($form_fields);
                        if ($field_count){
                            foreach ($form_fields as $field_name => $field){
                                ?>
                                <div class="<?php echo $field_count>1 ? 'tutor-col-12 tutor-col-sm-6' : 'tutor-col-12'; ?> tutor-mb-30">
                                    <?php
                                    if (! empty($field['label'])){
                                        echo "<label for='field_{$method_id}_$field_name'>{$field['label']}</label>";
                                    }

                                    $passing_data = apply_filters('tutor_withdraw_account_field_type_data', array(
                                        'method_id' => $method_id,
                                        'method' => $method,
                                        'field_name' => $field_name,
                                        'field' => $field,
                                        'old_value' => null,
                                    ));
                                    $old_value = tutor_utils()->avalue_dot($field_name.".value", $saved_account);
                                    if ($old_value){
                                        $passing_data['old_value'] = $old_value;
                                    }

                                    if(in_array($field['type'], array('text', 'number', 'email'))) {
                                        ?>
                                            <input class="tutor-form-control" type="<?php echo $field['type']; ?>" name="withdraw_method_field[<?php echo $method_id ?>][<?php echo $field_name ?>]" value="<?php echo $old_value; ?>" >
                                        <?php
                                    } else if($field['type']=='textarea') {
                                        ?>
                                            <textarea class="tutor-form-control" name="withdraw_method_field[<?php echo $method_id ?>][<?php echo $field_name ?>]">
                                                <?php echo $old_value; ?>
                                            </textarea>
                                        <?php
                                    }

                                    if ( ! empty($field['desc'])){
                                        echo "<p class='withdraw-field-desc'>{$field['desc']}</p>";
                                    }
                                    ?>
                                </div>
                                <?php
                            }
                        }
                        ?>

                        <?php do_action("tutor_withdraw_set_account_{$method_id}_after"); ?>

                        <div class="withdraw-account-save-btn-wrap">
                            <button type="submit" class="tutor_set_withdraw_account_btn tutor-btn" name="withdraw_btn_submit">
                                <?php _e('Save Withdrawal Account', 'tutor'); ?>
                            </button>
                        </div>
                    </div>
                    <?php
                }
                
                do_action('tutor_withdraw_set_account_form_after'); 
        }
        ?>
    </form>
</div>
