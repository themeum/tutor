<?php
/**
 * Profile Template for Account
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\User;

?>

<div class="tutor-profile-wrapper">
	<?php require_once tutor_get_template( 'account-header' ); ?>

	<div class="tutor-profile-container">
		<div class="tutor-flex tutor-flex-column tutor-gap-5 tutor-mt-9">
			<?php tutor_load_template( 'user-profile', array( 'show_statistics' => true ) ); ?>
		</div>
	</div>
</div>	
