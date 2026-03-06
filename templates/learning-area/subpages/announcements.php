<?php
/**
 * Tutor learning area announcements.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Announcements;
use Tutor\Components\Avatar;
use Tutor\Components\Constants\Size;
use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;

// Get course ID from global variable set in learning-area/index.php .
global $tutor_course_id;

// Pagination setup.
$limit        = (int) tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );

$args = array(
	'posts_per_page' => $limit,
	'paged'          => $current_page,
	'orderBy'        => 'ID',
	'order'          => 'DESC',
	'post_parent'    => $tutor_course_id,
);

$the_query           = Announcements::get_announcements( $args );
$announcements       = $the_query->have_posts() ? $the_query->posts : array();
$total_announcements = $the_query->found_posts;

?>
<div class="tutor-mt-7">
	<h4 class="tutor-h4 tutor-mb-4 tutor-sm-hidden tutor-flex tutor-items-center tutor-gap-1">
		<span><?php tutor_utils()->render_svg_icon( Icon::ANNOUNCEMENT, 20, 20 ); ?></span>
		<span><?php esc_html_e( 'Announcements', 'tutor-pro' ); ?></span>
	</h4>
	<div class="tutor-course-announcements tutor-mt-4 tutor-pt-4 tutor-pb-6">
		<?php if ( empty( $announcements ) ) : ?>
			<?php EmptyState::make()->title( __( 'No Announcements Found!', 'tutor' ) )->render(); ?>
		<?php else : ?>
			<div class="tutor-announcement-list">
				<?php foreach ( $announcements as $announcement ) : ?>
					<div class="tutor-announcement-item">
						<div class="tutor-medium tutor-font-medium tutor-mb-4">
							<?php echo esc_html( $announcement->post_title ); ?>
						</div>
						<div class="tutor-p2 tutor-mb-6">
							<?php echo wp_kses_post( $announcement->post_content ); ?>
						</div>
						<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mb-5">
							<div class="tutor-flex tutor-items-center tutor-gap-3">
								<?php
								$author_id   = (int) $announcement->post_author;
								$author_name = tutor_utils()->display_name( $author_id );
								?>
								<div class="tutor-avatar tutor-avatar-sm">
									<?php Avatar::make()->user( $author_id )->size( Size::SIZE_20 )->render(); ?>
								</div>
								<div class="tutor-small tutor-text-secondary">
									<?php
									// translators:%s is the author name.
									echo sprintf( esc_html__( 'By: %s', 'tutor' ), esc_html( $author_name ) );
									?>
								</div>
							</div>
							<div class="tutor-tiny tutor-text-secondary tutor-flex tutor-items-center tutor-gap-2">
								<span>
								<?php tutor_utils()->render_svg_icon( Icon::ANNOUNCEMENT ); ?>
								</span>
								<span>
								<?php echo esc_html( tutor_i18n_get_formated_date( $announcement->post_date ) ); ?>
								</span>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php
		if ( $total_announcements > $limit ) :
			Pagination::make()
				->current( $current_page )
				->total( $total_announcements )
				->limit( $limit )
				->attr( 'class', 'tutor-px-6 tutor-pb-6 tutor-sm-p-5 tutor-sm-border-t' )
				->render();
		endif;
		?>
	</div>
</div>

