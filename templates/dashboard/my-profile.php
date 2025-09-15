<?php
/**
 * My Profile Page
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

$uid  = get_current_user_id();
$user = get_userdata( $uid );

$profile_settings_link = tutor_utils()->get_tutor_dashboard_page_permalink( 'settings' );

$rdate = $user->user_registered;
$fname = $user->first_name;
$lname = $user->last_name;
$uname = $user->user_login;
$email = $user->user_email;

$phone = get_user_meta( $uid, 'phone_number', true );
$job   = nl2br( wp_strip_all_tags( get_user_meta( $uid, '_tutor_profile_job_title', true ) ) );
$bio   = get_user_meta( $uid, '_tutor_profile_bio', true );

$profile_data = array(
	'registration_date' => array(
		'label' => __( 'Registration Date', 'tutor' ),
		'value' => $rdate ? tutor_i18n_get_formated_date( tutor_utils()->get_local_time_from_unix( $rdate ) ) : '',
	),
	'first_name'        => array(
		'label' => __( 'First Name', 'tutor' ),
		'value' => $fname ? $fname : esc_html( '-' ),
	),
	'last_name'         => array(
		'label' => __( 'Last Name', 'tutor' ),
		'value' => $lname ? $lname : '-',
	),
	'username'          => array(
		'label' => __( 'Username', 'tutor' ),
		'value' => $uname,
	),
	'email'             => array(
		'label' => __( 'Email', 'tutor' ),
		'value' => $email,
	),
	'phone_number'      => array(
		'label' => __( 'Phone Number', 'tutor' ),
		'value' => $phone ? $phone : '-',
	),
	'skill_occupation'  => array(
		'label' => __( 'Skill/Occupation', 'tutor' ),
		'value' => $job ? $job : '-',
	),
	'biography'         => array(
		'label' => __( 'Biography', 'tutor' ),
		'value' => $bio ? $bio : '-',
	),
);

$profile_data = apply_filters( 'tutor_profile_data', $profile_data );
?>

<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24"><?php esc_html_e( 'My Profile', 'tutor' ); ?></div>
<div class="tutor-dashboard-content-inner tutor-dashboard-profile-data">
	<?php
	foreach ( $profile_data as $key => $data ) :
		?>
		<div class="tutor-row tutor-mb-24">
			<div class="tutor-col-12 tutor-col-sm-5 tutor-col-lg-3">
				<span class="tutor-fs-6 tutor-color-secondary"><?php echo esc_html( $data['label'] ); ?></span>
			</div>
			<div class="tutor-col-12 tutor-col-sm-7 tutor-col-lg-9">
			<?php
			echo 'biography' === $key ?
					'<span class="tutor-fs-6 tutor-color-secondary">' . wp_kses_post( wpautop( $data['value'] ) ) . '</span>'
					: '<span class="tutor-fs-6 tutor-fw-medium tutor-color-black">' . esc_html( $data['value'] ) . '</span>';

				do_action( 'tutor_after_profile_data_item_value', $key, $data );
			?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
