<div class="tutor-option-main-title">
    <h2><?php echo $section['label'] ?></h2>
	<button class="reset-btn reset_to_default" data-reset="<?php echo esc_attr( $section['slug'] ); ?>">
		<i class="btn-icon tutor-icon-refresh-1-filled"></i>
		<?php echo esc_attr( 'Reset to Default', 'tutor' ); ?>
	</button>
</div>

<?php foreach ($section['blocks'] as $blocks) :
    if (empty($blocks['label'])) : ?>
        <div class="tutor-option-single-item"><?php echo $this->blocks($blocks) ?> </div>
    <?php else : ?>
        <?php echo $this->blocks($blocks); ?>
    <?php endif; ?>
<?php endforeach; ?>