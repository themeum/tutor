<?php
/**
 * Tutor quiz result modal content.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

$modal_title   = $data['title'] ?? '';
$message       = $data['message'] ?? '';
$icon_html     = $data['icon_html'] ?? '';
$modal_id      = $data['modal_id'] ?? '';
$show_attempts = isset( $data['show_attempts'] ) ? (bool) $data['show_attempts'] : false;
$action_url    = $data['action_url'] ?? '';
$action_label  = $data['action_label'] ?? __( 'View Results', 'tutor' );
?>

<div class="tutor-modal-body tutor-quiz-result-modal">
	<div class="tutor-quiz-result-modal-icon">
		<?php if ( $icon_html ) : ?>
			<?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped by tutor_utils()->get_themed_svg() ?>
		<?php endif; ?>
	</div>

	<div class="tutor-flex tutor-flex-column tutor-gap-2 tutor-py-7">
		<div class="tutor-h5 tutor-font-semibold">
			<?php echo esc_html( $modal_title ); ?>
		</div>

		<div class="tutor-p3 tutor-text-secondary">
			<?php if ( $show_attempts ) : ?>
				<?php
				printf(
					wp_kses(
						// translators: %s: message.
						__( 'You answered <strong x-text="payload?.attempted ?? 0"></strong> of <strong x-text="payload?.total ?? 0"></strong> questions. %s', 'tutor' ),
						array(
							'strong' => array(
								'x-text' => true,
							),
						)
					),
					esc_html( $message )
				);
				?>
			<?php else : ?>
				<?php echo esc_html( $message ); ?>
			<?php endif; ?>
		</div>
	</div>

	<?php
	if ( $action_url ) {
		Button::make()
			->tag( 'a' )
			->label( $action_label )
			->variant( Variant::PRIMARY )
			->size( Size::SMALL )
			->attr( 'class', 'tutor-btn-block' )
			->attr( 'href', esc_url( $action_url ) )
			->render();
	} else {
		Button::make()
			->label( __( 'OK', 'tutor' ) )
			->variant( Variant::PRIMARY )
			->size( Size::SMALL )
			->attr( 'type', 'button' )
			->attr( 'class', 'tutor-btn-block' )
			->attr( '@click', sprintf( 'TutorCore.modal.closeModal("%s")', esc_js( $modal_id ) ) )
			->render();
	}
	?>
</div>
