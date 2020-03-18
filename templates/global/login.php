<?php

/**
 * Display global login
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
?>

<div class="tutor-login-form-wrap">
	<?php //wp_login_form(); ?>

    <?php
    $current_url = tutils()->get_current_url();
    $register_page = tutor_utils()->student_register_url();
	$register_url = add_query_arg ('redirect_to', $current_url, $register_page);

	//redirect_to
    $args = array(
	    'echo'                      => true,
	    // Default 'redirect' value takes the user back to the request URI.
	    'redirect'                  => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
	    'form_id'                   => 'loginform',
	    'label_username'            => __( 'Username or Email Address', 'tutor' ),
	    'label_password'            => __( 'Password', 'tutor' ),
	    'label_remember'            => __( 'Remember Me', 'tutor' ),
	    'label_log_in'              => __( 'Log In', 'tutor' ),
	    'label_create_new_account'  => __( 'Create a new account', 'tutor' ),
	    'id_username'               => 'user_login',
	    'id_password'               => 'user_pass',
	    'id_remember'               => 'rememberme',
	    'id_submit'                 => 'wp-submit',
	    'remember'                  => true,
	    'value_username'            => tutils()->input_old('log'),
	    // Set 'value_remember' to true to default the "Remember me" checkbox to checked.
	    'value_remember'            => false,
	    'wp_lostpassword_url'       => apply_filters('tutor_lostpassword_url', wp_lostpassword_url()),
	    'wp_lostpassword_label'     => __('Forgot Password?', 'tutor'),
    );

    //action="' . esc_url( site_url( 'wp-login.php', 'login_post' ) ) . '"

	tutor_alert(null, 'warning');

	ob_start();
	tutor_nonce_field();
	$nonce_field = ob_get_clean();

    $form = '
		<form name="' . $args['form_id'] . '" id="' . $args['form_id'] . '" method="post">
	
		'.$nonce_field.'
		
		<input type="hidden" name="tutor_action" value="tutor_user_login" />
			<p class="login-username">
				<input type="text" placeholder="'.esc_html( $args['label_username'] ).'" name="log" id="' . esc_attr( $args['id_username'] ) . '" class="input" value="' . esc_attr( $args['value_username'] ) . '" size="20" />
			</p>
			<p class="login-password">
				<input type="password" placeholder="'.esc_html( $args['label_password'] ).'" name="pwd" id="' . esc_attr( $args['id_password'] ) . '" class="input" value="" size="20" />
			</p>
			<div class="tutor-login-rememeber-wrap">
			' . ( $args['remember'] ? '<p class="login-remember"><label><input name="rememberme" type="checkbox" id="' . esc_attr( $args['id_remember'] ) . '" value="forever"' . ( $args['value_remember'] ? ' checked="checked"' : '' ) . ' /> ' . esc_html( $args['label_remember'] ) . '</label></p>' : '' ) . '
			
			    <a href="'.$args['wp_lostpassword_url'].'">'.$args['wp_lostpassword_label'].'</a>
			</div>
			<p class="login-submit">
				<input type="submit" name="wp-submit" id="' . esc_attr( $args['id_submit'] ) . '" class="button button-primary" value="' . esc_attr( $args['label_log_in'] ) . '" />
				<input type="hidden" name="redirect_to" value="' . esc_url( $args['redirect'] ) . '" />
			</p>
			<p class="tutor-form-register-wrap">
			    <a href="'. esc_url($register_url). '">'.$args['label_create_new_account'].'</a>
            </p>
		</form>';
    echo $form;

    #@TODO: student_register_url() return false, it must be an valid url.

    ?>
</div>
