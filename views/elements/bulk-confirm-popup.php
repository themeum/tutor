
<!-- @todo: remove imgur.com -->
<div class="tutor-modal tutor-bulk-modal-disabled">
  	<span class="tutor-modal-overlay"></span>
  	<button data-tutor-modal-close class="tutor-modal-close">
		<span class="tutor-icon-times"></span>
  	</button>
  	<div class="tutor-modal-window">
		<div class="tutor-modal-content">
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
					<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>
					<button class="tutor-btn tutor-btn-primary tutor-ml-16 tutor-btn-loading" type="button" id="tutor-confirm-bulk-action">
						<?php esc_html_e( "Yes, I'am Sure", 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
  	</div>
</div>
