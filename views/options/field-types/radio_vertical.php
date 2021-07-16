<div class="tutor-option-field-row d-block">
    <div class="tutor-option-field-label">
        <label><?php echo $field['label']; ?></label>
        <p class="desc"><?php echo $field['desc'] ?></p>
    </div>
    <div class="tutor-option-field-input d-block">
        <div class="type-check d-block has-desc">
            <div class="tutor-form-check">
                <input type="radio" id="radio_x" class="tutor-form-check-input" name="radio_b" checked />
                <label for="radio_x">
                    Flexible
                    <p class="desc">
                        Allow instructors and admins to view the course content without enrolling
                    </p>
                </label>
            </div>
            <div class="tutor-form-check">
                <input type="radio" id="radio_y" class="tutor-form-check-input" name="radio_b" />
                <label for="radio_y">
                    Strict Mode
                    <p class="desc">
                        Students have to complete, pass all the lessons and quizzes (if any) to mark a course as
                        complete.
                    </p>
                </label>
            </div>
        </div>
    </div>
</div>