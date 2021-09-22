<?php $field_id = 'field_' . $field['key'];
?>
<div class="tutor-option-field-row" id="<?php echo $field_id; ?>">
    <?php include tutor()->path . "views/options/template/field_heading.php"; ?>

    <div class="tutor-option-field-input d-flex justify-content-end">
        <select class="tutor-form-select" disabled="">
            <option selected="">Select your Fee type</option>
            <option value="1">percent</option>
            <option value="2">fixed</option>
        </select>
        <input type="number" class="tutor-form-control" placeholder="0" value="0" min="0" disabled="">
    </div>
</div>