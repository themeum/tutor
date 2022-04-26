<div class="tutor-option-main-title">
    <div class="tutor-fs-4 tutor-fw-medium tutor-color-black"><?php echo $section->label ?></div>
	<button class="reset-btn reset_to_default" data-reset="<?php echo esc_attr( $section['slug'] ); ?>">
		<i class="btn-icon tutor-icon-refresh"></i>
		<?php echo esc_attr( 'Reset to Default', 'tutor' ); ?>
	</button>
</div>

<?php foreach ($section['blocks'] as $blocks) :
    if (empty($blocks['label'])) : ?>
        <div class="tutor-option-single-item tutor-mb-32"><?php echo $this->blocks($blocks) ?> </div>
    <?php else : ?>
        <?php echo $this->blocks($blocks); ?>
    <?php endif; ?>
<?php endforeach; ?>