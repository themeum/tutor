
<?php
/**
 * Main template
 *
 * @since v.1.4.3
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

tutor_utils()->tutor_custom_header();


if ( ! empty($template_part_name)){
	tutor_load_template($template_part_name);
}elseif ( ! empty($template)){
	tutor_load_template($template);
}

tutor_utils()->tutor_custom_footer();
