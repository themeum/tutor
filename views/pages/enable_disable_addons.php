<div class="wrap">
    <h2><?php _e('Addons', 'tutor'); ?></h2>

    <div class="tutor-addons-list">
        <h3 class="addon-list-heading"><?php _e('Addons List', 'tutor'); ?></h3>

        <br class="clear">
		<?php
		$addons = apply_filters('tutor_addons_lists_config', array());


		//tutor_utils()->print_view($addons);

		if (is_array($addons) && count($addons)){
			?>

            <!--
            <table class="tutor-addons-list-table">
                <tr>
                    <th>
						<?php /*_e('Addon Name', 'tutor'); */?>
                    </th>
                    <th>
						<?php /*_e('Enable/Disable', 'tutor'); */?>
                    </th>
                </tr>

				<?php
/*				foreach ($addons as $addon){
					$addonConfig = tutor_utils()->get_addon_config($addon['field_name']);
					$isEnable = (bool) tutor_utils()->avalue_dot('is_enable', $addonConfig);
					*/?>
                    <tr>
                        <td>
							<?php /*echo $addon['name']; */?>
                        </td>
                        <td>
                            <label class="btn-switch">
                                <input type="checkbox" class="tutor_addons_list_item" value="1" name="<?php /*echo $addon['field_name']; */?>" <?php /*checked(true, $isEnable) */?> />
                                <div class="btn-slider btn-round"></div>
                            </label>
                        </td>
                    </tr>
				<?php /*} */?>
            </table>
-->

            <div class="wp-list-table widefat plugin-install">
                <div id="the-list">
					<?php
					foreach ( $addons as $basName => $addon ) {
						$addonConfig = tutor_utils()->get_addon_config($basName);
						$isEnable = (bool) tutor_utils()->avalue_dot('is_enable', $addonConfig);

						$thumbanil_url = file_exists($addon['path'].'assets/images/thumbnail.png') ? $addon['url'].'assets/images/thumbnail.png':tutor()->url.'assets/images/tutor-plugin.png';

						?>
                        <div class="plugin-card plugin-card-akismet">
                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
										<?php
										echo $addon['name'];
										echo "<img src='{$thumbanil_url}' class='plugin-icon' alt=''>";
										?>
                                    </h3>
                                </div>
                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li>
                                            <label class="btn-switch">
                                                <input type="checkbox" class="tutor_addons_list_item" value="1" name="<?php echo $basName; ?>" <?php checked(true, $isEnable) ?> />
                                                <div class="btn-slider btn-round"></div>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                                <div class="desc column-description">
                                    <p><?php echo $addon['description']; ?></p>

                                    <p class="authors"><cite>By <a href="https://www.themeum.com" target="_blank">Themeum</a></cite></p>
                                </div>
                            </div>
                            <div class="plugin-card-bottom">
								<?php
								echo "<div class='plugin-version'> " . __( 'Version', 'tutor' ) . " : {$addon['version']}</div>";
								?>
                            </div>
                        </div>
					<?php }
					?>
                </div>

            </div>

            <br class="clear">

			<?php
		}
		?>
    </div>
</div>