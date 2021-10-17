
<div id="tutor-bulk-confirm-popup" class="tutor-modal">
  <span class="tutor-modal-overlay"></span>
  <button data-tutor-modal-close class="tutor-modal-close">
	<span class="las la-times"></span>
  </button>
  <div class="tutor-modal-root">
	<div class="tutor-modal-inner">
	  <div class="tutor-modal-body tutor-text-center">
		<div class="tutor-modal-icon">
		  <img src="https://i.imgur.com/Nx6U2u7.png" alt="" />
		</div>
		<div class="tutor-modal-text-wrap">
		  <h3 class="tutor-modal-title">
			  <?php esc_html_e( 'Are you sure?', 'tutor' ); ?>
		  </h3>
		  <p>
			<?php esc_html_e( 'This action can not be undone.' ); ?>
		  </p>
		</div>
		<div class="tutor-modal-btns tutor-btn-group">
		  <button
			data-tutor-modal-close
			class="tutor-btn tutor-is-outline tutor-is-default"
		  >
			Cancel
		  </button>
		  <button class="tutor-btn" type="button" id="tutor-confirm-bulk-action">
			  <?php esc_html_e( "Yes, I'am Sure", 'tutor' ); ?>
		  </button>
		</div>
	  </div>
	</div>
  </div>
</div>
