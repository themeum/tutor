<?php

/**
 * Display global login
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */


if ( ! defined( 'ABSPATH' ) )
	exit;
?>

<div class="tutor-login-form-wrap">
	<?php //wp_login_form(); ?>

    <?php
    $args = array(
	    'echo' => true,
	    // Default 'redirect' value takes the user back to the request URI.
	    'redirect' => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
	    'form_id' => 'loginform',
	    'label_username' => __( 'Username or Email Address' ),
	    'label_password' => __( 'Password' ),
	    'label_remember' => __( 'Remember Me' ),
	    'label_log_in' => __( 'Log In' ),
	    'id_username' => 'user_login',
	    'id_password' => 'user_pass',
	    'id_remember' => 'rememberme',
	    'id_submit' => 'wp-submit',
	    'remember' => true,
	    'value_username' => '',
	    // Set 'value_remember' to true to default the "Remember me" checkbox to checked.
	    'value_remember' => false,
	    'wp_lostpassword_url' => wp_lostpassword_url(),
	    'wp_lostpassword_label' => __('Forgot Password?'),
    );

    $form = '
		<form name="' . $args['form_id'] . '" id="' . $args['form_id'] . '" action="' . esc_url( site_url( 'wp-login.php', 'login_post' ) ) . '" method="post">
			<p class="login-username">
				<label for="' . esc_attr( $args['id_username'] ) . '">' . esc_html( $args['label_username'] ) . '</label>
				<input type="text" name="log" id="' . esc_attr( $args['id_username'] ) . '" class="input" value="' . esc_attr( $args['value_username'] ) . '" size="20" />
			</p>
			<p class="login-password">
				<label for="' . esc_attr( $args['id_password'] ) . '">' . esc_html( $args['label_password'] ) . '</label>
				<input type="password" name="pwd" id="' . esc_attr( $args['id_password'] ) . '" class="input" value="" size="20" />
			</p>
			
			' . ( $args['remember'] ? '<p class="login-remember"><label><input name="rememberme" type="checkbox" id="' . esc_attr( $args['id_remember'] ) . '" value="forever"' . ( $args['value_remember'] ? ' checked="checked"' : '' ) . ' /> ' . esc_html( $args['label_remember'] ) . '</label></p>' : '' ) . '
			
			<a href="'.$args['wp_lostpassword_url'].'">'.$args['wp_lostpassword_label'].'</a>
			
			<p class="login-submit">
				<input type="submit" name="wp-submit" id="' . esc_attr( $args['id_submit'] ) . '" class="button button-primary" value="' . esc_attr( $args['label_log_in'] ) . '" />
				<input type="hidden" name="redirect_to" value="' . esc_url( $args['redirect'] ) . '" />
			</p>
		</form>';
    echo $form;
    ?>

    <a href="<?php echo tutor_utils()->student_register_url(); ?>">Register Now</a>
</div>
