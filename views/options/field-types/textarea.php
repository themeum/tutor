<?php $field_id = 'field_' . $field['key'];
 ?>
<div class="tutor-option-field-row col-1x145" id="<?php echo $field_id; ?>"
>
<?php include tutor()->path . "views/options/template/field_heading.php";?>

    <div class="tutor-option-field-input">
        <textarea class="tutor-form-control" name="tutor_option[<?php echo $field['key']; ?>]" rows="10" placeholder="<?php echo $field['desc'] ?>"><?php echo $this->get($field['key']) ?></textarea>
    </div>
</div>