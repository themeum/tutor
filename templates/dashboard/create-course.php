<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

global $post;

$course_id = get_the_ID();
?>

<?php do_action('tutor/dashboard_course_builder_before'); ?>

    <form action="" method="post" enctype="multipart/form-data">

		<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
        <input type="hidden" value="tutor_add_course_builder" name="tutor_action"/>
        <input type="hidden" name="course_ID" id="course_ID" value="<?php echo get_the_ID(); ?>">
        <input type="hidden" name="post_ID" id="post_ID" value="<?php echo get_the_ID(); ?>">


        <div class="tutor-dashboard-course-builder-wrap">

			<?php do_action('tutor/dashboard_course_builder_form_field_before'); ?>

            <div class="tutor-course-builder-section tutor-course-builder-info">
                <div class="tutor-course-builder-section-title">
                    <h3><i class="tutor-icon-move"></i><span><?php esc_html_e('Course Info', 'tutor'); ?></span></h3>
                </div>
                <div class="tutor-frontend-builder-item-scope">
                    <div class="tutor-form-group">
                        <label class="tutor-builder-item-heading">
                            <?php _e('Course Title', 'tutor'); ?>
                        </label>
                        <input type="text" name="title" value="<?php echo get_the_title(); ?>" placeholder="<?php _e('ex. Learn photoshop CS6 from scratch', 'tutor'); ?>">
                    </div>
                </div>
                <div class="tutor-frontend-builder-item-scope">
                    <div class="tutor-form-group">
                        <label>
                            <?php _e('Description', 'tutor'); ?>
                        </label>

                        <?php
                        $editor_settings = array(
                            'media_buttons' => false,
                            'quicktags'     => false,
                            'editor_height' => 150,
                            'textarea_name' => 'content'
                        );
                        wp_editor($post->post_content, 'course_description', $editor_settings);
                        ?>
                    </div>
                </div>
                <div class="tutor-frontend-builder-item-scope">
                    <div class="tutor-form-group">
                        <label>
                            <?php _e('Choose a category', 'tutor'); ?>
                        </label>
                        <div class="tutor-form-field-course-categories">
                            <?php echo tutor_course_categories_checkbox($course_id); has_category(); ?>
                        </div>
                    </div>
                </div>
                <div class="tutor-frontend-builder-item-scope">
                    <div class="tutor-form-group">
                        <label>
                            <?php _e('Course Thumbnail', 'tutor'); ?>
                        </label>
                        <div class="tutor-form-field tutor-form-field-course-thumbnail tutor-thumbnail-wrap">
                            <div class="tutor-row tutor-align-items-center">
                                <div class="tutor-col-5">
                                    <div class="builder-course-thumbnail-img-src">
                                        <?php
                                        $builder_course_img_src = tutor_placeholder_img_src();
                                        $_thumbnail_url = get_the_post_thumbnail_url($course_id);
                                        $post_thumbnail_id = get_post_thumbnail_id( $course_id );

                                        if ( ! $_thumbnail_url){
                                            $_thumbnail_url = $builder_course_img_src;
                                        }
                                        ?>
                                        <img src="<?php echo $_thumbnail_url; ?>" class="thumbnail-img" data-placeholder-src="<?php echo $builder_course_img_src; ?>">
                                        <a href="javascript:;" class="tutor-course-thumbnail-delete-btn"><i class="tutor-icon-line-cross"></i></a>
                                    </div>
                                </div>

                                <div class="tutor-col-7">
                                    <div class="builder-course-thumbnail-upload-wrap">
                                        <h4><?php echo sprintf(__("Important Guidelines: %1\$s 700x430 pixels %2\$s %3\$s File Support: %1\$s jpg, .jpeg,. gif, or .png %2\$s no text on the image.", "tutor"), "<strong>", "</strong>", "<br>") ?></h4>
                                        <input type="hidden" id="tutor_course_thumbnail_id" name="tutor_course_thumbnail_id" value="<?php echo $post_thumbnail_id; ?>">
                                        <a href="javascript:;" class="tutor-course-thumbnail-upload-btn tutor-button"><?php _e('Upload Image', 'tutor'); ?></a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
			<?php do_action('tutor/dashboard_course_builder_form_field_after'); ?>
            <div class="tutor-form-row">
                <div class="tutor-form-col-12">
                    <div class="tutor-form-group">
                        <div class="tutor-form-field">
                            <button type="submit" name="course_submit_btn" value="save_course_as_draft"><?php _e('Save course as draft', 'tutor'); ?></button>
							<?php
							$can_publish_course = (bool) tutor_utils()->get_option('instructor_can_publish_course');
							if ($can_publish_course){
								?>
                                    <button type="submit" name="course_submit_btn" value="publish_course"><?php _e('Publish Course', 'tutor'); ?></button>
								<?php
							}else{
								?>
                                    <button type="submit" name="course_submit_btn" value="submit_for_review"><?php _e('Submit for Review', 'tutor'); ?></button>
								<?php
							}
							?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </form>

<?php do_action('tutor/dashboard_course_builder_after'); ?>