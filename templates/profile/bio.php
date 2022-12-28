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
$allowed_tags = array(
	'p'      => array(),
	'a'      => array(
		'href'   => 1,
		'target' => 1,
		'rel'    => 1,
	),
	'span'   => array( 'style' => 1 ),
	'br'     => array(),
	'em'     => array(),
	'strong' => array(),
	'u'      => array(),
	'ul'     => array(),
	'ol'     => array(),
	'li'     => array(),
);

if ( $profile_bio ) {
	?>
	<?php echo wp_kses( wpautop( $profile_bio ), $allowed_tags ); ?>
	<?php
} else {
	esc_html_e( 'Bio data is empty', 'tutor' );
}
