
<?php
/**
 * Main template
 *
 * @since v.1.4.3
 *
 * @author Themeum
 * @url https://themeum.com
 */

get_header();


if ( ! empty($template_part_name)){
	tutor_load_template($template_part_name);
}elseif ( ! empty($template)){
	tutor_load_template($template);
}

get_footer();
