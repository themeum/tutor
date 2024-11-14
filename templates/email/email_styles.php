<?php
/**
 * E-mail styles
 *
 * @since 3.0.0
 *
 * @author Themeum
 */

$has_pro        = tutor()->has_pro;
$email_settings = null;
if ( $has_pro && tutor_utils()->is_addon_enabled( 'tutor-pro/addons/tutor-email/tutor-email.php' ) ) {
	$email_settings = new TUTOR_EMAIL\EmailSettings();
}

$email_logo_position   = get_tutor_option( 'email_logo_position', 'left' );
$email_button_position = get_tutor_option( 'email_template_button_position', 'center' );
$email_template_colors = $email_settings ? $email_settings::get_email_template_colors() : array();

$header_background_color      = $email_settings ? $email_settings::get_color( 'header_background_color', $email_template_colors ) : '#FFFFFF';
$header_divider_color         = $email_settings ? $email_settings::get_color( 'header_divider_color', $email_template_colors ) : '#E0E2EA';
$body_background_color        = $email_settings ? $email_settings::get_color( 'body_background_color', $email_template_colors ) : '#FFFFFF';
$email_title_color            = $email_settings ? $email_settings::get_color( 'email_title_color', $email_template_colors ) : '#212327';
$email_text_color             = $email_settings ? $email_settings::get_color( 'email_text_color', $email_template_colors ) : '#5B616F';
$email_short_code_color       = $email_settings ? $email_settings::get_color( 'email_short_code_color', $email_template_colors ) : '#212327';
$footnote_color               = $email_settings ? $email_settings::get_color( 'footnote_color', $email_template_colors ) : '#A4A8B2';
$primary_button_color         = $email_settings ? $email_settings::get_color( 'primary_button_color', $email_template_colors ) : '#3E64DE';
$primary_button_hover_color   = $email_settings ? $email_settings::get_color( 'primary_button_hover_color', $email_template_colors ) : '#395BCA';
$primary_button_text_color    = $email_settings ? $email_settings::get_color( 'primary_button_text_color', $email_template_colors ) : '#FFFFFF';
$secondary_button_color       = $email_settings ? $email_settings::get_color( 'secondary_button_color', $email_template_colors ) : '#FFFFFF';
$secondary_button_hover_color = $email_settings ? $email_settings::get_color( 'secondary_button_hover_color', $email_template_colors ) : '#395BCA';
$secondary_button_text_color  = $email_settings ? $email_settings::get_color( 'secondary_button_text_color', $email_template_colors ) : '#3E64DE';
?>

<style>
:root {
	--primary-button-color: <?php echo esc_attr( $primary_button_color ); ?>;
	--primary-button-hover-color: <?php echo esc_attr( $primary_button_hover_color ); ?>;
	--secondary-button-color: <?php echo esc_attr( $secondary_button_color ); ?>;
	--secondary-button-hover-color: <?php echo esc_attr( $secondary_button_hover_color ); ?>;
}
body{padding: 0px;margin: 0px;color: #5B616F;}
.tutor-email-body{font-weight:400;padding: 50px 20px 50px;color: #5B616F;background-color: #EFF1F6;line-height: 26px;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;}
.tutor-email-body a, .tutor-email-body strong {color: <?php echo esc_html( $email_short_code_color ); ?>;font-weight:500!important;text-decoration: none;}
.tutor-email-body a{ color: royalblue;}
.tutor-email-body table {width: 100%; font-size: 16px; border-spacing: 0;}
.tutor-email-body table td{padding: 0;margin: 0;}
.tutor-email-header{ background-color: <?php echo esc_html( $header_background_color ); ?>; border-bottom: 1px solid <?php echo esc_html( $header_divider_color ); ?>; padding: 20px 50px;}
.tutor-email-header table {padding: 0;margin: 0;}
.tutor-test-mail-notice{text-align: right;}
.email-user-content{font-weight:400!important;word-break: break-word;}
.tutor-email-content{background-color: <?php echo esc_html( $body_background_color ); ?>; color: <?php echo esc_html( $email_text_color ); ?>; padding:50px 50px 40px;}
.tutor-greetings-content{margin-bottom: 20px;}
.tutor-email-logo {display: block;text-align: <?php echo esc_html( $email_logo_position ); ?>;}
.tutor-email-logo img{display: inline-block;vertical-align: middle;}
.tutor-email-wrapper{background: #ffffff;box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.05);border-radius: 10px;max-width: 600px;margin: 0 auto;font-style: normal;font-weight: 400;font-size: 16px;overflow: hidden;}
.tutor-email-wrapper p {margin-top:0;padding:0;text-decoration: none;font-size: 16px;margin-bottom: 16px;color: inherit;}
.tutor-h-center{text-align: center;}
.tutor-email-greetings{color: <?php echo esc_html( $email_short_code_color ); ?>; font-weight: 400; font-size: 16px; line-height: 26px;margin-bottom: 10px!important;}
.tutor-email-heading{margin:0;overflow-wrap: break-word;font-weight: 500;font-size: 22px;line-height: 140%;color: <?php echo esc_html( $email_title_color ); ?>;}
.tutor-mr-160{margin-right: 150px;}
.tutor-email-buttons{text-align: <?php echo esc_html( $email_button_position ); ?>;}
.tutor-email-buttons a{padding: 10px 20px;min-width: 160px;text-align: center;}
.tutor-email-buttons a.tutor-email-button-bordered{margin-right: 18px;}
.tutor-email-button{background-color: <?php echo esc_html( $primary_button_color ); ?>;border-color: <?php echo esc_html( $primary_button_color ); ?>;color: <?php echo esc_html( $primary_button_text_color ); ?>!important;cursor: pointer;border-radius: 6px;text-decoration: none;font-weight: 500;border: 1px solid transparent;position: relative;box-sizing: border-box;transition: 0.2s;line-height: 26px;font-size: 16px;display: inline-block;}
.tutor-email-button img{width: 24px;vertical-align: middle;margin-top: -3px;}
.tutor-email-button:hover{background-color: <?php echo esc_html( $primary_button_hover_color ); ?>;color: #fff!important;}
.tutor-email-button.preview-only{background-color: var(--primary-button-color);}
.tutor-email-button.preview-only:hover{background-color: var(--primary-button-hover-color);}
.tutor-email-button-bordered{background-color: <?php echo esc_html( $secondary_button_color ); ?>;border-color: <?php echo esc_html( $secondary_button_text_color ); ?>;color: <?php echo esc_html( $secondary_button_text_color ); ?>!important;padding: 10px 34px;cursor: pointer;border-radius: 6px;text-decoration: none;font-weight: 500;border: 1px solid;position: relative;box-sizing: border-box;transition: 0.2s;line-height: 26px;font-size: 16px;display: inline-block;}
.tutor-email-button-bordered:hover{background-color: <?php echo esc_html( $secondary_button_hover_color ); ?>;color: #fff!important;}
.tutor-email-button-bordered.preview-only{background-color: var(--secondary-button-color);}
.tutor-email-button-bordered.preview-only:hover{background-color: var(--secondary-button-hover-color);}
.tutor-email-warning {text-align: right;}
.tutor-email-warning > * {vertical-align: middle;display: inline-block;padding-left: 5px;}
.template-preview a,.template-preview button{pointer-events: none;}
.tutor-email-warning span {padding-top: 2px;}
.tutor-email-datatable{margin-bottom: 20px;width:100%;margin-top: 0;}
.tutor-email-datatable tr td{vertical-align: top;}
.tutor-email-datatable tr td.label{min-width: 150px;width: 150px;}
.tutor-email-from{margin-top: 20px;}
.tutor-panel-block{background: #E9EDFB;color: #212327;font-weight: 400;font-size: 16px;margin-bottom: 30px;padding:25px;border: 1px solid #95AAED;border-radius: 6px}
.tutor-panel-block [data-source="email-block-heading"]{margin-top: 0;font-weight: 500;margin-bottom: 10px;}
.tutor-cardblock-heading{margin-bottom: 15px;}
.tutor-cardblock-wrapper{display: block;border: 1px solid #CDCFD5;padding: 10px;border-radius: 4px;}
.tutor-cardblock-wrapper > * {vertical-align: middle;display: inline-block;}
.tutor-cardblock-content p {font-size: 16px;font-style: normal;font-weight: 400;line-height: 28px;letter-spacing: 0px;text-align: left;margin:0;}
.tutor-email-footer{background-color: <?php echo esc_html( $body_background_color ); ?>; position: relative;}
.tutor-email-footer-text{color: <?php echo esc_html( $footnote_color ); ?>;font-weight: 400;font-size: 16px;line-height: 26px;text-align: left;padding: 20px 50px 20px;}
.email-hr-separator{border-top: none;margin-top: 30px;margin-bottom: 30px;border-bottom: 1px solid #e0e2ea;}
.tutor-user-info{margin-bottom: 20px;}
.tutor-user-info-wrap{display: block;border-top: 1px solid #e0e2ea;border-bottom: 1px solid #e0e2ea;padding-top: 30px;padding-bottom: 30px;}
.tutor-user-info-wrap > * {display: inline-block;}
.tutor-user-info-wrap .answer-block{width: 80%;}
.tutor-user-info-wrap .answer-block{width: 80%;}
.tutor-user-panel {margin-bottom: 20px;}
.tutor-user-panel > div {display: inline-block;vertical-align: top;}
.tutor-user-panel .user-panel-label{margin-right: 20px;}
.tutor-user-panel .tutor-user-panel-wrap {border:1px solid #e0e2ea;padding: 10px;border-radius: 4px;}
.tutor-user-panel-wrap .tutor-email-avatar, .tutor-user-panel-wrap .info-block{display: inline-block;}
.tutor-user-panel-wrap .info-block p{margin: 0;}
.answer-heading{margin-bottom: 20px;clear: both;}
.answer-heading span{color: <?php echo esc_html( $email_short_code_color ); ?>;}
.answer-heading span:first-child{font-weight: 500;}
.answer-heading span:last-child{float: right;}
.tutor-email-avatar{margin-right: 15px;border-radius: 50%;}
.email-mb-30{margin-bottom: 30px;}
.tutor-inline-block{display: inline-block;}

.tutor-email-announcement{border: 1px solid #CDCFD5;border-radius: 6px;margin-top: 30px;}
.tutor-email-announcement .announcement-heading {background: #E9EDFB;padding:20px 30px;border-top-left-radius: 6px;border-top-right-radius: 6px;}
.tutor-email-announcement .announcement-heading .announcement-title{margin-bottom: 10px;}
.tutor-email-announcement .announcement-heading .announcement-meta > *{display: inline-block;vertical-align: middle; margin-right: 10px;font-size: 15px;}
.tutor-email-announcement .announcement-content{padding:20px 30px;}
.tutor-email-announcement .announcement-content p{margin-bottom: 30px;}
.tutor-email-footer-content{background-color: #eff1f6;padding: 0 50px 50px;}
.tutor-email-footer-content * {margin-top: 0;}
.tutor-badge-label.label-success {
	background: #e5f5eb;
	color: #075a2a;
	border-color: #cbe9d5;
}
.tutor-badge-label.label-warning {
	background: #ed970026;
	color: #ed9700;
	border-color: #ed97004d;
}
.tutor-badge-label.label-danger {
	background: #feeceb;
	color: #c62828;
	border-color: #fdd9d7;
}
.tutor-badge-label {
	border-radius: 42px;
	padding: 0 0.5em;
	background: #f8f8f9;
	color: #565b69;
	border: 1px solid #c0c3cb;
	line-height: 1.2;
	font-size: 13px;
}

@media only screen and (max-width: 576px) {
	.tutor-email-body{padding: 15px}
	.tutor-email-wrapper{margin:0;max-width: 100%;}
	.tutor-email-header{padding: 20px 24px;}
	.tutor-email-datatable table tr{display: block;margin-bottom: 25px;}
	.tutor-email-datatable table td{display: block;}
	.tutor-email-content{padding: 30px 20px; margin-bottom: -30px;}
	.tutor-email-buttons{margin-bottom: 24px;}
	.tutor-email-buttons a{width: 100%;text-align:center;padding: 8px 18px;font-size: 14px;}
	.tutor-email-buttons a.tutor-email-button-bordered{margin-right: 0px;margin-bottom: 16px;}
	.tutor-email-heading{font-size: 18px;}
	.tutor-email-warning span {text-transform: capitalize;}
	.tutor-email-warning span.no-res {display: none;}
	.tutor-email-footer-text{padding: 20px 15px;}
	.tutor-email-footer-content{padding: 15px;}
}
</style>
