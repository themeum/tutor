<?php
$args = $this->args;
$current_tab = tutor_utils()->array_get('settings_tab', $_GET);

?>

<div id="tutor-metabox-course-settings-tabs" class="tutor-course-settings-tabs">
    <div class="course-settings-tabs-container">
        <div class="settings-tabs-navs-wrap">
            <ul class="settings-tabs-navs">
				<?php
				$i = 0;
				foreach ($args as $key => $arg){
					$i++;

					$active = $i ===1 ? 'active' : '';

					$label      = tutor_utils()->array_get('label', $arg);
					$icon_class = tutor_utils()->array_get('icon_class', $arg);
					$url        = add_query_arg(array('settings_tab' => $key));

					$icon = '';
					if ($icon_class){
						$icon = "<i class='{$icon_class}'></i>";
					}

					echo "<li class='{$active}'>
							<a href='{$url}' data-target='#settings-tab-{$key}'>
								{$icon} {$label}
							</a> 
						</li>";
				} ?>
            </ul>
        </div>

        <div class="settings-tabs-container">
			<?php
			
			$i = 0;

			foreach ($args as $key => $tab){
				$i++;

				$label = tutor_utils()->array_get('label', $tab);
				$callback = tutor_utils()->array_get('callback', $tab);
				$fields = tutor_utils()->array_get('fields', $tab);

				if ($current_tab){
					$active = $current_tab === $key ? 'active' : '' ;
					$display = $current_tab === $key ? 'block' : 'none' ;
				}else{
					$active = $i ===1 ? 'active' : '';
					$display = $i ===1 ? 'block' : 'none' ;
				}

				echo "<div id='settings-tab-{$key}' class='settings-tab-wrap {$active}' style='display: {$display};'>";

					do_action("tutor_course/settings_tab_content/before", $key, $tab);
					do_action("tutor_course/settings_tab_content/before/{$key}", $tab);

					
					if (tutor_utils()->count($fields)){
						foreach ($fields as $field_key => $field){
							$type = tutor_utils()->array_get('type', $field);
							$value = tutor_utils()->array_get('value', $field);

							if($type=='line_break') {
								echo '<hr class="tutor-mb-30"/>';
								continue;
							}

							?>
							<div class="tutor-bs-row tutor-mb-30">
								<?php
									if (!empty($field['label'])){
										?>
										<div class="tutor-bs-col-12">
											<label class="tutor-course-setting-label"><?php echo $field['label']; ?></label>
										</div>
										<?php
									}
								?>
								<div class="tutor-bs-col-12">
									<?php
										switch($field['type']) {
											case 'number' :
												echo '<input class="tutor-form-control" type="number" name="' . $field_key . '" value="' . $value . '" >';
												break;

											case 'radio' :
												foreach($field['options'] as $value => $label) {
													$id_string = 'course_setting_radio_' . $field_key;
													?>
													<div class="tutor-form-check tutor-mb-10">
														<input type="radio" id="<?php echo $id_string; ?>" class="tutor-form-check-input" name="<?php echo $field_key; ?>" value="<?php echo $value; ?>" <?php echo $value==$field['value'] ? 'checked="checked"' : ''; ?>/>
														<label for="<?php echo $id_string; ?>" class="text-medium-caption">
															<?php echo $label; ?>
														</label>
													</div>
													<?php
												}
												break;

											case 'checkbox' :
												foreach($field['options'] as $option) {
													$id_string = 'course_setting_' . $field_key;
													?>
													<div class="tutor-form-check tutor-mb-10">
														<input id="<?php echo $id_string; ?>" type="checkbox" class="tutor-form-check-input" name="<?php echo $field_key; ?>" <?php echo $option['checked'] ? 'checked="checked"' : ''; ?>/>
														<label for="<?php echo $id_string; ?>" class="text-medium-caption">
															<?php echo $option['label_title']; ?>
															<?php 
																if(!empty($option['hint'])) {
																	echo '<span class="tutor-bs-d-block text-regular-small">'.$option['hint'].'</span>';
																}
															?>
														</label>
													</div>
													<?php
												}
												break;

											case 'select' :
												?>
												<select class="tutor-form-select" name="_tutor_course_settings[<?php echo $field['field_key']; ?>]" class="tutor_select2">
													<?php
													if ( ! isset($field['select_options']) || $field['select_options'] !== false){
														echo '<option value="">'.__('Select Option', 'tutor').'</option>';
													}
													if ( ! empty($field['options'])){
														foreach ($field['options'] as $optionKey => $option){
															?>
															<option value="<?php echo $optionKey ?>" <?php selected($this->get($field['field_key']),  $optionKey) ?> ><?php echo $option ?></option>
															<?php
														}
													}
													?>
												</select>
												<?php
										}

										if (isset($field['desc'])){
											?>
												<p class="tutor-input-feedback tutor-has-icon">
													<i class="ttr-info-circle-outline-filled tutor-input-feedback-icon"></i>
													<?php echo $field['desc']; ?>
												</p>
											<?php
										}
									?>
								</div>
							</div>
							<?php
						}
					}
					
					/**
					 * Handling Callback
					 */
					if ( $callback && is_callable( $callback ) ) {
						call_user_func( $callback, $key, $tab );
					}

					do_action("tutor_course/settings_tab_content/after", $key, $tab);
					do_action("tutor_course/settings_tab_content/after/{$key}", $tab);

				echo "</div>";
			}
			?>
        </div>
    </div>
</div>
