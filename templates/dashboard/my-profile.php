

<div class="dashboard-page-title">
    <h3><?php _e('Withdrawal Preference'); ?></h3>
</div>

<div class="tutor-dashboard-content-inner">

    <h4><?php _e('Select a withdraw method', 'tutor'); ?></h4>

	<?php
	$tutor_withdrawal_methods = tutor_withdrawal_methods();

	if (tutor_utils()->count($tutor_withdrawal_methods)){
		?>

        <div class="withdraw-method-select-wrap">
			<?php
			foreach ($tutor_withdrawal_methods as $method_id => $method){
				?>

                <div class="withdraw-method-select withdraw-method-<?php echo $method_id; ?>" data-withdraw-method="<?php echo $method_id; ?>">
                    <input type="radio" id="withdraw_method_select_<?php echo $method_id; ?>" class="withdraw-method-select-input" name="tutor_selected_withdraw_method" value="<?php echo $method_id; ?>" style="display: none;">

                    <label for="withdraw_method_select_<?php echo $method_id; ?>">
						<?php echo tutor_utils()->avalue_dot('method_name', $method);  ?>
                    </label>
                </div>
				<?php
			}
			?>

        </div>


        <div class="withdraw-method-forms-wrap">

			<?php
			foreach ($tutor_withdrawal_methods as $method_id => $method){

				$form_fields = tutor_utils()->avalue_dot('form_fields', $method);
				?>

                <div id="withdraw-method-form-<?php echo $method_id; ?>" class="withdraw-method-form withdraw-method-form-<?php echo $method_id;
				?>" style="display: block;">

					<?php

					if (tutor_utils()->count($form_fields)){
						foreach ($form_fields as $field_name => $field){

							?>

                            <div class="withdraw-method-field-wrap">

								<?php
								if (! empty($field['label'])){
									echo "<label for='field_{$method_id}_$field_name'>{$field['label']}</label>";
								}

								$passing_data = array(
									'method_id' => $method_id,
									'method' => $method,
									'field_name' => $field_name,
									'field' => $field,
								);

								tutor_load_template("dashboard.withdraw-method-fields.{$field['type']}", $passing_data);
								?>

                            </div>
							<?php

						}
					}

					?>


                </div>
				<?php
			}
			?>


        </div>


		<?php
	}
	?>




	<?php

	echo '<pre>';
	print_r($tutor_withdrawal_methods);
	echo '</pre>';

	?>

</div>
