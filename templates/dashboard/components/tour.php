<?php
/**
 * Frontend Dashboard Tour Component
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Modal;

$slides_data = array(
	array(
		'title'      => __( 'Your learning experience just got a whole lot better', 'tutor' ),
		'imageLarge' => 'https://placehold.co/568x366/E2E8F0/A0AEC0?text=Large',
		'imageSmall' => 'https://placehold.co/324x366/E2E8F0/A0AEC0?text=Small',
	),
	array(
		'title'      => __( 'Find quiz attempts inside your courses', 'tutor' ),
		'imageLarge' => 'https://placehold.co/568x366/E2E8F0/A0AEC0?text=Large',
		'imageSmall' => 'https://placehold.co/324x366/E2E8F0/A0AEC0?text=Small',
	),
	array(
		'title'      => __( 'Access all your lesson & video notes in one place', 'tutor' ),
		'imageLarge' => 'https://placehold.co/568x366/E2E8F0/A0AEC0?text=Large',
		'imageSmall' => 'https://placehold.co/324x366/E2E8F0/A0AEC0?text=Small',
	),
	array(
		'title'      => __( 'Everything about your account now lives in one place', 'tutor' ),
		'imageLarge' => 'https://placehold.co/568x366/E2E8F0/A0AEC0?text=Large',
		'imageSmall' => 'https://placehold.co/324x366/E2E8F0/A0AEC0?text=Small',
	),
	array(
		'title'      => __( 'Switch between dark and light mode whenever you like', 'tutor' ),
		'imageLarge' => 'https://placehold.co/568x366/E2E8F0/A0AEC0?text=Large',
		'imageSmall' => 'https://placehold.co/324x366/E2E8F0/A0AEC0?text=Small',
	),
);

$tour_content_template = tutor_get_template( 'dashboard.components.tour-content' );
$tour_modal_id         = 'tutor-tour-modal';
$slides_json           = wp_json_encode( $slides_data );
?>

<div
	x-data="tutorTour({ slidesData: <?php echo esc_attr( $slides_json ); ?>, modalId: '<?php echo esc_js( $tour_modal_id ); ?>' })"
>
	<?php
		Modal::make()
			->id( $tour_modal_id )
			->closeable( false )
			->width( '600px' )
			->template( $tour_content_template )
			->render();
	?>

</div>
