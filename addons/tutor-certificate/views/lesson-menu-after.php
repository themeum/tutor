<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

$cert_download_uril = add_query_arg(array('tutor_action' => 'download_course_certificate', 'course_id' => get_the_ID()));
?>

<a href="<?php echo $cert_download_uril; ?>" class="certificate-download-btn tutor-button"><i class="tutor-icon-mortarboard"></i> <?php _e('Download Certificate', 'tutor'); ?></a>