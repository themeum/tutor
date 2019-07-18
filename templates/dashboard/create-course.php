<?php
if ( ! defined( 'ABSPATH' ) )
    exit;

get_tutor_header(true);
do_action('tutor_load_template_before', 'dashboard.create-course', null);
global $post;

$course_id = get_the_ID();
$can_publish_course = (bool) tutor_utils()->get_option('instructor_can_publish_course');

?>

<?php do_action('tutor/dashboard_course_builder_before'); ?>
<form action="" method="post" enctype="multipart/form-data">
    <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
    <header class="tutor-dashboard-builder-header">
        <div class="tutor-container tutor-fluid">
            <div class="tutor-row tutor-align-items-center">
                <div class="tutor-col">
                    <div class="tutor-dashboard-builder-header-left">
                        <div class="tutor-dashboard-builder-logo">
                            <?php $tutor_course_builder_logo_src = apply_filters('tutor_course_builder_logo_src', tutor()->url . 'assets/images/tutor-logo.png'); ?>
                            <img src="<?php echo esc_url($tutor_course_builder_logo_src); ?>" alt="">
                        </div>
                        <button type="submit" class="tutor-dashboard-builder-draft-btn" name="course_submit_btn" value="save_course_as_draft"><i class="tutor-icon-default"></i><span><?php _e('Save', 'tutor'); ?></span></button>
                    </div>
                </div>
                <div class="tutor-col-auto">
                    <div class="tutor-dashboard-builder-header-right">
                        <a href="<?php the_permalink($course_id); ?>" target="_blank"><i class="tutor-icon-glasses"></i><?php _e('Preview', 'tutor'); ?></a>
                        <?php
                        if ($can_publish_course){
                            ?>
                            <button class="tutor-button" type="submit" name="course_submit_btn" value="publish_course"><?php _e('Publish Course', 'tutor'); ?></button>
                            <?php
                        }else{
                            ?>
                            <button class="tutor-button" type="submit" name="course_submit_btn" value="submit_for_review"><?php _e('Submit for Review', 'tutor'); ?></button>
                            <?php
                        }
                        ?>
                        <a href="<?php echo tutor_utils()->tutor_dashboard_url(); ?>"> <?php _e('Exit', "tutor") ?></a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="tutor-frontend-course-builder-section">
        <div class="tutor-container">
            <div class="tutor-row">
                <div class="tutor-col-8">
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
                                    <label> <?php _e('Description', 'tutor'); ?></label>
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

                            <?php
                            $enable_course_sell_by_woocommerce = tutor_utils()->get_option('enable_course_sell_by_woocommerce');
                            $enable_tutor_edd = tutor_utils()->get_option('enable_tutor_edd');
                            if ($enable_course_sell_by_woocommerce || $enable_tutor_edd){
                                $course_price = tutor_utils()->get_raw_course_price(get_the_ID());
                                ?>
                                <div class="tutor-frontend-builder-item-scope">
                                    <div class="tutor-form-group">
                                        <label class="tutor-builder-item-heading">
                                            <?php _e('Course Price', 'tutor'); ?>
                                        </label>
                                        <input type="text" name="course_price" value="<?php echo $course_price->regular_price; ?>" placeholder="<?php _e('Set course price', 'tutor'); ?>">
                                    </div>
                                </div>
                                <?php
                            }
                            ?>

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
                                    <div class="tutor-form-field tutor-course-builder-btn-group">
                                        <button type="submit" class="tutor-button" name="course_submit_btn" value="save_course_as_draft"><?php _e('Save course as draft', 'tutor'); ?></button>
                                        <?php
                                        if ($can_publish_course){
                                            ?>
                                            <button class="tutor-button tutor-success" type="submit" name="course_submit_btn" value="publish_course"><?php _e('Publish Course', 'tutor'); ?></button>
                                            <?php
                                        }else{
                                            ?>
                                            <button class="tutor-button tutor-success" type="submit" name="course_submit_btn" value="submit_for_review"><?php _e('Submit for Review', 'tutor'); ?></button>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!--.tutor-col-8-->
                <div class="tutor-col-4">
                    <div class="tutor-course-builder-upload-tips">
                        <h3 class="tutor-course-builder-tips-title"><i class="tutor-icon-light-bulb"></i><?php _e('Course Upload Tips', 'tutor') ?></h3>
                        <ul>
                            <li><?php _e("Prerequisites", 'tutor');?></li>
                            <li><?php _e("NO Node.js knowledge is required at all!", 'tutor');?></li>
                            <li><?php _e("NO other programming language knowledge (besides JavaScript, see next point) is required", 'tutor');?></li>
                            <li><?php _e("Basic JavaScript knowledge is assumed though - you should at least be willing to pick it up whilst going through the course. A JS refresher module exists to bring you up to the latest syntax quickly", 'tutor');?></li>
                            <li><?php _e("Basic HTML + CSS knowledge helps but is NOT required", 'tutor');?></li>
                        </ul>
                    </div>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab architecto deserunt dignissimos doloribus ducimus facilis fuga fugiat incidunt laudantium libero magni natus necessitatibus numquam porro quas qui quidem repellat rerum saepe sapiente sed sint sunt, suscipit tempora tempore temporibus vel vitae. Accusamus aliquam atque consectetur cupiditate debitis dignissimos dolorum, eos exercitationem, fuga fugit illum impedit nostrum obcaecati odio quasi quo ratione tempore veniam? A architecto assumenda atque beatae debitis deleniti dignissimos dolores et expedita explicabo fugiat fugit ipsa iusto maiores maxime modi, molestias nihil nisi nobis non nostrum odio optio pariatur placeat quam quas qui quidem ratione reiciendis rem repudiandae saepe tempora temporibus unde veniam veritatis voluptas. Illo, obcaecati praesentium. Aliquam consequuntur, deserunt doloribus earum harum porro quia suscipit ullam velit voluptas! Autem corporis cumque, dolor ducimus et facere modi natus necessitatibus nobis nulla obcaecati odit omnis quae quas quasi quia rem ut voluptate! Aperiam commodi debitis dignissimos doloremque eaque, eum illum ipsa libero maxime necessitatibus nesciunt, nobis nostrum officia officiis quos rerum sint sit sunt suscipit voluptate! Cupiditate eaque eius, et incidunt minus nisi nulla quasi quidem recusandae tempore. Cumque, sed, voluptatum? Animi, beatae deserunt eligendi eum eveniet expedita magnam maxime minus nesciunt non porro sapiente tenetur voluptatum? Aliquam aperiam eum exercitationem fuga in nostrum suscipit tenetur? A aut consequatur ducimus labore nesciunt odit pariatur vel? Ad animi asperiores aspernatur assumenda at aut consequuntur debitis dicta dolorem eligendi et facere facilis fugit harum laborum laudantium libero magni maxime nostrum, nulla officiis, omnis porro quas quia quis quo repudiandae saepe sequi unde velit vero voluptate voluptates voluptatibus. Adipisci aliquam at, commodi consectetur delectus, doloremque doloribus eum fuga laudantium molestias natus, numquam officiis perspiciatis quam quo reiciendis repellat veniam? Aliquid harum ipsa minima nemo provident quia, rem rerum. A accusamus alias, aperiam cumque dolores enim est fugiat illo impedit in iste libero magni mollitia natus non perspiciatis quas quasi ratione sed sunt! Aut dolorem et eum itaque, optio vitae? Delectus, excepturi obcaecati quos reiciendis reprehenderit sapiente voluptatibus. Eligendi esse eum modi nihil suscipit. Ab cupiditate deserunt doloribus inventore iure optio quod rerum! Alias asperiores consectetur eum facere in itaque, modi necessitatibus omnis provident quam reprehenderit, rerum ullam vitae. Asperiores beatae commodi debitis, deserunt, dolorem eligendi est ex exercitationem facere harum id illo ipsa ipsum labore libero molestiae mollitia nesciunt nostrum obcaecati officiis perspiciatis porro provident quibusdam quo rem, rerum sed sequi temporibus ut veniam veritatis vero voluptas voluptates? Debitis quidem, reiciendis.</p>
                </div> <!--.tutor-col-4-->
            </div> <!--.tutor-row-->
        </div>
    </div>
</form>
<?php do_action('tutor/dashboard_course_builder_after'); ?>


<?php
do_action('tutor_load_template_after', 'dashboard.create-course', null);
get_tutor_footer(true); ?>
