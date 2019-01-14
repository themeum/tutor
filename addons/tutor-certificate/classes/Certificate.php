<?php
/**
 * Tutor Multi Instructor
 */

namespace TUTOR_CERT;

use Dompdf\Dompdf;
use Dompdf\Options;

class Certificate{
	public function __construct() {
		if ( ! function_exists('tutor_utils')){
			return;
		}
		$isEnable = tutor_utils()->get_option('enable_course_certificate');
		if ( ! $isEnable){
			return;
		}
		add_action('tutor_enrolled_box_after', array($this, 'lesson_page_action_menu_after'));
		$this->create_certificate();
	}

	public function create_certificate(){
		$download_action = sanitize_text_field(tutor_utils()->avalue_dot('tutor_action', $_GET));
		if ($download_action !== 'download_course_certificate' || ! is_user_logged_in()){
			return;
		}

		$course_id = (int) sanitize_text_field(tutor_utils()->avalue_dot('course_id', $_GET));
		$is_enrolled = tutor_utils()->is_enrolled($course_id);

		if ( ! $is_enrolled){
			return;
		}
		$is_completed = tutor_utils()->is_completed_course($course_id);
		if ( ! $is_completed){
			return;
		}

		$this->generat_certificate($course_id);
	}

	public function generat_certificate($course_id, $debug = false){
		$duration        = get_post_meta( $course_id, '_course_duration', true );
		$durationHours   = (int) tutor_utils()->avalue_dot( 'hours', $duration );
		$durationMinutes = (int) tutor_utils()->avalue_dot( 'minutes', $duration );
		$course = get_post($course_id);
		$completed = tutor_utils()->is_completed_course($course_id);

		ob_start();
		include TUTOR_CERT()->path.'views/certificate.php';
		$content = ob_get_clean();

		if ($debug){
			echo $content;
			die();
		}
		$this->generate_PDF($content);
	}

	public function generate_PDF($certificate_content = null){
		if ( ! $certificate_content){
			return;
		}
		require_once TUTOR_CERT()->path.'lib/vendor/autoload.php';

		$options =  new Options( apply_filters( 'tutor_cert_dompdf_options', array(
			'defaultFont'				=> 'sans',
			'isRemoteEnabled'			=> true,
			'isFontSubsettingEnabled'	=> true,
			// HTML5 parser requires iconv
			'isHtml5ParserEnabled'		=> extension_loaded('iconv') ? true : false,
		) ) );

		$dompdf = new Dompdf($options);
		//Getting Certificate to generate PDF
		$dompdf->loadHtml($certificate_content);

		//Setting Paper
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();
		$dompdf->stream('certificate'.time().'.pdf');
	}

	public function pdf_style() {
		$css = TUTOR_CERT()->path.'assets/css/pdf.css';

		ob_start();
		if (file_exists($css)) {
			include($css);
		}
		$css = ob_get_clean();
		$css = apply_filters( 'tutor_cer_css', $css, $this );

		echo $css;
	}

	public function lesson_page_action_menu_after(){
		ob_start();
		include TUTOR_CERT()->path.'views/lesson-menu-after.php';
		$content = ob_get_clean();

		echo $content;
	}
}