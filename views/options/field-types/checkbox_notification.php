<?php
if ( ! empty( $field['options'] ) ) { ?>
    <div class="tutor-option-field-row">
        <?php include tutor()->path . "views/options/template/field_heading.php"; ?>
        <div class="type-check tutor-bs-d-flex">
            <?php foreach ( $field['options'] as $optionKey => $option ) : ?>
                <div class="tutor-form-check">
                    <input type="checkbox" id="check_<?php esc_attr_e( $optionKey ); ?>" name="tutor_option<?php esc_attr_e( $optionKey ); ?>" value="1" <?php checked( $option['value'], 1 ); ?> class="tutor-form-check-input" />
                    <label for="check_<?php esc_attr_e( $optionKey ); ?>"> <?php esc_html_e( $option['label'] ); ?> </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php 
}
