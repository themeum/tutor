<?php
/**
 * Display single login
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 * 
 * Template content and design updated 
 * 
 * @version 1.9.6
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

get_header();

?>

<?php do_action('tutor_lesson/single/before/wrap'); ?>
    <div <?php tutor_post_class(); ?>>

        <div class="tutor-denied-wrapper">

            <div class="image-wrapper">
                <img src="<?php echo esc_url( tutor()->url.'assets/images/denied.png' )?>" alt="denied">
            </div>

            <div class="tutor-denied-content-wrapper">

                <div>
                    <img src="<?php echo esc_url( tutor()->url.'assets/images/tutor-logo.png' );?>" alt="tutor-logo">
                </div>

                <div>
                    <?php
                        $course_id = tutor_utils()->get_course_id_by( 'lesson', get_the_ID() );
                    ?>

                    <h2>
                        <?php _e( 'Permission Denied', 'tutor' ); ?>
                    </h2>
                    <p>
                        <?php _e( 'Please enroll in this course to view course content.', 'tutor' )?>
                    </p>
                    <p> 
                        <?php echo sprintf( __( 'Course name : %s', 'tutor' ), get_the_title( $course_id ) ); ?>
                    </p>
                </div>
                
                <div>
                    <a href="<?php echo get_permalink( $course_id ); ?>" class="tutor-button">
                        <?php _e( 'View Course', 'tutor' ); ?>
                    </a>
                </div>

            </div>

        </div>
    </div><!-- .wrap -->

<?php do_action('tutor_lesson/single/after/wrap'); ?>

<?php
get_footer();
