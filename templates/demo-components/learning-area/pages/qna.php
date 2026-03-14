<?php
/**
 * Tutor learning area Q&A.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>
<div class="tutor-learning-area-qna tutor-mb-9">
	<div class="tutor-discussion-search tutor-p-6 tutor-border-b">
		<div class="tutor-input-field">
			<div class="tutor-input-wrapper">
				<!-- @TODO: Input size lg need to apply -->
				<input 
					type="text"
					placeholder="<?php esc_attr_e( 'Search questions, topics...', 'tutor' ); ?>"
					class="tutor-input tutor-input-content-left tutor-input-content-clear"
				>
				<div class="tutor-input-content tutor-input-content-left">
					<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::SEARCH_2, 20, 20 ) ); ?>
				</div>
				<button 
					type="button"
					class="tutor-input-clear-button"
					aria-label="<?php esc_attr_e( 'Clear input', 'tutor' ); ?>"
				>
					<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
				</button>
			</div>
		</div>
	</div>
	<form class="tutor-discussion-form tutor-p-6 tutor-border-b" x-data="{ focused: false }">
		<div class="tutor-input-field">
			<label for="name" class="tutor-block tutor-medium tutor-font-semibold tutor-mb-4">Question & Answer</label>
			<div class="tutor-input-wrapper">
				<textarea 
					type="text"
					id="name"
					placeholder="<?php esc_attr_e( 'Asked questions...', 'tutor' ); ?>"
					class="tutor-input tutor-text-area"
					@focus="focused = true"
				></textarea>
			</div>
		</div>
		<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mt-5" x-cloak :class="{ 'tutor-hidden': !focused }">
			<div class="tutor-tiny tutor-text-subdued tutor-flex tutor-items-center tutor-gap-2">
				<?php tutor_utils()->render_svg_icon( Icon::COMMAND, 12, 12 ); ?> 
				<?php esc_html_e( 'Cmd/Ctrl +', 'tutor' ); ?>
				<?php tutor_utils()->render_svg_icon( Icon::ENTER, 12, 12 ); ?> 
				<?php esc_html_e( 'Enter to Save	', 'tutor' ); ?>
			</div>
			<div class="tutor-flex tutor-items-center tutor-gap-4">
				<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-x-small" @click="focused = false">
					<?php esc_html_e( 'Cancel', 'tutor' ); ?>
				</button>
				<button type="button" class="tutor-btn tutor-btn-primary-soft tutor-btn-x-small">
					<?php esc_html_e( 'Save', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</form>
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
	<div class="tutor-discussion-list tutor-flex tutor-flex-column tutor-gap-4 tutor-p-6">
		<div class="tutor-discussion-card is-important">
			<?php
			tutor_utils()->render_svg_icon(
				Icon::BOOKMARK,
				20,
				20,
				array( 'class' => 'tutor-discussion-card-bookmark' )
			);
			?>
			<div class="tutor-avatar tutor-avatar-32">
				<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
			</div>
			<div class="tutor-discussion-card-content">
				<div class="tutor-discussion-card-top">
					<div class="tutor-discussion-card-author">Annathoms</div>
					<div>
						<span class="tutor-text-subdued">asked in</span> 
						<div class="tutor-preview-trigger">Camera Skills & Photo Theory</div>
					</div>
				</div>
				<h6 class="tutor-discussion-card-title">This is the question posted by the student.</h6>
				<div class="tutor-discussion-card-meta">
					<button class="tutor-discussion-card-meta-reply-button"><?php esc_html_e( 'Reply', 'tutor' ); ?></button>
					<div class="tutor-flex tutor-items-center tutor-gap-2"><?php tutor_utils()->render_svg_icon( Icon::THUMB, 20, 20 ); ?> 0</div>
					<div class="tutor-flex tutor-items-center tutor-gap-2"><?php tutor_utils()->render_svg_icon( Icon::EYE_LINE, 20, 20 ); ?> 6</div>
					<div class="tutor-flex tutor-items-center tutor-gap-2"><?php tutor_utils()->render_svg_icon( Icon::COMMENTS, 20, 20 ); ?> 3</div>
					<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-sm-ml-2">
						<div class="tutor-avatar tutor-avatar-20">
							<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
						</div>
						<div class="tutor-text-small">1 minute ago</div>
					</div>
				</div>
			</div>
		</div>
		<div class="tutor-discussion-card">
			<div class="tutor-avatar tutor-avatar-32">
				<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
			</div>
			<div class="tutor-discussion-card-content">
				<div class="tutor-discussion-card-top">
					<div class="tutor-discussion-card-author">Annathoms</div>
					<div>
						<span class="tutor-text-subdued">asked in</span> 
						<div class="tutor-preview-trigger">Camera Skills & Photo Theory</div>
					</div>
				</div>
				<h6 class="tutor-discussion-card-title">This is the question posted by the student.</h6>
				<div class="tutor-discussion-card-meta">
					<button class="tutor-discussion-card-meta-reply-button"><?php esc_html_e( 'Reply', 'tutor' ); ?></button>
					<div class="tutor-flex tutor-items-center tutor-gap-2"><?php tutor_utils()->render_svg_icon( Icon::THUMB, 20, 20 ); ?> 0</div>
					<div class="tutor-flex tutor-items-center tutor-gap-2"><?php tutor_utils()->render_svg_icon( Icon::EYE_LINE, 20, 20 ); ?> 6</div>
					<div class="tutor-flex tutor-items-center tutor-gap-2"><?php tutor_utils()->render_svg_icon( Icon::COMMENTS, 20, 20 ); ?> 3</div>
					<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-sm-ml-2">
						<div class="tutor-avatar tutor-avatar-20">
							<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
						</div>
						<div class="tutor-text-small">1 minute ago</div>
					</div>
				</div>
			</div>
		</div>
		<div class="tutor-discussion-card">
			<div class="tutor-avatar tutor-avatar-32">
				<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
			</div>
			<div class="tutor-discussion-card-content">
				<div class="tutor-discussion-card-top">
					<div class="tutor-discussion-card-author">Annathoms</div>
					<div>
						<span class="tutor-text-subdued">asked in</span> 
						<div class="tutor-preview-trigger">Camera Skills & Photo Theory</div>
					</div>
				</div>
				<h6 class="tutor-discussion-card-title">This is the question posted by the student.</h6>
				<div class="tutor-discussion-card-meta">
					<button class="tutor-discussion-card-meta-reply-button"><?php esc_html_e( 'Reply', 'tutor' ); ?></button>
					<div class="tutor-flex tutor-items-center tutor-gap-2"><?php tutor_utils()->render_svg_icon( Icon::THUMB, 20, 20 ); ?> 0</div>
					<div class="tutor-flex tutor-items-center tutor-gap-2"><?php tutor_utils()->render_svg_icon( Icon::EYE_LINE, 20, 20 ); ?> 6</div>
					<div class="tutor-flex tutor-items-center tutor-gap-2"><?php tutor_utils()->render_svg_icon( Icon::COMMENTS, 20, 20 ); ?> 3</div>
					<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-sm-ml-2">
						<div class="tutor-avatar tutor-avatar-20">
							<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
						</div>
						<div class="tutor-text-small">1 minute ago</div>
					</div>
				</div>
			</div>
		</div>
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

<?php tutor_load_template( 'demo-components.learning-area.pages.qna-single' ); ?>
