<div class="tutor-option-main-title">
    <h2><?php echo $section['label'] ?></h2>
    <a href="#">
        <i class="las la-undo-alt"></i> Reset to Default </a>
</div>

<?php foreach ($section['blocks'] as $blocks) :
    if (empty($blocks['label'])) : ?>
        <div class="tutor-option-single-item"><?php echo $this->blocks($blocks) ?> </div>
    <?php else : ?>
        <?php echo $this->blocks($blocks); ?>
    <?php endif; ?>
<?php endforeach; ?>