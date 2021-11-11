<div class="tutor-option-main-title">
    <h2><?php echo $section['label']
        ?></h2>
        <button class="reset-btn reset_to_default" data-reset="<?php echo esc_attr( $section['slug'] ); ?>">
            <i class="btn-icon ttr-refresh-1-filled"></i>
            <?php echo esc_attr( 'Reset to Default', 'tutor' ); ?>
        </button>
</div>
<!-- end /.tutor-option-main-title -->

<!-- .tutor-option-single-item  (Certificate) -->
<div class="tutor-option-single-item create-certificate-steps">
    <div class="item-wrapper">
        <h4>
            Create Your Certificate <br />
            In 3 Steps
        </h4>
        <ul>
            <li>Select your favorite design</li>
            <li>Type in your text & upload your signature</li>
            <li><strong>Press Save,</strong>Your certificate Ready</li>
        </ul>
        <div class="create-certificate-btn">
            <button class="tutor-btn tutor-is-sm">
                <!-- <span class="tutor-btn-icon las la-file-signature"></span> -->
                <span class="tutor-btn-icon ttr-certificate-filled"></span>
                <span>Create Certificate</span>
            </button>
        </div>
    </div>
</div>
<!-- end /.tutor-option-single-item  (Certificate) -->

<!-- .tutor-option-single-item  (Certificate) -->
<?php foreach ($section['blocks'] as $blocks) :
    if (empty($blocks['label'])) : ?>
        <div class="tutor-option-single-item"><?php echo $this->blocks($blocks) ?> </div>
    <?php else : ?>
        <?php echo $this->blocks($blocks); ?>
    <?php endif; ?>
<?php endforeach; ?>

<!-- end /.tutor-option-single-item  (Certificate) -->