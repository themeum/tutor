<?php
/**
 * @package @DOZENT
 * @since v.1.0.0
 */
?>

<div class="wrap dozent-uninstall-wrap">
	<h2><?php _e('Uninstall Dozent', 'dozent'); ?></h2>
    <p class="desc"><?php _e('Just deactive dozent plugin or completely uninstall and erase all of data saved before by dozent.', 'dozent'); ?></p>

    <div class="dozent-uninstall-btn-group">
        <?php $plugin_file = dozent()->basename; ?>

        <a href="<?php echo wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . urlencode( $plugin_file ) . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'deactivate-plugin_' . $plugin_file ); ?>" class="dozent-button button-warning"> Deactive </a>

        <a href="admin.php?action=uninstall_dozent_and_erase" class="dozent-button button-danger"> Completely Uninstall and erase all data </a>
    </div>
</div>