<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

global $post;



$course_id = get_the_ID();

$duration = maybe_unserialize(get_post_meta($course_id, '_course_duration', true));
$durationHours = tutor_utils()->avalue_dot('hours', $duration);
$durationMinutes = tutor_utils()->avalue_dot('minutes', $duration);
$durationSeconds = tutor_utils()->avalue_dot('seconds', $duration);

$levels = tutor_utils()->course_levels();

$course_level = get_post_meta($course_id, '_tutor_course_level', true);
$benefits = get_post_meta($course_id, '_tutor_course_benefits', true);
$requirements = get_post_meta($course_id, '_tutor_course_requirements', true);
$target_audience = get_post_meta($course_id, '_tutor_course_target_audience', true);
$material_includes = get_post_meta($course_id, '_tutor_course_material_includes', true);


//Video variable

$video = maybe_unserialize(get_post_meta($post->ID, '_video', true));

$videoSource = tutor_utils()->avalue_dot('source', $video);
$runtimeHours = tutor_utils()->avalue_dot('runtime.hours', $video);
$runtimeMinutes = tutor_utils()->avalue_dot('runtime.minutes', $video);
$runtimeSeconds = tutor_utils()->avalue_dot('runtime.seconds', $video);
$sourceVideoID = tutor_utils()->avalue_dot('source_video_id', $video);
$poster = tutor_utils()->avalue_dot('poster', $video);


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
							<?php _e('Level', 'tutor'); ?>
                        </label>

                        <div class="tutor-form-field ">
							<?php
							foreach ($levels as $level_key => $level){
								?>
                                <label>
                                    <input type="radio" name="course_level" value="<?php echo $level_key; ?>" <?php $course_level ? checked($level_key, $course_level) : $level_key === 'intermediate' ? checked(1, 1): ''; ?> > <?php echo $level; ?>

                                </label>
								<?php
							}
							?>
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







            <div class="tutor-form-row">
                <div class="tutor-form-col-12">

                    <div class="tutor-form-label">
                        <label for="">
			                <?php
			                if ($post->post_type === tutor()->course_post_type){
				                _e('Course Intro Video', 'tutor');
			                }else{
				                _e('Video Source', 'tutor');
			                }
			                ?>
                        </label>
                    </div>

                    <div class="tutor-form-field tutor-option-field">
                        <select name="video[source]" class="tutor_lesson_video_source tutor_select2">
                            <option value=""><?php _e('Select Video Source', 'tutor'); ?></option>
                            <option value="html5" <?php selected('html5', $videoSource); ?> ><?php _e('HTML5 (mp4)', 'tutor'); ?></option>
                            <option value="youtube" <?php selected('youtube', $videoSource); ?>><?php _e('YouTube', 'tutor'); ?></option>
                            <option value="vimeo" <?php selected('vimeo', $videoSource); ?>><?php _e('Vimeo', 'tutor'); ?></option>
                        </select>

                        <p class="desc">
			                <?php _e('Select the video type and place video value below.', 'tutor'); ?>
                        </p>

                        <div class="tutor-lesson-video-input">
                            <div class="video_source_wrap_html5"  style="display: <?php echo $videoSource === 'html5' ? 'block' : 'none'; ?>;">
                                <a href="javascript:;" class="video_upload_btn"><i class="dashicons dashicons-upload"></i> </a>
                                <input type="hidden" name="video[source_video_id]" value="<?php echo $sourceVideoID; ?>" >
                                <p style="display: <?php echo $sourceVideoID ? 'block' : 'none'; ?>;"><?php _e('Media ID', 'tutor'); ?>: <span class="video_media_id"><?php echo $sourceVideoID; ?></span></p>
                            </div>

                            <div class="video_source_wrap_youtube" style="display: <?php echo $videoSource === 'youtube' ? 'block' :
				                'none'; ?>;">
                                <input type="text" name="video[source_youtube]" value="<?php echo tutor_utils()->avalue_dot('source_youtube', $video); ?>" placeholder="<?php _e('YouTube Video URL', 'tutor'); ?>">
                            </div>
                            <div class="video_source_wrap_vimeo" style="display: <?php echo $videoSource === 'vimeo' ? 'block' : 'none'; ?>;">
                                <input type="text" name="video[source_vimeo]" value="<?php echo tutor_utils()->avalue_dot('source_vimeo', $video); ?>" placeholder="<?php _e('Vimeo Video URL', 'tutor'); ?>">
                            </div>
                        </div>

                    </div>

                </div>

            </div>




            <div class="tutor-form-row">
                <div class="tutor-col-12">
                    <div class="tutor-option-field-label">
                        <label for=""><?php _e('Video Run Time', 'tutor'); ?></label>
                    </div>
                    <div class="tutor-option-field">
                        <div class="tutor-option-gorup-fields-wrap">
                            <div class="tutor-lesson-video-runtime">

                                <div class="tutor-form-row">

                                    <div class="tutor-col-3">
                                        <input type="text" value="<?php echo $runtimeHours ? $runtimeHours : '00'; ?>" name="video[runtime][hours]">
                                        <p><?php _e('HH', 'tutor'); ?></p>
                                    </div>

                                    <div class="tutor-col-3">
                                        <input type="text" value="<?php echo $runtimeMinutes ? $runtimeMinutes : '00'; ?>" name="video[runtime][minutes]">
                                        <p><?php _e('MM', 'tutor'); ?></p>
                                    </div>

                                    <div class="tutor-col-3">
                                        <input type="text" value="<?php echo $runtimeSeconds ? $runtimeSeconds : '00'; ?>" name="video[runtime][seconds]">
                                        <p><?php _e('SS', 'tutor'); ?></p>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="tutor-option-field-row tutor-video-poster-field" style="display: <?php echo $videoSource === 'html5' ? 'block': 'none'; ?>;">
                <div class="tutor-option-field-label">
                    <label for=""><?php _e('Video Poster', 'tutor'); ?></label>
                </div>
                <div class="tutor-option-field">
                    <div class="tutor-option-gorup-fields-wrap">
                        <div class="tutor-video-poster-wrap">
                            <p class="video-poster-img">
								<?php
								if ($poster){
									echo '<img src="'.wp_get_attachment_image_url($poster).'" alt="" /> ';
								}
								?>
                            </p>
                            <input type="hidden" name="video[poster]" value="<?php echo $poster; ?>">
                            <button type="button" class="tutor_video_poster_upload_btn button button-link"><?php _e('Upload', 'tutor'); ?></button>
                        </div>

                    </div>
                </div>
            </div>




            <div class="tutor-form-row">
                <div class="tutor-col-12">
                    <div class="tutor-course-builder-wrap">
	                    <?php include  tutor()->path.'views/metabox/course-topics.php'; ?>
                    </div>
                </div>
            </div>




        </div>


	    <?php do_action('tutor/dashboard_course_builder_form_field_after'); ?>



    </div>


</form>



<?php do_action('tutor/dashboard_course_builder_after'); ?>
