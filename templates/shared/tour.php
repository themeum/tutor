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

$tour_asset_base = 'https://tutor-lms.s3.us-east-1.amazonaws.com/tour';

$slides_data = array(
	array(
		'title'      => __( 'Your learning experience just got a whole lot better', 'tutor' ),
		'imageLarge' => $tour_asset_base . '/tour-1.webp',
		'imageSmall' => $tour_asset_base . '/tour-1-sm.webp',
	),
	array(
		'title'      => __( 'Find quiz attempts inside your courses', 'tutor' ),
		'imageLarge' => $tour_asset_base . '/tour-2.webp',
		'imageSmall' => $tour_asset_base . '/tour-2-sm.webp',
	),
	array(
		'title'      => __( 'Access all your lesson & video notes in one place', 'tutor' ),
		'imageLarge' => $tour_asset_base . '/tour-3.webp',
		'imageSmall' => $tour_asset_base . '/tour-3-sm.webp',
	),
	array(
		'title'      => __( 'Everything about your account now lives in one place', 'tutor' ),
		'imageLarge' => $tour_asset_base . '/tour-4.webp',
		'imageSmall' => $tour_asset_base . '/tour-4-sm.webp',
	),
	array(
		'title'      => __( 'Switch between dark and light mode whenever you like', 'tutor' ),
		'imageLarge' => $tour_asset_base . '/tour-5.webp',
		'imageSmall' => $tour_asset_base . '/tour-5-sm.webp',
	),
);

$tour_content_template = tutor_get_template( 'shared.tour-content' );
$tour_modal_id         = 'tutor-tour-modal';
$tour_user_id          = get_current_user_id();
$slides_json           = wp_json_encode( $slides_data );
?>

<div
	x-data="tutorTour({
		slidesData: <?php echo esc_attr( $slides_json ); ?>,
		modalId: '<?php echo esc_attr( $tour_modal_id ); ?>',
		userId: <?php echo absint( $tour_user_id ); ?>
	})"
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