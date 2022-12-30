<?php
/**
 * Profile Bio Template
 *
 * @package Tutor\Templates
 * @subpackage Profile
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

$user_name = sanitize_text_field( get_query_var( 'tutor_profile_username' ) );
$get_user  = tutor_utils()->get_user_by_login( $user_name );
$user_id   = $get_user->ID;


$profile_bio  = get_user_meta( $user_id, '_tutor_profile_bio', true );

if ( $profile_bio ) {
	?>
	<?php echo wp_kses_post( wpautop( $profile_bio ) ); ?>
	<?php
} else {
	esc_html_e( 'Bio data is empty', 'tutor' );
}
