<?php

/**
 * Display loop thumbnail
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

global $post;
?>

<div class="dozent-loop-author">
	<?php
	global $authordata;
	?>
    <p> <a href="<?php echo dozent_utils()->profile_url($authordata->ID); ?>"><?php echo get_the_author(); ?></a> </p>
</div>
