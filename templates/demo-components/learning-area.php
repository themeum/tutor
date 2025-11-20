<?php
/**
 * Tutor Learning Area.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>
<div class="tutor-learning-area" x-data="{ sidebarOpen: false }">
	<?php tutor_load_template( 'demo-components.learning-area.components.header' ); ?>
	<div class="tutor-learning-area-body">
		<?php tutor_load_template( 'demo-components.learning-area.components.sidebar' ); ?>
		<div class="tutor-learning-area-content">
			Learning area contents
		</div>
	</div>
</div>
