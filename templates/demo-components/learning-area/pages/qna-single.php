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
<div class="tutor-learning-area-qna-single">
	<div class="tutor-qna-single-header tutor-p-6 tutor-border-b">
		<button type="button" class="tutor-btn tutor-btn-secondary tutor-btn-small tutor-gap-2">
			<?php tutor_utils()->render_svg_icon( Icon::ARROW_LEFT_2 ); ?>
			<?php esc_html_e( 'Back', 'tutor' ); ?>
		</button>
	</div>
	<div class="tutor-qna-single-body tutor-p-6 tutor-border-b">
		<div class="tutor-flex tutor-gap-6 tutor-mb-5">
			<div class="tutor-avatar tutor-avatar-40">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>
			<div>
				<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-mb-2 tutor-small">
					<span class="tutor-qna-card-author">Annathoms</span> 
					<span class="tutor-text-secondary">2 days ago</span>
				</div>
				<div>
					<span class="tutor-text-secondary">asked in</span> 
					<div class="tutor-preview-trigger">Camera Skills & Photo Theory</div>
				</div>
			</div>
			<div class="tutor-ml-auto">
				<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
					<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL ); ?>
				</button>
			</div>
		</div>
		<div class="tutor-p1 tutor-font-medium tutor-text-secondary">Blocked by “Verification Limit Exceeded” on SheerID (Figma Education), Blocked by “Verification Limit Exceeded” on SheerID (Figma Education)Blocked by “Verification Limit Exceeded” on SheerID (Figma Education)Blocked by “Verification Limit Exceeded” on SheerID (Figma Education)Blocked by “Verification Limit Exceeded” on SheerID (Figma Education)Blocked by “Verification Limit Exceeded” on SheerID (Figma Education)</div>
	</div>
	<div class="tutor-qna-single-reply-count">
		<div class="tutor-flex tutor-items-center tutor-gap-6">
			<div class="tutor-flex tutor-items-center tutor-gap-2">
				<button class="tutor-qna-thumb-button">
					<?php tutor_utils()->render_svg_icon( Icon::THUMB_FILL, 20, 20, array( 'class' => 'tutor-icon-secondary' ) ); ?>
				</button>
				5
			</div>
			<div class="tutor-flex tutor-items-center tutor-gap-2">
				<?php tutor_utils()->render_svg_icon( Icon::COMMENTS, 20, 20, array( 'class' => 'tutor-icon-secondary' ) ); ?>
				4
			</div>
		</div>
		<div class="tutor-flex tutor-items-center tutor-gap-4">
			<div class="tutor-qna-avatar-list">
				<div class="tutor-avatar tutor-avatar-24">
					<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
				</div>
				<div class="tutor-avatar tutor-avatar-24">
					<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
				</div>
				<div class="tutor-avatar tutor-avatar-24">
					<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
				</div>
			</div>
			& 5 people like this
		</div>
	</div>
	<form class="tutor-qna-single-reply-form tutor-p-6 tutor-border-b" x-data="{ focused: false }">
		<div class="tutor-input-field">
			<label for="name" class="tutor-block tutor-medium tutor-font-semibold tutor-mb-4">Reply</label>
			<div class="tutor-input-wrapper">
				<textarea 
					type="text"
					id="name"
					placeholder="<?php esc_attr_e( 'Just drop your response here!', 'tutor' ); ?>"
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
			<?php esc_html_e( 'Replies', 'tutor' ); ?>
			<span class="tutor-text-primary tutor-font-medium">(4)</span>
		</div>
		<div class="tutor-qna-filter-right">
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
	<div class="tutor-qna-single-reply-list">
		<div class="tutor-qna-reply-list-item">
			<div class="tutor-avatar tutor-avatar-40">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>
			<div>
				<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-mb-2 tutor-small">
					<span class="tutor-qna-card-author">Annathoms</span> 
					<span class="tutor-text-subdued">2 days ago</span>
				</div>
				<div class="tutor-p2 tutor-text-secondary tutor-mb-6">It's so nerve-racking. :(</div>
				<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-text-subdued">
					<button class="tutor-qna-thumb-button">
						<?php tutor_utils()->render_svg_icon( Icon::THUMB, 20, 20, array( 'class' => 'tutor-icon-subdued' ) ); ?>
					</button>
					5
				</div>
			</div>
			<div class="tutor-ml-auto">
				<button class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon tutor-icon-secondary">
					<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL ); ?>
				</button>
			</div>
		</div>
		<div class="tutor-qna-reply-list-item">
			<div class="tutor-avatar tutor-avatar-40">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>
			<div>
				<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-mb-2 tutor-small">
					<span class="tutor-qna-card-author">Annathoms</span> 
					<span class="tutor-text-subdued">2 days ago</span>
				</div>
				<div class="tutor-p2 tutor-text-secondary tutor-mb-6">Hi I’m facing exactly the same problems than ​@af2cb64c_8cb4 Is it possible to fall back to previous august 7th previous update for Figma Desktop to check if that issues are caused by that update ?</div>
				<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-text-subdued">
					<button class="tutor-qna-thumb-button">
						<?php tutor_utils()->render_svg_icon( Icon::THUMB, 20, 20, array( 'class' => 'tutor-icon-subdued' ) ); ?>
					</button>
					5
				</div>
			</div>
			<div class="tutor-ml-auto">
				<button class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon tutor-icon-secondary">
					<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL ); ?>
				</button>
			</div>
		</div>
		<div class="tutor-qna-reply-list-item">
			<div class="tutor-avatar tutor-avatar-40">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>
			<div>
				<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-mb-2 tutor-small">
					<span class="tutor-qna-card-author">Annathoms</span> 
					<span class="tutor-text-subdued">2 days ago</span>
				</div>
				<div class="tutor-p2 tutor-text-secondary tutor-mb-6">Hi I’m facing exactly the same problems than ​@af2cb64c_8cb4 Is it possible to fall back to previous august 7th previous update for Figma Desktop to check if that issues are caused by that update ?</div>
				<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-text-subdued">
					<button class="tutor-qna-thumb-button">
						<?php tutor_utils()->render_svg_icon( Icon::THUMB_FILL, 20, 20, array( 'class' => 'tutor-icon-subdued' ) ); ?>
					</button>
					5
				</div>
			</div>
			<div class="tutor-ml-auto">
				<button class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon tutor-icon-secondary">
					<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
