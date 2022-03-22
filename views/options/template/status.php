<div class="tutor-option-main-title">
	<div class="tutor-fs-4 tutor-fw-medium tutor-color-black"><?php _e('Status','tutor'); ?></div>
</div>

<?php foreach ( $section['blocks'] as $blocks ) :
	if ( empty( $blocks['label'] ) ) : ?>
		<div class="tutor-option-single-item tutor-mb-32"><?php echo $this->blocks( $blocks ); ?> </div>
	<?php else : ?>
		<?php echo $this->blocks( $blocks ); ?>
	<?php endif; ?>
<?php endforeach; ?>
