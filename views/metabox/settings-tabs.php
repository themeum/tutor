<?php
/**
 * Settings meta box template
 *
 * @package Tutor\Views
 * @subpackage Tutor\MetaBox
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

$args = $this->args;

?>

<div id="tutor-metabox-course-settings-tabs" class="tutor-course-settings-tabs">
	<div class="course-settings-tabs-container">
		<div class="settings-tabs-navs-wrap">
			<ul class="settings-tabs-navs">
				<?php
				$i = 0;
				foreach ( $args as $key => $arg ) {
					$i++;

					$active = 1 === $i ? 'active' : '';

					$label      = tutor_utils()->array_get( 'label', $arg );
					$icon_class = tutor_utils()->array_get( 'icon_class', $arg );
					$url        = add_query_arg( array( 'settings_tab' => $key ) );

					$icon = '';
					if ( $icon_class ) {
						$icon = $icon_class;
					}
					?>
					<li class="<?php echo esc_attr( $active ); ?>">
						<a href="<?php echo esc_url( $url ); ?>" data-target="#settings-tab-<?php echo esc_attr( $key ); ?>">
							<i class="<?php echo esc_attr( $icon ); ?>"></i> <?php echo esc_html( $label ); ?>
						</a>
					</li>
					<?php
				}
				?>
			</ul>
		</div>

		<div class="settings-tabs-container">
			<?php

			$i = 0;

			foreach ( $args as $key => $tab ) {
				$i++;

				$label    = tutor_utils()->array_get( 'label', $tab );
				$callback = tutor_utils()->array_get( 'callback', $tab );
				$fields   = tutor_utils()->array_get( 'fields', $tab );

				// Set first tab as active.
				$active  = 1 === $i ? 'active' : '';
				$display = 1 === $i ? 'block' : 'none';
				?>

				<div id="settings-tab-<?php echo esc_attr( $key ); ?>" class="settings-tab-wrap <?php echo esc_attr( $active ); ?>" style="display: <?php echo esc_attr( $display ); ?>;">
				<?php
					do_action( 'tutor_course/settings_tab_content/before', $key, $tab );
					do_action( "tutor_course/settings_tab_content/before/{$key}", $tab );


				if ( tutor_utils()->count( $fields ) ) {
					foreach ( $fields as $field_key => $field ) {
						$type  = tutor_utils()->array_get( 'type', $field );
						$value = tutor_utils()->array_get( 'value', $field );

						if ( 'line_break' == $type ) {
							echo '<hr class="tutor-mb-32"/>';
							continue;
						}
						?>
							<div class="tutor-row tutor-mb-32 <?php if( 'Content Drip Type' === $field['label'] ): echo esc_attr( 'content-drip-options-wrapper' ); endif; ?>">
							<?php
								$second_class = 'tutor-col-12';
								$_vertical    = isset( $field['is_vertical'] ) ? $field['is_vertical'] : false;

							if ( ! empty( $field['label'] ) ) {
								$second_class = 'tutor-col-12 ' . ( $_vertical ? '' : 'tutor-col-md-7' );
								?>
										<div class="tutor-col-12 <?php echo esc_attr( $_vertical ? '' : 'tutor-col-md-5' ); ?>">
											<label class="tutor-course-setting-label">
										<?php echo esc_html( $field['label'] ); ?>
											</label>
									<?php if ( isset( $field['desc'] ) && 'Content Drip Type' === $field['label'] ) { ?>
												<p class="tutor-form-feedback">
													<?php echo esc_html( $field['desc'] ); ?>
												</p>
											<?php } ?>
										</div>
									<?php
							}
							?>
								<div class="<?php echo esc_attr( $second_class ); ?>">
								<?php
								switch ( $field['type'] ) {
									case 'number':
										echo '<input class="tutor-form-control" type="number" name="' . esc_attr( $field_key ) . '" value="' . esc_attr( $value ) . '"  min="0">';
										break;

									case 'radio':
										foreach ( $field['options'] as $value => $label ) {
											$id_string = 'course_setting_radio_' . ( ! empty( $field['id'] ) ? $field['id'] : $value );
											?>
														<div class="tutor-my-20 tutor-align-center">
															<div class="tutor-form-check">
																<input type="radio" id="<?php echo esc_attr( $id_string ); ?>" class="tutor-form-check-input tutor-flex-shrink-0" name="<?php echo esc_attr( $field_key ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo $value == $field['value'] ? 'checked="checked"' : ''; ?>/>
																<label for="<?php echo esc_attr( $id_string ); ?>" class="tutor-fs-7 tutor-fw-medium tutor-fs-6">
														<?php echo esc_attr( $label ); ?>
																</label>
															</div>
														</div>
												<?php
										}
										break;

									case 'checkbox':
									case 'toggle_switch':
										foreach ( $field['options'] as $option ) {
											$fragment  = ( ! empty( $field['id'] ) ? $field['id'] : $field['type'] . '_' . $field_key );
											$id_string = 'course_setting_' . $fragment;

											if ( $field['type'] == 'checkbox' ) {
												?>
														<div class="tutor-form-check tutor-mb-12">
															<input id="<?php echo esc_attr( $id_string ); ?>" type="checkbox" class="tutor-form-check-input" value="<?php echo isset( $option['value'] ) ? esc_attr( $option['value'] ) : ''; ?>" name="<?php echo esc_attr( $field_key ); ?>" <?php echo $option['checked'] ? 'checked="checked"' : ''; ?>/>
															<label for="<?php echo esc_attr( $id_string ); ?>" class="tutor-fs-7 tutor-fw-medium">
														<?php echo esc_html( $option['label_title'] ); ?>
														<?php
														if ( ! empty( $option['hint'] ) ) {
															echo '<span class="tutor-d-block tutor-fs-7">' . esc_html( $option['hint'] ) . '</span>';
														}
														?>
															</label>
														</div>
														<?php
											} elseif ( 'toggle_switch' == $field['type'] ) {
												?>
															<label class="tutor-form-toggle">
																<input id="<?php echo esc_attr( $id_string ); ?>" type="checkbox" class="tutor-form-toggle-input" name="<?php echo esc_attr( $field_key ); ?>" <?php echo $option['checked'] ? 'checked="checked"' : ''; ?>/>
																<span class="tutor-form-toggle-control"></span> <?php echo isset( $option['label_title'] ) ? esc_attr( $option['label_title'] ) : ''; ?>
															</label>
													<?php

													if ( ! empty( $option['hint'] ) ) {
														?>
															<div class="tutor-fs-7 tutor-has-icon tutor-color-muted tutor-d-flex tutor-mt-12">
																<i class="tutor-icon-circle-info-o tutor-mt-4 tutor-mr-8"></i>
															<?php echo esc_html( $option['hint'] ); ?>
															</div>
															<?php
													}
											}
										}
										break;

									case 'select':
										?>
												<select class="tutor-form-select" name="<?php echo esc_attr( $field_key ); ?>" class="tutor_select2">
											<?php
											if ( ! empty( $field['options'] ) ) {
												foreach ( $field['options'] as $option_key => $option ) {
													?>
															<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $field['value'], $option_key ); ?> >
															<?php echo esc_html( $option ); ?>
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
											<?php echo esc_html( $field['desc'] ); ?>
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

					do_action( 'tutor_course/settings_tab_content/after', $key, $tab );
					do_action( "tutor_course/settings_tab_content/after/{$key}", $tab );

				echo '</div>';
			}
			?>
		</div>
	</div>
</div>
