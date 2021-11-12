<div id="tutor-page-reset-modal" class="tutor-modal tutor-modal-confirmation">
	<span class="tutor-modal-overlay"></span>
	<button data-tutor-modal-close class="tutor-modal-close">
		<span class="las la-times"></span>
	</button>
	<div class="tutor-modal-root">
		<div class="tutor-modal-inner">
			<div class="tutor-modal-body tutor-text-center">
				<div class="tutor-modal-icon">
					<img src="<?php echo esc_attr( $modal['icon'] ); ?>" alt=""/>
				</div>
				<div class="tutor-modal-text-wrap">
					<h3 class="tutor-modal-title"><?php echo esc_attr( $modal['heading'] ); ?></h3>
				</div>
				<div class="tutor-alert tutor-warning tutor-mt-30">
					<div class="tutor-alert-text">
						<span class="tutor-alert-icon tutor-icon-34  ttr-warning-filled tutor-mr-10"></span>
						<span class="color-warning-100"><?php echo esc_attr( $modal['message'] ); ?></span>
					</div>
				</div>
				<div class="tutor-modal-btns tutor-btn-group tutor-mt-40">
					<button data-tutor-modal-close class="tutor-btn tutor-is-outline tutor-is-default">
						Cancel
					</button>
					<button class="tutor-btn reset_to_default" data-reset="<?php echo esc_attr( $section['slug'] ); ?>">
						Yes, Delete Course
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
