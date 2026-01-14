<?php
/**
 * Tutor dashboard Q&A card.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$is_unread = $is_unread ?? false;

?>
<div class="tutor-qna-card <?php echo esc_attr( $is_unread ? 'unread' : '' ); ?>">
	<div class="tutor-avatar tutor-avatar-32">
		<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
	</div>
	<div class="tutor-qna-card-content">
		<div class="tutor-qna-card-top">
			<div class="tutor-qna-card-author">Annathoms</div>
			<div>
				<span class="tutor-text-subdued">asked in</span> 
				<div class="tutor-preview-trigger">Camera Skills & Photo Theory</div>
			</div>
		</div>
		<h6 class="tutor-qna-card-title">This is the question posted by the student.</h6>
		<div class="tutor-qna-card-meta">
			<button class="tutor-qna-card-meta-reply-button"><?php esc_html_e( 'Reply', 'tutor' ); ?></button>
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
	<div class="tutor-qna-card-actions">
		<button class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-sm-hidden">
			<?php esc_html_e( 'Reply', 'tutor' ); ?>
		</button>
		<div x-data="tutorPopover({ placement: 'bottom-end' })" class="tutor-flex">
			<button 
				x-ref="trigger" 
				@click="toggle()" 
				class="tutor-btn tutor-btn-text tutor-btn-x-small tutor-btn-icon tutor-qna-card-actions-more">
				<?php tutor_utils()->render_svg_icon( Icon::ELLIPSES ); ?>
			</button>

			<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
				<div class="tutor-popover-menu">
					<button class="tutor-popover-menu-item">
						<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
						<?php esc_html_e( 'Mark as Unread', 'tutor' ); ?>
					</button>
					<button class="tutor-popover-menu-item">
						<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?>
						<?php esc_html_e( 'Delete', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>