<?php
/**
 * Created by PhpStorm.
 * User: mhshohel
 * Date: 19/6/19
 * Time: 1:19 PM
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

?>

<?php do_action('tutor_assignment/single/before/content'); ?>

    <div class="tutor-single-page-top-bar">
        <div class="tutor-topbar-item tutor-hide-sidebar-bar">
            <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar"><i class="tutor-icon-menu-2"></i> </a>
			<?php $course_id = get_post_meta(get_the_ID(), '_tutor_course_id_for_assignments', true); ?>
            <a href="<?php echo get_the_permalink($course_id); ?>" class="tutor-topbar-home-btn">
                <i class="tutor-icon-next-2"></i> <?php echo __('Go to Course Home', 'tutor') ; ?>
            </a>
        </div>
        <div class="tutor-topbar-item tutor-topbar-content-title-wrap">
			<?php
			tutor_utils()->get_lesson_type_icon(get_the_ID(), true, true);
			the_title(); ?>
        </div>

    </div>



    <div class="tutor-lesson-content-area">


        <div class="tutor-assignment-title">
            <h2><?php the_title(); ?></h2>
        </div>

        <div class="tutor-assignment-information">
            <p><?php _e('Total Marks', 'tutor'); ?> : <?php echo tutor_utils()->get_assignment_option(get_the_ID(), 'total_mark'); ?> </p>
            <p><?php _e('Passing Marks', 'tutor'); ?> : <?php echo tutor_utils()->get_assignment_option(get_the_ID(),'pass_mark'); ?> </p>
        </div>

        <hr />

        <div class="tutor-assignment-content">
			<?php the_content(); ?>
        </div>

		<?php
		$is_submitting = tutor_utils()->is_assignment_submitting(get_the_ID());
		if ($is_submitting){
			?>

            <div class="tutor-assignment-submit-form-wrap">

                <h2><?php _e('Assignment answer form', 'tutor'); ?></h2>


                <form action="" method="post" id="tutor_assignment_submit_form" enctype="multipart/form-data">
	                <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
                    <input type="hidden" value="tutor_assignment_submit" name="tutor_action"/>
                    <input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">

                    <?php
                    $allowd_upload_files = (int) tutor_utils()->get_assignment_option(get_the_ID(), 'upload_files_limit');
                    ?>


                    <div class="tutor-form-group">
                        <p><?php _e('Write your answer briefly', 'tutor'); ?></p>
                        <textarea name="assignment_answer"></textarea>
                    </div>


                    <?php
                    if ($allowd_upload_files){
                        ?>
                        <p>Attach assignment files</p>
                        <?php
                        for ($item = 1; $item <= $allowd_upload_files; $item++){
                            ?>
                            <div class="tutor-form-group">
                                <input type="file" name="attached_assignment_files[]">
                            </div>
                            <?php
                        }
                    }
                    ?>

                    <div class="tutor-assignment-submit-btn-wrap">
                        <button type="submit" class="tutor-btn" id="tutor_assignment_submit_btn"> <?php _e('Submit Assignment', 'tutor');
		                    ?> </button>
                    </div>

                </form>

            </div>

			<?php
		}else{

			$submitted_assignment = tutor_utils()->is_assignment_submitted(get_the_ID());
			if ($submitted_assignment){
			    ?>

                <div class="tutor-assignments-submitted-answers-wrap">

                    <h2><?php _e('Your Answers', 'tutor'); ?></h2>

                    <?php echo nl2br(stripslashes($submitted_assignment->comment_content)); ?>

                    <?php

                    $attached_files = get_comment_meta($submitted_assignment->comment_ID, 'uploaded_attachments', true);

                    if ($attached_files){
	                    $attached_files = json_decode($attached_files, true);

	                    if (tutor_utils()->count($attached_files)){

	                        ?>
                            <h2><?php _e('Your uploaded file(s)', 'tutor'); ?></h2>

		                    <?php

		                    $upload_dir = wp_get_upload_dir();
		                    $upload_baseurl = trailingslashit(tutor_utils()->array_get('baseurl', $upload_dir));

	                        foreach ($attached_files as $attached_file){
	                            ?>

                                <div class="uploaded-files">

                                    <a href="<?php echo $upload_baseurl.tutor_utils()->array_get('uploaded_path', $attached_file) ?>" target="_blank"><?php echo tutor_utils()->array_get('name', $attached_file); ?></a>


                                </div>

                                <?php
                            }
                        }

                    }


                    ?>

                </div>



                <?php
            }else {


				?>

                <div class="tutor-assignment-start-btn-wrap">
                    <form action="" method="post" id="tutor_assignment_start_form">
						<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
                        <input type="hidden" value="tutor_assignment_start_submit" name="tutor_action"/>
                        <input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">

                        <button type="submit" class="tutor-btn"
                                id="tutor_assignment_start_btn"> <?php _e( 'Start assignment submit', 'tutor' ); ?> </button>
                    </form>
                </div>

				<?php
			}
        }
		?>




    </div>

<?php do_action('tutor_assignment/single/after/content'); ?>