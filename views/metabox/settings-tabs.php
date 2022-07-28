<?php
$args = $this->args;

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

				// Set first tab as active
				$active = $i ===1 ? 'active' : '';
				$display = $i ===1 ? 'block' : 'none' ;

				echo "<div id='settings-tab-{$key}' class='settings-tab-wrap {$active}' style='display: {$display};'>";

					do_action("tutor_course/settings_tab_content/before", $key, $tab);
					do_action("tutor_course/settings_tab_content/before/{$key}", $tab);


					if (tutor_utils()->count($fields)){
						foreach ($fields as $field_key => $field){
							$type = tutor_utils()->array_get('type', $field);
							$value = tutor_utils()->array_get('value', $field);

							if($type=='line_break') {
								echo '<hr class="tutor-mb-32"/>';
								continue;
							}
							?>
							<div class="tutor-row tutor-mb-32">
								<?php
									$second_class = 'tutor-col-12';
									$_vertical = isset($field['is_vertical']) ? $field['is_vertical'] : false;

									if (!empty($field['label'])){
										$second_class = 'tutor-col-12 '.($_vertical ? '' : 'tutor-col-md-7');
										?>
										<div class="tutor-col-12 <?php echo $_vertical ? '' : 'tutor-col-md-5'; ?>">
											<label class="tutor-course-setting-label">
												<?php echo $field['label']; ?>
											</label>
											<?php if ( isset( $field['desc'] ) && 'Content Drip Type' === $field['label'] ) { ?>
												<p class="tutor-form-feedback">
													<?php echo $field['desc']; ?>
												</p>
											<?php } ?>
										</div>
										<?php
									}
								?>
								<div class="<?php echo $second_class; ?>">
									<?php
										switch($field['type']) {
											case 'number' :
												echo '<input class="tutor-form-control" type="number" name="' . $field_key . '" value="' . $value . '"  min="0">';
												break;

												case 'radio' :
													foreach($field['options'] as $value => $label) {
														$id_string = 'course_setting_radio_' . (!empty($field['id']) ? $field['id'] : $value);
														?>
														<div class="tutor-my-20 tutor-align-center">
															<div class="tutor-form-check">
																<input type="radio" id="<?php echo $id_string; ?>" class="tutor-form-check-input tutor-flex-shrink-0" name="<?php echo $field_key; ?>" value="<?php echo $value; ?>" <?php echo $value==$field['value'] ? 'checked="checked"' : ''; ?>/>
																<label for="<?php echo $id_string; ?>" class="tutor-fs-7 tutor-fw-medium tutor-fs-6">
																	<?php echo $label; ?>
																</label>
															</div>
														</div>
														<?php
													}
													break;

											case 'checkbox' :
											case 'toggle_switch' :
												foreach($field['options'] as $option) {
													$fragment = (!empty($field['id']) ? $field['id'] : $field['type'] . '_' . $field_key);
													$id_string = 'course_setting_' . $fragment;

													if($field['type']=='checkbox') {
														?>
														<div class="tutor-form-check tutor-mb-12">
															<input id="<?php echo $id_string; ?>" type="checkbox" class="tutor-form-check-input" value="<?php echo isset($option['value']) ? $option['value'] : ''; ?>" name="<?php echo $field_key; ?>" <?php echo $option['checked'] ? 'checked="checked"' : ''; ?>/>
															<label for="<?php echo $id_string; ?>" class="tutor-fs-7 tutor-fw-medium">
																<?php echo $option['label_title']; ?>
																<?php
																	if(!empty($option['hint'])) {
																		echo '<span class="tutor-d-block tutor-fs-7">'.$option['hint'].'</span>';
																	}
																?>
															</label>
														</div>
														<?php
													} else if($field['type']=='toggle_switch') {
														?>
															<label class="tutor-form-toggle">
																<input id="<?php echo $id_string; ?>" type="checkbox" class="tutor-form-toggle-input" name="<?php echo $field_key; ?>" <?php echo $option['checked'] ? 'checked="checked"' : ''; ?>/>
																<span class="tutor-form-toggle-control"></span> <?php echo isset( $option['label_title'] ) ? $option['label_title'] : ''; ?>
															</label>
														<?php

														if(!empty($option['hint'])) {
															?>
															<div class="tutor-fs-7 tutor-has-icon tutor-color-muted tutor-d-flex tutor-mt-12">
																<i class="tutor-icon-circle-info-o tutor-mt-4 tutor-mr-8"></i>
																<?php echo $option['hint']; ?>
															</div>
															<?php
														}
													}
												}
												break;

											case 'select' :
												?>
												<select class="tutor-form-select" name="<?php echo $field_key; ?>" class="tutor_select2">
													<?php
													if ( ! empty($field['options'])){
														foreach ($field['options'] as $optionKey => $option){
															?>
															<option value="<?php echo $optionKey ?>" <?php selected($field['value'],  $optionKey) ?> >
																<?php echo $option; ?>
															</option>
															<?php
														}
													}
													?>
												</select>
												<?php
												break;
										}

										if ( isset( $field['desc'] ) && 'Content Drip Type' !== $field['label'] ) {
											?>
												<div class="tutor-fs-7 tutor-has-icon tutor-color-muted tutor-d-flex tutor-mt-12">
													<i class="tutor-icon-circle-info-o tutor-mt-4 tutor-mr-8"></i>
													<?php echo $field['desc']; ?>
												</div>
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
