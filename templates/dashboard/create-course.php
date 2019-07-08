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


            <div class="tutor-form-row">
                <div class="tutor-form-col-12">
                    <div class="tutor-form-group">
                        <label>
							<?php _e('Course Title', 'tutor'); ?>
                        </label>

                        <input type="text" name="title" value="<?php echo get_the_title(); ?>" placeholder="<?php _e('ex. Learn photoshop CS6 from scratch', 'tutor'); ?>">
                    </div>
                </div>

            </div>

            <div class="tutor-form-row">
                <div class="tutor-form-col-12">
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
            </div>

            <div class="tutor-form-row">
                <div class="tutor-form-col-12">
                    <div class="tutor-form-group">
                        <label>
							<?php _e('Choose a category', 'tutor'); ?>
                        </label>
                        <div class="tutor-form-field-course-categories">
							<?php echo tutor_course_categories_checkbox(); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tutor-form-row">
                <div class="tutor-form-col-12">
                    <div class="tutor-form-group">
                        <label>
							<?php _e('Course Thumbnail', 'tutor'); ?>
                        </label>

                        <div class="tutor-form-field tutor-form-field-course-thumbnail tutor-thumbnail-wrap">

                            <div class="tutor-row">
                                <div class="tutor-col-4">
                                    <div class="builder-course-thumbnail-img-src">
										<?php $builder_course_img_src = tutor_placeholder_img_src(); ?>
                                        <img src="<?php echo $builder_course_img_src; ?>" class="thumbnail-img" data-placeholder-src="<?php echo $builder_course_img_src; ?>">
                                    </div>
                                </div>

                                <div class="tutor-col-8">
                                    <div class="builder-course-thumbnail-upload-wrap">
                                        <a href="javascript:;" class="tutor-course-thumbnail-delete-btn"><i class="tutor-icon-garbage"></i> </a>
                                        <input type="hidden" id="tutor_course_thumbnail_id" name="tutor_course_thumbnail_id" value="">
                                        <a href="javascript:;" class="tutor-course-thumbnail-upload-btn"><?php _e('Upload Image', 'tutor'); ?></a>
                                    </div>
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
                        <button type="submit"><?php _e('Save Course', 'tutor'); ?></button>
                    </div>

                </div>
            </div>
        </div>

    </div>

</form>

<?php do_action('tutor/dashboard_course_builder_after'); ?>