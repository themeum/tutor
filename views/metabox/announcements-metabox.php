
<div class="dozent-option-field-row">
    <div class="dozent-option-field-label">
        <label for=""><?php _e('Title', 'dozent'); ?></label>
    </div>
    <div class="dozent-option-field">
        <input type="text" name="announcements[title]" value="">
    </div>
</div>

<div class="dozent-option-field-row">
    <div class="dozent-option-field-label">
        <label for=""><?php _e('Announcements', 'dozent'); ?></label>
    </div>
    <div class="dozent-option-field">
		<?php
		$editor_settings = array(
			'teeny'         => true,
			'media_buttons' => false,
			'quicktags'     => false,
			'editor_height' => 150,
			'textarea_name' => 'announcements[content]'
		);
		wp_editor(null, 'announcements_content', $editor_settings);
		?>

        <p class="desc"><?php _e('available variable', 'dozent'); ?>, {user_display_name}</p>

		<?php
		submit_button(__('Add Announcement', 'dozent')); ?>
    </div>
</div>




<?php
$announcements = dozent_utils()->get_announcements(get_the_ID());
if (is_array($announcements) && count($announcements)){
	?>
    <div class="dozent-announcements-wrap">
		<?php
		foreach ($announcements as $announcement){
			?>
            <div class="dozent-announcement">
                <div class="dozent-announcement-title-wrap">
                    <h3><?php echo $announcement->post_title; ?>

                        <span class="announcement-delete-btn">
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?action=dozent_delete_announcement&topic_id='.$announcement->ID), dozent()->nonce_action, dozent()->nonce); ?>" title="<?php _e('Delete Announcement'); ?>">
                                <i class="dashicons dashicons-trash"></i>
                            </a>
                        </span>
                    </h3>
                </div>

                <div class="dozent-announcement-meta text-muted">
					<?php _e( sprintf("Posted by %s, at %s ago", 'admin', human_time_diff(strtotime($announcement->post_date)) ) , 'dozent' ); ?>
                </div>

                <div class="dozent-announcement-content">
					<?php echo dozent_utils()->announcement_content(wpautop(stripslashes($announcement->post_content))); ?>
                </div>
            </div>

			<?php
		}
		?>
    </div>
	<?php
}
?>
