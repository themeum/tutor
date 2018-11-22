<?php
/**
 * Announcements
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */


$announcements = dozent_utils()->get_announcements(get_the_ID());
?>

<?php do_action('dozent_course/announcements/before'); ?>
<div class="dozent-announcements-wrap">
	<?php
	if (is_array($announcements) && count($announcements)){
		?>
		<?php
		foreach ($announcements as $announcement){
			?>
            <div class="dozent-announcement">
                <div class="dozent-announcement-title-wrap">
                    <h3><?php echo $announcement->post_title; ?></h3>
                </div>

                <div class="dozent-announcement-meta dozent-text-mute">
					<?php _e( sprintf("Posted by %s, at %s ago", 'admin', human_time_diff(strtotime($announcement->post_date)) ) , 'dozent' ); ?>
                </div>

                <div class="dozent-announcement-content">
					<?php echo dozent_utils()->announcement_content(wpautop(stripslashes($announcement->post_content))); ?>
                </div>
            </div>
			<?php
		}
		?>
		<?php
	}else{
		?>
        <div class="dozent-no-announcements">
            <h2><?php _e('No announcements posted yet.', 'dozent'); ?></h2>
            <p>
				<?php _e('The teacher hasn’t added any announcements to this course yet. Announcements are used to inform you of updates or additions to the course.', 'dozent'); ?>
            </p>
        </div>

		<?php
	}
	?>
</div>

<?php do_action('dozent_course/announcements/after'); ?>