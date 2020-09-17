
<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for=""><?php _e('Title', 'tutor'); ?></label>
    </div>
    <div class="tutor-option-field">
        <input type="text" name="announcements[title]" value="">
    </div>
</div>

<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for=""><?php _e('Announcements', 'tutor'); ?></label>
    </div>
    <div class="tutor-option-field">
        <div class="tutor-announcement-editor tutor-course-builder-form-elem">
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
        </div>
        <p class="desc"><?php _e('available variable', 'tutor'); ?>, {user_display_name}</p>
    </div>
</div>
<div class="tutor-option-field-row">
    <div class="submit">
        <button type="submit" name="submit" id="submit" class="tutor-btn bordered-btn"><i class="tutor-icon-speaker"></i><?php _e('Add Announcement', 'tutor'); ?></button>
    </div>
</div>


<?php
$announcements = tutor_utils()->get_announcements(get_the_ID());
if (is_array($announcements) && count($announcements)){
	?>
    <div class="tutor-announcements-wrap">
		<?php
		foreach ($announcements as $announcement){
			?>
            <div class="tutor-announcement">
                <div class="tutor-announcement-title-wrap">
                    <h3><?php echo $announcement->post_title; ?>

                        <span class="announcement-delete-btn">
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?action=tutor_delete_announcement&topic_id='.$announcement->ID), tutor()->nonce_action, tutor()->nonce); ?>" title="<?php _e('Delete Announcement'); ?>">
                                <i class="tutor-icon-garbage"></i>
                            </a>
                        </span>
                    </h3>
                </div>

                <div class="tutor-announcement-meta text-muted">
					<?php _e( sprintf("Posted by %s, at %s ago", 'admin', human_time_diff(strtotime($announcement->post_date)) ) , 'tutor' ); ?>
                </div>

                <div class="tutor-announcement-content">
					<?php echo tutor_utils()->announcement_content(wpautop(stripslashes($announcement->post_content))); ?>
                </div>
            </div>

			<?php
		}
		?>
    </div>
	<?php
}
?>
