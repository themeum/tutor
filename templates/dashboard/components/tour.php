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
use Tutor\Helpers\UrlHelper;

$slides_data = array(
	array(
		'title'      => __( 'Your learning experience just got a whole lot better', 'tutor' ),
		'imageLarge' => UrlHelper::asset( 'images/tour/tour-1.webp' ),
		'imageSmall' => UrlHelper::asset( 'images/tour/tour-1-sm.webp' ),
	),
	array(
		'title'      => __( 'Find quiz attempts inside your courses', 'tutor' ),
		'imageLarge' => UrlHelper::asset( 'images/tour/tour-2.webp' ),
		'imageSmall' => UrlHelper::asset( 'images/tour/tour-2-sm.webp' ),
	),
	array(
		'title'      => __( 'Access all your lesson & video notes in one place', 'tutor' ),
		'imageLarge' => UrlHelper::asset( 'images/tour/tour-3.webp' ),
		'imageSmall' => UrlHelper::asset( 'images/tour/tour-3-sm.webp' ),
	),
	array(
		'title'      => __( 'Everything about your account now lives in one place', 'tutor' ),
		'imageLarge' => UrlHelper::asset( 'images/tour/tour-4.webp' ),
		'imageSmall' => UrlHelper::asset( 'images/tour/tour-4-sm.webp' ),
	),
	array(
		'title'      => __( 'Switch between dark and light mode whenever you like', 'tutor' ),
		'imageLarge' => UrlHelper::asset( 'images/tour/tour-5.webp' ),
		'imageSmall' => UrlHelper::asset( 'images/tour/tour-5-sm.webp' ),
	),
);

$tour_content_template = tutor_get_template( 'dashboard.components.tour-content' );
$tour_modal_id         = 'tutor-tour-modal';
$tour_user_id          = get_current_user_id();
$slides_json           = wp_json_encode( $slides_data );
?>

<div
	x-data="tutorTour({ slidesData: <?php echo esc_attr( $slides_json ); ?>, modalId: '<?php echo esc_js( $tour_modal_id ); ?>', userId: <?php echo esc_attr( $tour_user_id ); ?> })"
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
