<div id="tutor-page-reset-modal" class="tutor-modal">
	<span class="tutor-modal-overlay"></span>
	<button data-tutor-modal-close class="tutor-modal-close">
		<span class="las la-times"></span>
	</button>
	<div class="tutor-modal-root">
		<div class="tutor-modal-inner">
			<div class="tutor-modal-body tutor-text-center">
				<div class="tutor-modal-icon">
					<img src="<?php echo esc_attr( $modal['icon'] ); ?>" alt="icon" />
				</div>
				<div class="tutor-modal-text-wrap">
					<h3 class="tutor-modal-title"><?php echo esc_attr( $modal['heading'] ); ?></h3>
					<p><?php echo esc_attr( $modal['message'] ); ?></p>
				</div>
				<div class="tutor-modal-btns tutor-btn-group">
					<button data-tutor-modal-close class="tutor-btn tutor-is-outline tutor-is-default">
						Cancel
					</button>
					<button class="tutor-btn">Yes, Delete Course</button>
				</div>
			</div>
		</div>
	</div>
</div>
