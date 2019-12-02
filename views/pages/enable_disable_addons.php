<div class="wrap">
    <div class="tutor-addons-list">
        <h3 class="addon-list-heading"><?php _e('Addons List', 'tutor'); ?></h3>
        <br class="clear">
		<?php
		$addons = apply_filters('tutor_addons_lists_config', array());

		if (is_array($addons) && count($addons)){
			?>
            <div class="wp-list-table widefat plugin-install">
                <div id="the-list">
					<?php
					foreach ( $addons as $basName => $addon ) {
						$addonConfig = tutor_utils()->get_addon_config($basName);
						$isEnable = (bool) tutor_utils()->avalue_dot('is_enable', $addonConfig);

						$thumbnailURL =  tutor()->url.'assets/images/tutor-plugin.png';
						if (file_exists($addon['path'].'assets/images/thumbnail.png') ){
							$thumbnailURL = $addon['url'].'assets/images/thumbnail.png';
                        }elseif (file_exists($addon['path'].'assets/images/thumbnail.jpg') ){
							$thumbnailURL = $addon['url'].'assets/images/thumbnail.jpg';
						}elseif (file_exists($addon['path'].'assets/images/thumbnail.svg')){
							$thumbnailURL = $addon['url'].'assets/images/thumbnail.svg';
						}

						/**
						 * Checking if there any depend plugin exists
						 */
						$depends = tutils()->array_get('depend_plugins', $addon);
						$plugins_required = array();
						if (tutils()->count($depends)){
							foreach ($depends as $plugin_base => $plugin_name){
								if ( ! is_plugin_active($plugin_base)){
									$plugins_required[$plugin_base] = $plugin_name;
								}
							}
						}
						?>
                        <div class="plugin-card plugin-card-akismet">
                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
										<?php
										echo $addon['name'];
										echo "<img src='{$thumbnailURL}' class='plugin-icon' alt=''>";
										?>
                                    </h3>
                                </div>

	                            <?php if ( ! tutils()->count($plugins_required)) { ?>
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
                                <?php } ?>

                                <div class="desc column-description">
                                    <p><?php echo $addon['description']; ?></p>
                                    <p class="authors"><cite>By <a href="https://www.themeum.com" target="_blank">Themeum</a></cite></p>
                                </div>
                            </div>

	                        <?php
	                        if (tutils()->count($plugins_required)) {
		                        ?>
                                <div class="required-plugin-cards">
                                    <p>
                                        <strong><?php _e('Required Plugin(s)', 'tutor'); ?></strong><br/>
	                                    <?php echo implode( ", ", $plugins_required ) ?>
                                    </p>
                                </div>
		                        <?php
	                        }
	                        ?>

                            <div class="plugin-card-bottom">
								<?php
								echo "<div class='plugin-version'> " . __( 'Version', 'tutor' ) . " : ".TUTOR_VERSION." </div>";
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