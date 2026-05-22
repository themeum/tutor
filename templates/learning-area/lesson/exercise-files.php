<?php
/**
 * Lesson exercise files
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\SvgIcon;
global $tutor_current_content_id;

$attachments    = tutor_utils()->get_attachments( $tutor_current_content_id );
$open_mode_view = 'view' === apply_filters( 'tutor_pro_attachment_open_mode', null );

?>

<div x-show="activeTab === 'exercise_files'" x-cloak class="tutor-tab-panel" role="tabpanel">
	<div class="tutor-lesson-exercise-files tutor-p-6">
		<h5 class="tutor-h5 tutor-mb-4"><?php esc_html_e( 'Exercise Files', 'tutor' ); ?></h5>
		<div class="tutor-grid tutor-grid-cols-2 tutor-sm-grid-cols-1 tutor-gap-5">
			<?php foreach ( $attachments as $attachment ) : ?>
				<div class="tutor-rounded-md tutor-flex tutor-flex-col tutor-gap-2">
					<div class="tutor-card tutor-attachment-card">
						<div class="tutor-attachment-card-icon" aria-hidden="true">
							<?php SvgIcon::make()->name( Icon::RESOURCES )->render(); ?>
						</div>
						<div class="tutor-attachment-card-body">
							<div class="tutor-attachment-card-title">
								<?php echo esc_html( sprintf( '%s (%s)', $attachment->title, $attachment->ext ) ); ?>
							</div>
							<span class="tutor-attachment-card-meta">
								<?php echo esc_html( $attachment->size ); ?>			
							</span>
						</div>
						<div class="tutor-attachment-card-actions">
							<a href="<?php echo esc_url( $attachment->url ); ?>" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon" rel="noopener" <?php echo esc_attr( $open_mode_view ? 'target="_blank"' : 'download' ); ?>>
								<?php SvgIcon::make()->name( $open_mode_view ? Icon::LINK_EXTERNAL : Icon::DOWNLOAD_2 )->size( 20 )->render(); ?>
							</a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
