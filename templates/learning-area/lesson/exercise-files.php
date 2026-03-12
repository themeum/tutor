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
global $tutor_current_content_id;

$attachments = tutor_utils()->get_attachments( $tutor_current_content_id );

?>

<div x-show="activeTab === 'exercise_files'" x-cloak class="tutor-tab-panel" role="tabpanel">
	<div class="tutor-lesson-exercise-files tutor-p-6">
		<h5 class="h5 tutor-mb-4"><?php esc_html_e( 'Exercise Files', 'tutor' ); ?></h5>
		<div class="tutor-resources-list">
			<?php foreach ( $attachments as $attachment ) : ?>
				<div class="tutor-rounded-md tutor-flex tutor-flex-col tutor-gap-2">
					<div class="tutor-card tutor-attachment-card">
						<div class="tutor-attachment-card-icon" aria-hidden="true">
							<?php tutor_utils()->render_svg_icon( Icon::RESOURCES ); ?>
						</div>
						<div class="tutor-attachment-card-body">
							<div class="tutor-attachment-card-title">
								<?php echo esc_html( $attachment->title ); ?>
							</div>
							<span class="tutor-attachment-card-meta">
							<?php echo esc_html( $attachment->file_size ); ?>			
							</span>
						</div>
						<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
							<?php tutor_utils()->render_svg_icon( Icon::DOWNLOAD_2 ); ?>
						</button>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
