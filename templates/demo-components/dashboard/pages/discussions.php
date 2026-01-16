<?php
/**
 * Tutor dashboard discussions.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$current_url = admin_url( 'admin.php?page=playground&subpage=dashboard' );

$page_nav_items = array(
	array(
		'type'   => 'link',
		'label'  => __( 'Q&A', 'tutor' ),
		'icon'   => Icon::QA,
		'url'    => esc_url( add_query_arg( 'dashboard-page', 'qna', $current_url ) ),
		'active' => true,
	),
	array(
		'type'   => 'link',
		'label'  => __( 'Lesson Comments', 'tutor' ),
		'icon'   => Icon::COMMENTS,
		'url'    => esc_url( add_query_arg( 'dashboard-page', 'lesson-comments', $current_url ) ),
		'active' => false,
	),
);

?>
<div class="tutor-dashboard-discussions tutor-surface-l1 tutor-border tutor-rounded-2xl">
	<?php
	tutor_load_template(
		'demo-components.dashboard.components.page-nav',
		array( 'items' => $page_nav_items )
	);
	?>
	<div class="tutor-flex tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
		<div class="tutor-small tutor-text-secondary">
			<?php esc_html_e( 'Questions', 'tutor' ); ?>
			<span class="tutor-text-primary tutor-font-medium">(3234)</span>
		</div>
		<div class="tutor-sm-border tutor-sm-rounded-2xl tutor-sm-mt-4">
			<div class="tutor-flex tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
				<div class="tutor-small tutor-text-secondary">
					<?php esc_html_e( 'Questions', 'tutor' ); ?>
					<span class="tutor-text-primary tutor-font-medium">(3234)</span>
				</div>
				<div class="tutor-discussion-filter-right">
					<button class="tutor-btn tutor-btn-outline tutor-btn-x-small tutor-gap-4 tutor-pr-3">
						<?php esc_html_e( 'Newest First', 'tutor' ); ?>
						<?php
						tutor_utils()->render_svg_icon(
							Icon::STEPPER,
							16,
							16,
							array( 'class' => 'tutor-icon-secondary' )
						);
						?>
					</button>
				</div>
			</div>
			<div class="tutor-flex tutor-flex-column tutor-gap-4 tutor-p-6">
				<?php
				tutor_load_template(
					'demo-components.dashboard.components.qna-card',
					array(
						'is_unread' => true,
					)
				);
				?>
				<?php
				tutor_load_template(
					'demo-components.dashboard.components.qna-card',
					array(
						'is_unread' => false,
					)
				);
				?>
			</div>
			<div class="tutor-px-6 tutor-pb-6">
				<nav class="tutor-pagination" role="navigation" aria-label="Pagination Navigation">
					<span class="tutor-pagination-info" aria-live="polite">
						Page <span class="tutor-pagination-current">3</span> of <span class="tutor-pagination-total">12</span>
					</span>

					<ul class="tutor-pagination-list">
						<li>
							<a class="tutor-pagination-item tutor-pagination-item-prev" aria-label="Previous page" aria-disabled="true">
								<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_LEFT_2 ); ?>
							</a>
						</li>

						<li><a class="tutor-pagination-item">1</a></li>
						<li>
							<a class="tutor-pagination-item tutor-pagination-item-active" aria-current="page">2</a>
						</li>
						<li><a class="tutor-pagination-item">3</a></li>
						<li><span class="tutor-pagination-ellipsis" aria-hidden="true">…</span></li>
						<li><a class="tutor-pagination-item">6</a></li>
						<li><a class="tutor-pagination-item">7</a></li>

						<li>
							<a class="tutor-pagination-item tutor-pagination-item-next" aria-label="Next page">
								<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_RIGHT_2 ); ?>
							</a>
						</li>
					</ul>
				</nav>
			</div>
		</div>
	</div>
</div>
