<div class="wrap">
    <h1><?php _e('Dozent Settings'); ?></h1>


    <form id="dozent-option-form" class="dozent-option-form" method="post">
        <?php wp_nonce_field('dozent_option_save') ?>
        <input type="hidden" name="action" value="dozent_option_save" >

		<?php
		$options_attr = $this->options_attr;

		if (is_array($options_attr) && count($options_attr)){
			$first_item = null;
			?>
            <ul class="dozent-option-nav-tabs">
				<?php
				foreach ($options_attr as $key => $option_group){
					if (empty($option_group)){
						continue;
					}
					if ( ! $first_item){
						$first_item = $key;
					}
					$is_first_item = ($first_item === $key);
					$current_class = $is_first_item ? 'current' : '';

					echo "<li class='option-nav-item {$current_class}'><a href='#{$key}' class='dozent-option-nav-item'>{$option_group['label']}</a> </li>";
				}
				?>
            </ul>

			<?php

			foreach ($options_attr as $key => $option_group){
				if (empty($option_group)){
					continue;
				}
				$is_first_item = ($first_item === $key);
				?>

                <div id="<?php echo $key; ?>" class="dozent-option-nav-page <?php echo $is_first_item ? 'current-page' : ''; ?> " style="display: <?php echo $is_first_item ? 'block' : 'none' ?>;" >
                    <!--<h3><?php /*echo $option_group['label']; */?></h3>-->

					<?php
					if (!empty($option_group['sections'])){
						foreach ($option_group['sections'] as $fgKey => $field_group){
							?>

                            <div class="dozent-option-field-row">
                                <h2><?php echo $field_group['label']; ?></h2>
                            </div>

							<?php
							foreach ($field_group['fields'] as $field_key => $field){
								$field['field_key'] = $field_key;
								echo $this->generate_field($field);
							}
						}
					}
					?>
                </div>
				<?php
			}
		}

		?>

        <p class="submit">
            <button type="button" id="save_dozent_option" class="button button-primary"><?php echo __('Save Settings', 'dozent') ?></button>
        </p>
    </form>


</div>


