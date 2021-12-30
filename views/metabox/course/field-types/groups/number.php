
<input type="number" name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr($input_value); ?>" >
<?php
if ($label){
    echo _esc_html('<p>'.$label.'</p>');
}
?>