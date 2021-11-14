<?php
/**
 * Display the content
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

global $post;
global $previous_id;
global $next_id;

$content_id = tutor_utils()->get_post_id($course_content_id);
$contents = tutor_utils()->get_course_prev_next_contents_by_id($content_id);
$previous_id = $contents->previous_id;
$next_id = $contents->next_id;

$jsonData = array();
$jsonData['post_id'] = get_the_ID();
$jsonData['best_watch_time'] = 0;
$jsonData['autoload_next_course_content'] = (bool) get_tutor_option('autoload_next_course_content');

$best_watch_time = tutor_utils()->get_lesson_reading_info(get_the_ID(), 0, 'video_best_watched_time');
if ($best_watch_time > 0){
	$jsonData['best_watch_time'] = $best_watch_time;
}
?>

<?php do_action('tutor_lesson/single/before/content'); ?>

<div class="tutor-single-page-top-bar d-flex justify-content-between">
    <div class="tutor-topbar-item tutor-topbar-sidebar-toggle tutor-hide-sidebar-bar flex-center">
        <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">
            <span class="ttr-icon-light-left-line color-text-white flex-center"></span>
        </a>
    </div>
    <div class="tutor-topbar-item tutor-topbar-content-title-wrap flex-center">
        <?php

        if ($post->post_type === 'tutor_quiz') {
            echo wp_kses_post( '<span class="ttr-quiz-filled color-text-white tutor-mr-5"></span>' );
            echo wp_kses_post( '<span class="text-regular-caption color-design-white">' );
            esc_html_e( 'Quiz: ', 'tutor' );
            the_title(); 
            echo wp_kses_post( '</span>' );
        } elseif ($post->post_type === 'tutor_assignments'){
            echo wp_kses_post( '<span class="ttr-assignment-filled color-text-white tutor-mr-5"></span>' );
            echo wp_kses_post( '<span class="text-regular-caption color-design-white">' );
            esc_html_e( 'Assignment: ', 'tutor' );
            the_title(); 
            echo wp_kses_post( '</span>' );
        } elseif ($post->post_type === 'tutor_zoom_meeting'){
            echo wp_kses_post( '<span class="ttr-zoom-brand color-text-white tutor-mr-5"></span>' );
            echo wp_kses_post( '<span class="text-regular-caption color-design-white">' );
            esc_html_e( 'Zoom Meeting: ', 'tutor' );
            the_title(); 
            echo wp_kses_post( '</span>' );
        } else{
            echo wp_kses_post( '<span class="ttr-youtube-brand color-text-white tutor-mr-5"></span>' );
            echo wp_kses_post( '<span class="text-regular-caption color-design-white">' );
            esc_html_e( 'Lesson: ', 'tutor' );
            the_title(); 
            echo wp_kses_post( '</span>' );
        }

        ?>
    </div>

    <div class="tutor-topbar-item tutor-topbar-mark-to-done flex-center">
        <?php tutor_lesson_mark_complete_html(); ?>
    </div>
    <div class="tutor-topbar-cross-icon flex-center">
        <?php $course_id = tutor_utils()->get_course_id_by('lesson', get_the_ID()); ?>
        <a href="<?php echo get_the_permalink($course_id); ?>">
            <span class="ttr-line-cross-line color-text-white flex-center"></span>
        </a>
    </div>

</div>

<div class="course-players flex-center">
    <input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr(json_encode($jsonData)); ?>">
	<?php tutor_lesson_video(); ?>
    <div class="tutor-lesson-prev flex-center">
        <a href="<?php echo get_the_permalink($previous_id); ?>">
            <span class="ttr-angle-left-filled"></span>
        </a>
    </div>
    <div class="tutor-lesson-next flex-center">
        <a href="<?php echo get_the_permalink($next_id); ?>">
            <span class="ttr-angle-right-filled"></span>
        </a>
    </div>
</div>

<div class="tutor-course-spotlight-wrapper">
    <div class="tutor-default-tab tutor-course-details-tab">
        <div class="tab-header tutor-bs-d-flex justify-content-center">
            <div class="tab-header-item flex-center is-active" data-tutor-tab-target="tutor-course-details-tab-1">
                <span class="ttr-document-alt-filled"></span>
                <span><?php _e('Overview','tutor'); ?></span>
            </div>
            <div class="tab-header-item flex-center" data-tutor-tab-target="tutor-course-details-tab-2">
                <span class="ttr-attach-filled"></span>
                <span><?php _e('Exercise Files','tutor'); ?></span>
            </div>
            <div class="tab-header-item flex-center" data-tutor-tab-target="tutor-course-details-tab-3">
                <span class="ttr-comment-filled"></span>
                <span><?php _e('Comments','tutor'); ?></span>
            </div>
        </div>
        <div class="tab-body">
            <div class="tab-body-item is-active" id="tutor-course-details-tab-1">
                <div class="text-medium-h6 color-text-primary"><?php _e('About Lesson','tutor'); ?></div>
                <div class="text-regular-body color-text-subsued tutor-mt-12">
                    <?php the_content(); ?>
                </div>
            </div>
            <div class="tab-body-item" id="tutor-course-details-tab-2">
                <div class="text-medium-h6 color-text-primary"><?php _e('Exercise Files','tutor'); ?></div>
                <?php get_tutor_posts_attachments(); ?>
            </div>
            <div class="tab-body-item" id="tutor-course-details-tab-3">
                <div class="text-medium-h6 color-text-primary">Join the conversation</div>
                <div class="tutor-conversation tutor-mt-12 tutor-pb-20 tutor-pb-sm-50">
                    <div class="tutor-no-comments-show d-flex">
                        <svg width="239" height="76" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M98.164 8.082h58.871c1.497 0 2.711 1.178 2.711 2.615v28.708c0 1.438-1.214 2.615-2.711 2.615h-2.063l.303 7.028c.041.818-.91 1.357-1.639.898l-12.704-7.926H98.164c-1.497 0-2.71-1.158-2.71-2.615V10.697c0-1.457 1.213-2.615 2.71-2.615Z"
                                fill="#E3E5EA" />
                            <path d="M.332 74.96s107.705-61.07 238.333.34L.332 74.96Z" fill="url(#a)" />
                            <path
                                d="m136.663 5.587 4.309 4.551c.627.66 1.618-.34.991-.978l-4.309-4.552c-.607-.659-1.619.32-.991.979Z"
                                fill="#C1C3CA" />
                            <path
                                d="M140.749 4.589 136.44 9.14c-.627.658.384 1.637.991.998l4.309-4.552c.648-.659-.364-1.657-.991-.998Z"
                                fill="#C1C3CA" />
                            <path
                                d="M139.637.097H81.089c-1.497 0-2.69 1.198-2.69 2.655v29.307c0 1.477 1.213 2.655 2.69 2.655h2.064l-.304 7.167c-.04.838.91 1.377 1.619.918l12.644-8.085h42.525c1.497 0 2.69-1.198 2.69-2.655V2.752c0-1.477-1.193-2.655-2.69-2.655Z"
                                fill="#CDCFD4" />
                            <path d="M75.75 48.429v7.127" stroke="#C1C3CA" stroke-width="5"
                                stroke-miterlimit="10" />
                            <path
                                d="M79.27 44.835c-2.468-8.225-3.459-9.063-3.459-9.063s-.769.918-3.44 9.063c-2.346 7.107 1.801 7.147 2.914 7.047-.02 0 6.373.919 3.986-7.047Z"
                                fill="#E5E7EA" />
                            <path d="M165.551 48.987v7.127" stroke="#C1C3CA" stroke-width="5"
                                stroke-miterlimit="10" />
                            <path
                                d="M169.072 45.374c-2.468-8.225-3.459-9.064-3.459-9.064s-.769.919-3.46 9.064c-2.347 7.107 1.801 7.147 2.913 7.047 0 0 6.393.939 4.006-7.047Z"
                                fill="#E5E7EA" />
                            <path d="M175.184 53.42v6.169" stroke="#C1C3CA" stroke-width="5"
                                stroke-miterlimit="10" />
                            <path
                                d="M178.219 50.286c-2.145-7.127-2.994-7.866-2.994-7.866s-.668.798-2.995 7.866c-2.023 6.148 1.558 6.188 2.529 6.108 0 0 5.544.819 3.46-6.108Z"
                                fill="#E5E7EA" />
                            <path d="M65.676 52.082v6.169" stroke="#C1C3CA" stroke-width="5"
                                stroke-miterlimit="10" />
                            <path
                                d="M68.73 48.968c-2.144-7.127-2.994-7.866-2.994-7.866s-.667.78-2.994 7.866c-2.023 6.15 1.558 6.189 2.529 6.11-.02 0 5.523.798 3.46-6.11Z"
                                fill="#E5E7EA" />
                            <path
                                d="M81.96 3.35a.623.623 0 0 0 .626-.619.623.623 0 0 0-.627-.619.623.623 0 0 0-.627.62c0 .341.28.618.627.618ZM83.842 3.35a.623.623 0 0 0 .627-.619.623.623 0 0 0-.627-.619.623.623 0 0 0-.627.62c0 .341.28.618.627.618ZM85.725 3.35a.623.623 0 0 0 .627-.619.623.623 0 0 0-.627-.619.623.623 0 0 0-.627.62c0 .341.28.618.627.618Z"
                                fill="#E3E5EA" />
                            <path
                                d="M100.834 11.217a404.607 404.607 0 0 1 2.954 3.134c.465.5 1.214-.24.749-.738a404.607 404.607 0 0 1-2.954-3.134c-.465-.5-1.214.239-.749.738Z"
                                fill="#FCFCFD" />
                            <path
                                d="M103.647 10.479a424.634 424.634 0 0 0-2.953 3.134c-.466.499.283 1.238.748.738a404.607 404.607 0 0 0 2.954-3.134c.465-.499-.283-1.238-.749-.739ZM117.221 11.217a424.837 424.837 0 0 1 2.954 3.134c.465.5 1.214-.24.748-.738a424.634 424.634 0 0 1-2.953-3.134c-.466-.5-1.214.239-.749.738Z"
                                fill="#FCFCFD" />
                            <path
                                d="M120.054 10.479a404.607 404.607 0 0 0-2.954 3.134c-.465.499.283 1.238.748.738a404.607 404.607 0 0 0 2.954-3.134c.465-.499-.283-1.238-.748-.739ZM105.348 21.937c.445.32.951.798 1.558.619.465-.12.91-.919 1.335-.959.263-.02.789.759 1.153.899s.708.06 1.012-.16c.202-.14.384-.48.587-.559.202-.06-.021-.08.202-.04.101.02.303.3.384.38.182.179.344.359.587.439.283.1.607.08.87-.06.283-.16.344-.52.587-.68.222-.139.263.06.546.3.202.18.425.4.708.48.728.16 1.133-.46 1.517-.919.445-.519-.303-1.258-.748-.738-.081.1-.385.578-.506.598-.162.02-.445-.379-.607-.499-.405-.34-.89-.539-1.396-.22-.223.14-.324.52-.546.64-.203.12-.162.04-.405-.2-.404-.42-.87-.839-1.497-.56-.222.1-.405.3-.566.46-.263.24-.243.36-.486.26-.506-.18-.627-.799-1.254-.879-.607-.08-1.012.46-1.457.779-.182.12-.222.2-.384.18-.182-.02-.506-.34-.648-.46-.586-.379-1.112.52-.546.899Z"
                                fill="#FCFCFD" />
                            <defs>
                                <linearGradient id="a" x1="118.84" y1="42.749" x2="119.027" y2="81.964"
                                    gradientUnits="userSpaceOnUse">
                                    <stop offset=".239" stop-color="#EFF1F5" />
                                    <stop offset=".366" stop-color="#F1F3F6" stop-opacity=".833" />
                                    <stop offset=".896" stop-color="#F6F7F8" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <p class="text-regular-h6 color-text-subsued tutor-ml-sm-30 tutor-mt-30 tutor-mt-sm-0">
                            There are no commnets<br> posted here yet
                        </p>
                    </div>
                    <div class="tutor-comment-box tutor-mt-32">
                        <div class="comment-avatar">
                            <img src="https://i.imgur.com/CckCKYQ.jpeg" alt="">
                        </div>
                        <div class="tutor-comment-textarea">
                            <textarea placeholder="Write a comments" class="tutor-form-control"></textarea>
                        </div>
                        <div class="tutor-comment-submit-btn">
                            <button class="tutor-btn tutor-btn-primary tutor-btn-sm">Submit</button>
                        </div>
                    </div>
                    <div class="tutor-comments-list tutor-parent-comment tutor-mt-30" data-comment-line="200">
                        <div class="comment-avatar">
                            <img src="https://i.imgur.com/CckCKYQ.jpeg" alt="">
                        </div>
                        <div class="tutor-single-comment">
                            <div class="tutor-actual-comment tutor-mb-10">
                                <div class="tutor-comment-author">
                                    <span class="text-bold-body">Estella Clayton</span>
                                    <span class="text-regular-caption tutor-ml-10 tutor-ml-sm-10">1 week
                                        ago</span>
                                </div>
                                <div class="tutor-comment-text text-regular-body tutor-mt-5">
                                    No need to convince ordinary smartphone users who do not understand worth of
                                    it
                                </div>
                            </div>
                            <div class="tutor-comment-actions tutor-ml-22">
                                <span class="text-regular-body color-text-title">reply</span>
                                <span class="text-regular-body color-text-title">like</span>
                                <span class="text-regular-body color-text-title">edit</span>
                                <span class="text-regular-body color-text-title">delete</span>
                            </div>
                            <div class="tutor-comments-list tutor-child-comment tutor-mt-30">
                                <div class="comment-avatar">
                                    <img src="https://i.imgur.com/CckCKYQ.jpeg" alt="">
                                </div>
                                <div class="tutor-single-comment">
                                    <div class="tutor-actual-comment tutor-mb-10">
                                        <div class="tutor-comment-author">
                                            <span class="text-bold-body">Estella Clayton</span>
                                            <span class="text-regular-caption tutor-ml-0 tutor-ml-sm-10">1 week
                                                ago</span>
                                        </div>
                                        <div class="tutor-comment-text text-regular-body tutor-mt-5">
                                            No need to convince ordinary smartphone users who do not understand
                                            worth of it
                                        </div>
                                    </div>
                                    <div class="tutor-comment-actions tutor-ml-22">
                                        <span class="text-regular-body color-text-title">reply</span>
                                        <span class="text-regular-body color-text-title">like</span>
                                    </div>
                                </div>
                            </div>
                            <div class="tutor-comments-list tutor-child-comment tutor-mt-30">
                                <div class="comment-avatar">
                                    <img src="https://i.imgur.com/CckCKYQ.jpeg" alt="">
                                </div>
                                <div class="tutor-single-comment">
                                    <div class="tutor-actual-comment tutor-mb-10">
                                        <div class="tutor-comment-author">
                                            <span class="text-bold-body">Estella Clayton</span>
                                            <span class="text-regular-caption tutor-ml-0 tutor-ml-sm-10">1 week
                                                ago</span>
                                        </div>
                                        <div class="tutor-comment-text text-regular-body tutor-mt-5">
                                            No need to convince ordinary smartphone users who do not understand
                                            worth of it
                                        </div>
                                    </div>
                                    <div class="tutor-comment-actions tutor-ml-22">
                                        <span class="text-regular-body color-text-title">reply</span>
                                        <span class="text-regular-body color-text-title">like</span>
                                    </div>
                                </div>
                            </div>
                            <div class="tutor-comment-box tutor-reply-box tutor-mt-20">
                                <div class="comment-avatar">
                                    <img src="https://i.imgur.com/CckCKYQ.jpeg" alt="">
                                </div>
                                <div class="tutor-comment-textarea">
                                    <textarea placeholder="Write a comments"
                                        class="tutor-form-control"></textarea>
                                </div>
                                <div class="tutor-comment-submit-btn">
                                    <button class="tutor-btn tutor-btn-primary tutor-btn-sm">Reply</button>
                                </div>
                            </div>
                        </div>
                        <span class="tutor-comment-line"></span>
                    </div>
                    <div class="tutor-comments-list tutor-parent-comment tutor-mt-30">
                        <div class="comment-avatar">
                            <img src="https://i.imgur.com/CckCKYQ.jpeg" alt="">
                        </div>
                        <div class="tutor-single-comment">
                            <div class="tutor-actual-comment tutor-mb-10">
                                <div class="tutor-comment-author">
                                    <span class="text-bold-body">Estella Clayton</span>
                                    <span class="text-regular-caption tutor-ml-10 tutor-ml-sm-10">1 week
                                        ago</span>
                                </div>
                                <div class="tutor-comment-text text-regular-body tutor-mt-5">
                                    No need to convince ordinary smartphone users who do not understand worth of
                                    it
                                </div>
                            </div>
                            <div class="tutor-comment-actions tutor-ml-22">
                                <span class="text-regular-body color-text-title">reply</span>
                                <span class="text-regular-body color-text-title">like</span>
                            </div>
                            <div class="tutor-comments-list tutor-child-comment tutor-mt-30">
                                <div class="comment-avatar">
                                    <img src="https://i.imgur.com/CckCKYQ.jpeg" alt="">
                                </div>
                                <div class="tutor-single-comment">
                                    <div class="tutor-actual-comment tutor-mb-10">
                                        <div class="tutor-comment-author">
                                            <span class="text-bold-body">Estella Clayton</span>
                                            <span class="text-regular-caption tutor-ml-0 tutor-ml-sm-10">1 week
                                                ago</span>
                                        </div>
                                        <div class="tutor-comment-text text-regular-body tutor-mt-5">
                                            No need to convince ordinary smartphone users who do not understand
                                            worth of
                                            it
                                        </div>
                                    </div>
                                    <div class="tutor-comment-actions tutor-ml-22">
                                        <span class="text-regular-body color-text-title">reply</span>
                                        <span class="text-regular-body color-text-title">like</span>
                                        <span class="text-regular-body color-text-title">edit</span>
                                        <span class="text-regular-body color-text-title">delete</span>
                                    </div>
                                </div>
                            </div>
                            <div class="tutor-comments-list tutor-child-comment tutor-mt-30">
                                <div class="comment-avatar">
                                    <img src="https://i.imgur.com/CckCKYQ.jpeg" alt="">
                                </div>
                                <div class="tutor-single-comment">
                                    <div class="tutor-actual-comment tutor-mb-10">
                                        <div class="tutor-comment-author">
                                            <span class="text-bold-body">Estella Clayton</span>
                                            <span class="text-regular-caption tutor-ml-0 tutor-ml-sm-10">1 week
                                                ago</span>
                                        </div>
                                        <div class="tutor-comment-text text-regular-body tutor-mt-5">
                                            No need to convince ordinary
                                            <!-- smartphone users who do not understand
                                            worth of
                                            it -->
                                        </div>
                                    </div>
                                    <div class="tutor-comment-actions tutor-ml-22">
                                        <span class="text-regular-body color-text-title">reply</span>
                                        <span class="text-regular-body color-text-title">like</span>
                                        <span class="text-regular-body color-text-title">edit</span>
                                        <span class="text-regular-body color-text-title">delete</span>
                                    </div>
                                </div>
                            </div>
                            <div class="tutor-comment-box tutor-reply-box tutor-mt-20">
                                <div class="comment-avatar">
                                    <img src="https://i.imgur.com/CckCKYQ.jpeg" alt="">
                                </div>
                                <div class="tutor-comment-textarea">
                                    <textarea placeholder="Write a comments"
                                        class="tutor-form-control"></textarea>
                                </div>
                                <div class="tutor-comment-submit-btn">
                                    <button class="tutor-btn tutor-btn-primary tutor-btn-sm">Reply</button>
                                </div>
                            </div>
                        </div>
                        <span class="tutor-comment-line"></span>
                    </div>
                </div>
                <div
                    class="tutor-comment-list-footer color-design-brand tutor-p-20 d-flex align-items-center justify-content-center">
                    <span class="ttr-comment-filled tutor-mr-10"></span>
                    <p class="text-regular-body color-text-subsued">Write a comments</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php do_action('tutor_lesson/single/after/content'); ?>