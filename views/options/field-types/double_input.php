<div class="tutor-option-field-row">
<?php include tutor()->path . "views/options/template/field_heading.php";?>

    <div class="tutor-option-field-input">
        <div class="d-flex flex-column double-input">
            <label for="revenue-instructor" class="revenue-percentage">
                <span>Instructor Takes</span>
                <input type="number" class="tutor-form-control" placeholder="0 %" name="revenue-instructor" id="revenue-instructor" min="0" max="100" value="10">
            </label>
            <label for="revenue-admin" class="revenue-percentage">
                <span>Admin Takes</span>
                <input type="number" class="tutor-form-control" placeholder="0 %" name="revenue-admin" id="revenue-admin" min="0" max="100" value="100">
            </label>
        </div>
    </div>
</div>