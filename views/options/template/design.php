<!-- .tutor-option-main-title -->
<div class="tutor-option-main-title">
    <h2><?php echo $section->label ?></h2>
    <a href="#">
        <i class="las la-undo-alt"></i> Reset to Default </a>
</div>
<!-- end /.tutor-option-main-title -->

<!-- .tutor-option-single-item  Design (Course) -->
<div class="tutor-option-single-item">
    <h4>Course</h4>
    <div class="item-wrapper">
        <div class="tutor-option-field-row d-block">
            <div class="tutor-option-field-label">
                <label>Course Builder Page Logo</label>
            </div>
            <div class="tutor-option-field-input image-previewer">
                <div class="d-flex logo-upload">
                    <div class="logo-preview">
                        <span class="preview-loading"></span>
                        <img src="<?php echo tutor()->url . 'assets/images/images-v2' ?>/icons/tutor-logo-course-builder.svg" alt="course builder logo" />
                        <!-- <img src="" alt="" /> -->
                        <span class="delete-btn"></span>
                    </div>
                    <div class="logo-upload-wrap">
                        <p>
                            Size: <strong>200x40 pixels;</strong> File Support:
                            <strong>jpg, .jpeg or .png.</strong>
                        </p>
                        <label for="builder-logo-upload" class="tutor-btn tutor-is-sm">
                            <input type="file" name="builder-logo-upload" id="builder-logo-upload" accept=".jpg, .jpeg, .png, .svg" />
                            <span class="tutor-btn-icon tutor-v2-icon-test icon-image-filled"></span>
                            <span>Upload Image</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="tutor-option-field-row col-1x2 col-per-row">
            <div class="tutor-option-field-label">
                <label>Column Per Row</label>
                <p class="desc">Define how many column you want to use to display courses.</p>
            </div>
            <div class="tutor-option-field-input">
                <div class="d-flex radio-thumbnail items-per-row">
                    <label for="items-per-row-1" class="items-per-row-label">
                        <input type="radio" name="items-per-row" id="items-per-row-1" />
                        <span class="icon-wrapper icon-col">
                            <span>1</span>
                        </span>
                        <span class="title">One</span>
                    </label>
                    <label for="items-per-row-2" class="items-per-row-label">
                        <input type="radio" name="items-per-row" id="items-per-row-2" checked />
                        <span class="icon-wrapper icon-col">
                            <span>2</span>
                            <span>2</span>
                        </span>
                        <span class="title">Two</span>
                    </label>
                    <label for="items-per-row-3" class="items-per-row-label">
                        <input type="radio" name="items-per-row" id="items-per-row-3" />
                        <span class="icon-wrapper icon-col">
                            <span>3</span>
                            <span>3</span>
                            <span>3</span>
                        </span>
                        <span class="title">Three</span>
                    </label>
                    <label for="items-per-row-4" class="items-per-row-label">
                        <input type="radio" name="items-per-row" id="items-per-row-4" />
                        <span class="icon-wrapper icon-col">
                            <span>4</span>
                            <span>4</span>
                            <span>4</span>
                            <span>4</span>
                        </span>
                        <span class="title">Four</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label>Course Filter</label>
                <p class="desc">Show sorting and filtering options on course archive page</p>
            </div>
            <div class="tutor-option-field-input">
                <label class="tutor-form-toggle">
                    <input type="checkbox" class="tutor-form-toggle-input" checked />
                    <span class="tutor-form-toggle-control"></span>
                </label>
            </div>
        </div>

        <div class="tutor-option-field-row d-block">
            <div class="tutor-option-field-label">
                <label>Preferred Course Filters</label>
                <p class="desc">Choose preferred filter options you'd like to show in course archive page.</p>
            </div>
            <div class="tutor-option-field-input">
                <div class="type-check d-flex">
                    <div class="tutor-form-check">
                        <input type="checkbox" class="tutor-form-check-input" id="course-filters-keyword" name="course-filters-keyword" checked />
                        <label for="course-filters-keyword"> Keyword Search </label>
                    </div>
                    <div class="tutor-form-check">
                        <input type="checkbox" class="tutor-form-check-input" id="course-filters-category" name="course-filters-category" />
                        <label for="course-filters-category"> Category </label>
                    </div>
                    <div class="tutor-form-check">
                        <input type="checkbox" class="tutor-form-check-input" id="course-filters-tag" name="course-filters-tag" />
                        <label for="course-filters-tag"> Tag </label>
                    </div>
                    <div class="tutor-form-check">
                        <input type="checkbox" class="tutor-form-check-input" id="course-filters-difficulty-lavel" name="course-filters-difficulty-lavel" />
                        <label for="course-filters-difficulty-lavel"> Difficulty Level </label>
                    </div>
                    <div class="tutor-form-check">
                        <input type="checkbox" class="tutor-form-check-input" id="course-filters-price" name="course-filters-price" />
                        <label for="course-filters-price"> Price Type </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end /.tutor-option-single-item  Design (Course) -->

<!-- .tutor-option-single-item  Design (Layout) -->
<div class="tutor-option-single-item">
    <h4>Layout</h4>
    <div class="item-wrapper">
        <div class="tutor-option-field-row d-block">
            <div class="tutor-option-field-label">
                <label>Instructor List Layout</label>
                <p class="desc">Content Needed Here............</p>
            </div>
            <div class="tutor-option-field-input">
                <div class="radio-thumbnail has-title instructor-list">
                    <div class="vertical">
                        <div class="layout-label">Vertical</div>
                        <div class="d-flex- fields-wrapper">
                            <label for="intructor-list-portrait">
                                <input type="radio" name="instructor-list-layout" id="intructor-list-portrait" checked />
                                <span class="icon-wrapper">
                                    <img src="<?php echo tutor()->url . 'assets/images/images-v2' ?>/instructor-layout/intructor-portrait.svg" alt="" />
                                </span>
                                <span class="title">Portrait</span>
                            </label>
                            <label for="intructor-list-cover">
                                <input type="radio" name="instructor-list-layout" id="intructor-list-cover" />
                                <span class="icon-wrapper">
                                    <img src="<?php echo tutor()->url . 'assets/images/images-v2' ?>/instructor-layout/instructor-cover.svg" alt="" />
                                </span>
                                <span class="title">Cover</span>
                            </label>
                            <label for="intructor-list-minimal">
                                <input type="radio" name="instructor-list-layout" id="intructor-list-minimal" />
                                <span class="icon-wrapper">
                                    <img src="<?php echo tutor()->url . 'assets/images/images-v2' ?>/instructor-layout/instructor-minimal.svg" alt="" />
                                </span>
                                <span class="title">Minimal</span>
                            </label>
                        </div>
                    </div>
                    <div class="horizontal">
                        <div class="layout-label">Horizontal</div>
                        <div class="d-flex- fields-wrapper">
                            <label for="intructor-list-horizontal-portrait">
                                <input type="radio" name="instructor-list-layout" id="intructor-list-horizontal-portrait" />
                                <span class="icon-wrapper">
                                    <img src="<?php echo tutor()->url . 'assets/images/images-v2' ?>/instructor-layout/instructor-horizontal-portrait.svg" alt="" />
                                </span>
                                <span class="title">Horizontal Portrait</span>
                            </label>
                            <label for="intructor-list-horizontal-minimal">
                                <input type="radio" name="instructor-list-layout" id="intructor-list-horizontal-minimal" />
                                <span class="icon-wrapper">
                                    <img src="<?php echo tutor()->url . 'assets/images/images-v2' ?>/instructor-layout/instructor-horizontal-minimal.svg" alt="" />
                                </span>
                                <span class="title">Horizontal Minimal</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tutor-option-field-row d-block">
            <div class="tutor-option-field-label">
                <label>Public Profile Layout</label>
                <p class="desc">Content Needed Here............</p>
            </div>
            <div class="tutor-option-field-input">
                <div class="radio-thumbnail has-title public-profile fields-wrapper">
                    <label for="profile-layout-modern">
                        <input type="radio" name="profile-layout" id="profile-layout-modern" />
                        <span class="icon-wrapper">
                            <img src="<?php echo tutor()->url . 'assets/images/images-v2' ?>/profile-layout/profile-modern.svg" alt="" />
                        </span>
                        <span class="title">Modern</span>
                    </label>
                    <label for="profile-layout-minimal">
                        <input type="radio" name="profile-layout" id="profile-layout-minimal" />
                        <span class="icon-wrapper">
                            <img src="<?php echo tutor()->url . 'assets/images/images-v2' ?>/profile-layout/profile-minimal.svg" alt="" />
                        </span>
                        <span class="title">Minimal</span>
                    </label>
                    <label for="profile-layout-classic">
                        <input type="radio" name="profile-layout" id="profile-layout-classic" checked />
                        <span class="icon-wrapper">
                            <img src="<?php echo tutor()->url . 'assets/images/images-v2' ?>/profile-layout/profile-classic.svg" alt="" />
                        </span>
                        <span class="title">Classic</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.tutor-option-single-item  Design (Layout) -->

<!-- .tutor-option-single-item  Design (Course Details) -->
<div class="tutor-option-single-item">
    <h4>Course Details</h4>
    <div class="item-wrapper">
        <div class="tutor-option-field-input">
            <div class="type-toggle-grid">
                <div class="toggle-item">
                    <label class="tutor-form-toggle">
                        <input type="checkbox" class="tutor-form-toggle-input" />
                        <span class="tutor-form-toggle-control"></span>
                        <span class="label-after"> Instructor Info </span>
                    </label>
                    <div class="tooltip-wrap tooltip-icon">
                        <span class="tooltip-txt tooltip-right">I'm your tiny tooltips text</span>
                    </div>
                </div>
                <div class="toggle-item">
                    <label class="tutor-form-toggle">
                        <input type="checkbox" class="tutor-form-toggle-input" />
                        <span class="tutor-form-toggle-control"></span>
                        <span class="label-after"> Question and Answer </span>
                    </label>
                    <div class="tooltip-wrap tooltip-icon">
                        <span class="tooltip-txt tooltip-right">I'm your tiny tooltips text</span>
                    </div>
                </div>
                <div class="toggle-item">
                    <label class="tutor-form-toggle">
                        <input type="checkbox" class="tutor-form-toggle-input" />
                        <span class="tutor-form-toggle-control"></span>
                        <span class="label-after"> Author </span>
                    </label>
                    <div class="tooltip-wrap tooltip-icon">
                        <span class="tooltip-txt tooltip-right">I'm your tiny tooltips text</span>
                    </div>
                </div>
                <div class="toggle-item">
                    <label class="tutor-form-toggle">
                        <input type="checkbox" class="tutor-form-toggle-input" />
                        <span class="tutor-form-toggle-control"></span>
                        <span class="label-after"> Level </span>
                    </label>
                    <div class="tooltip-wrap tooltip-icon">
                        <span class="tooltip-txt tooltip-right">I'm your tiny tooltips text</span>
                    </div>
                </div>
                <div class="toggle-item">
                    <label class="tutor-form-toggle">
                        <input type="checkbox" class="tutor-form-toggle-input" />
                        <span class="tutor-form-toggle-control"></span>
                        <span class="label-after"> Social Share </span>
                    </label>
                    <div class="tooltip-wrap tooltip-icon">
                        <span class="tooltip-txt tooltip-right">I'm your tiny tooltips text</span>
                    </div>
                </div>
                <div class="toggle-item">
                    <label class="tutor-form-toggle">
                        <input type="checkbox" class="tutor-form-toggle-input" />
                        <span class="tutor-form-toggle-control"></span>
                        <span class="label-after"> Course Duration </span>
                    </label>
                    <div class="tooltip-wrap tooltip-icon">
                        <span class="tooltip-txt tooltip-right">I'm your tiny tooltips text</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end /.tutor-option-single-item  Design (Course Details) -->