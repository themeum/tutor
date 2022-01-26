
<div class="tutor-modal tutor-bulk-modal-disabled">
  	<span class="tutor-modal-overlay"></span>
  	<button data-tutor-modal-close class="tutor-modal-close">
		<span class="tutor-icon-line-cross-line"></span>
  	</button>
  	<div class="tutor-modal-root">
		<div class="tutor-modal-inner">
			<div class="tutor-modal-body tutor-text-center tutor-bulk-confirm-modal">
				<div class="tutor-modal-icon">
				<img src="https://i.imgur.com/Nx6U2u7.png" alt="" />
				</div>
				<div class="tutor-modal-text-wrap">
				<h3 class="tutor-modal-title">
					<?php esc_html_e( 'Wait!', 'tutor' ); ?>
				</h3>
				<p>
					<?php esc_html_e( 'Are you sure you would like perform this action? We suggest you proceed with caution.', 'tutor' ); ?>
				</p>
				</div>
				<div class="tutor-modal-btns tutor-btn-group">
				<button
					data-tutor-modal-close
					class="tutor-btn tutor-is-outline tutor-is-default"
				>
					<?php esc_html_e( 'Cancel', 'tutor' ); ?>
				</button>
				<button class="tutor-btn tutor-btn-wordpress tutor-no-hover tutor-btn-loading tutor-btn-lg" type="button" id="tutor-confirm-bulk-action">
					<?php esc_html_e( "Yes, I'am Sure", 'tutor' ); ?>
				</button>
				</div>
			</div>
		</div>
  	</div>
</div>
