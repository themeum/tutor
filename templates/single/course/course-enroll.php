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


<div class="tutor-price-preview-box">
    <div class="tutor-price-box-thumbnail">
		<?php
		if(tutor_utils()->has_video_in_single()){
			tutor_course_video();
		} else{
			get_tutor_course_thumbnail();
		}

		?>
    </div>

	<?php

	$material_includes = tutor_course_material_includes();
	if(!empty($material_includes)){
		?>
        <div class="tutor-price-box-description">
            <h6><?php esc_html_e('Material Includes', 'tutor') ?></h6>
            <ul>
				<?php if (is_array($material_includes) && count($material_includes)){
					foreach ($material_includes as $material){
						echo "<li>{$material}</li>";
					}
				} ?>
            </ul>
        </div>
		<?php
	}
	?>



    <?php tutor_single_course_add_to_cart(); ?>


</div> <!-- tutor-price-preview-box -->
