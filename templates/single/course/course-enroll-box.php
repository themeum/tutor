<?php
/**
 * Template for displaying course content
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;


?>


<div class="dozent-price-preview-box">
    <div class="dozent-price-box-thumbnail">
		<?php
		if(dozent_utils()->has_video_in_single()){
			dozent_course_video();
		} else{
			get_dozent_course_thumbnail();
		}

		?>
    </div>
	<?php dozent_course_price(); ?>
	<?php dozent_course_material_includes_html(); ?>
    <?php dozent_single_course_add_to_cart(); ?>


</div> <!-- dozent-price-preview-box -->
