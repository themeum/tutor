<div class="tutor-option-main-title">
	<h2>Status</h2>
</div>


<!-- .tutor-option-single-item  (Certificate) -->
<?php foreach ( $section['blocks'] as $blocks ) :
	if ( empty( $blocks['label'] ) ) : ?>
		<div class="tutor-option-single-item"><?php echo $this->blocks( $blocks ); ?> </div>
	<?php else : ?>
		<?php echo $this->blocks( $blocks ); ?>
	<?php endif; ?>
<?php endforeach; ?>

<!-- end /.tutor-option-single-item  (Certificate) -->
