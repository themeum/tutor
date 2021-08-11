<?php if ($blocks['block_type'] == 'uniform') : ?>

    <div class="tutor-option-single-item <?php echo !isset($blocks['class']) ? ($blocks['slug'] ?? null) : $blocks['class'] ?>">
        <?php echo $blocks['label'] ? '<h4>' . $blocks['label'] . '</h4>' : '' ?>
        <div class="item-wrapper">
            <?php
            foreach ($blocks['fields'] as $field) :
                echo $this->generate_field($field);
            endforeach;
            ?>
        </div>
    </div>

<?php elseif ($blocks['block_type'] == 'isolate') : ?>

    <div class="tutor-option-single-item <?php echo $blocks['slug'] ?>">
        <?php echo $blocks['label'] ? '<h4>' . $blocks['label'] . '</h4>' : '' ?>
        <?php foreach ($blocks['fields'] as $field) : ?>
            <div class="item-wrapper">
                <?php echo $this->generate_field($field); ?>
            </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>