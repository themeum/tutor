<?php if ($blocks['block_type'] == 'uniform') : ?>
    <!-- .tutor-option-single-item  -->
    <div class="tutor-option-single-item">
        <h4><?php echo $blocks['label'] ?? '' ?></h4>
        <div class="item-wrapper">
            <?php
            foreach ($blocks['fields'] as $field) :
                echo $this->generate_field($field);
            endforeach;
            ?>
        </div>
    </div>
    <!-- end /.tutor-option-single-item  -->

<?php elseif ($blocks['block_type'] == 'isolate') : ?>

    <!-- .tutor-option-single-item  -->
    <div class="tutor-option-single-item">
        <h4><?php echo $blocks['label'] ?? '' ?></h4>
        <?php foreach ($blocks['fields'] as $field) : ?>
            <div class="item-wrapper">
                <?php echo $this->generate_field($field) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <!-- end /.tutor-option-single-item  -->
<?php endif; ?>