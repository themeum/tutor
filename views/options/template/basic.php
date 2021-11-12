<div class="tutor-option-main-title">
	<h2><?php echo esc_attr( $section['label'] ); ?></h2>
	<button data-tutor-modal-target="tutor-page-reset-modal" class="reset-btn">
		<i class="btn-icon ttr-refresh-1-filled"></i>
		<?php echo esc_attr( 'Reset to Default', 'tutor' ); ?>
	</button>
	<?php
	echo $this->this_confirmation(
		array(
			'key'     => esc_attr( $section['slug'] ),
			'icon'    => tutor()->icon_dir . 'reset.svg',
			'heading' => esc_attr( 'Reset to Default Settings?' ),
			'message' => esc_attr( 'WARNING! This will overwrite all existing settings, please proceed with caution.' ),
		)
	);
	?>
</div>
<!-- end /.tutor-option-main-title -->
<?php
foreach ( $section['blocks'] as $blocks ) :
	if ( empty( $blocks['label'] ) ) :
		?>
		<div class="tutor-option-single-item"><?php echo $this->blocks( $blocks ); ?> </div>
	<?php else : ?>
		<?php echo $this->blocks( $blocks ); ?>
	<?php endif; ?>
<?php endforeach; ?>
