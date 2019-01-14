<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>PDF Certificate Title</title>
    <style type="text/css"><?php $this->pdf_style(); ?></style>
</head>
<body>

<div class="certificate-wrap">
    <div class="certificate-topheading">
        <h1>VERIFIED</h1>
        <h4>Certificate of Achievement</h4>
    </div>

    <div class="certificate-content">
		<?php
		$user = wp_get_current_user();

		$hour_text = '';
		$min_text = '';
		if ($durationHours){
			if ($durationHours > 1){
				$hour_text = $durationHours.' hours';
			}else{
				$hour_text = $durationHours.' hour';
			}
		}
		if ($durationMinutes){
			if ($durationMinutes > 1){
				$min_text = $durationMinutes.' minutes';
			}else{
				$min_text = $durationMinutes.' minute';
			}
		}
		$duration_text= $hour_text.' '.$min_text;
		?>
        <p>  This is to certify that</p>
        <h1> <?php echo $user->display_name; ?></h1>
        <p>  has successfully completed <?php echo $duration_text; ?>  online course of </p>
        <h2> <?php echo $course->post_title; ?> </h2>
        <p>on <?php echo date('F d, Y', strtotime( $completed->completion_date) ); ?></p>
    </div>

    <div class="certificate-footer">

        <table>
            <tr>
                <td class="first-col"> </td>
                <td>
                    <div class="signature-wrap">
						<?php
						$signature_id = tutor_utils()->get_option('tutor_cert_signature_image_id');
						$certURL = TUTOR_CERT()->url.'/assets/images/signature.png';
						if ($signature_id){
							$certURL = wp_get_attachment_url($signature_id);
						}
						?>
                        <img src="<?php echo $certURL; ?>" />
                    </div>
                </td>
            </tr>

            <tr>
                <td class="first-col">
                    <p> <strong>Valid Certificate ID</strong> </p>
                </td>
                <td>
                    <p class="certificate-author-name"> <strong><?php echo tutor_utils()->get_option('tutor_cert_authorised_name'); ?></strong> </p>
                </td>
            </tr>
            <tr>
                <td class="first-col"> <p><?php echo $completed->completed_hash; ?></p> </td>
                <td><?php echo tutor_utils()->get_option('tutor_cert_authorised_company_name'); ?> </td>
            </tr>
        </table>
    </div>
</div>

<div id="watermark">
    <img src="<?php echo TUTOR_CERT()->url.'assets/images/blank-certificate.png'; ?>" height="100%" width="100%" />
</div>


</body>
</html>