<?php
/**
 * Tutor playground.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Input;

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Tutor Playground</title>
	<style>
		nav {
			margin-bottom: 30px;
		}
		nav a {
			padding-right: 10px;
		}
		nav a.active {
			color: blue;
		}
		section {
			margin-bottom: 30px;
		}
	</style>
</head>
<body>
<?php
	$current_url = admin_url( 'admin.php?page=playground' );

	$subpages = array(
		'dashboard'        => 'Dashboard',
		'learning-area'    => 'Learning Area',
		'user-profile'     => 'User Profile',
		'certificates'     => 'Certificates',
		'reviews'          => 'Reviews',
		'profile-settings' => 'Account Settings',
		'billing'          => 'Billing',
		'quiz'             => 'Quiz',
		'quiz-summary'     => 'Quiz Summary',
		'assignment'       => 'Assignment',
	);

	$subpage = Input::get( 'subpage', '' );
	?>
	<h1>Tutor LMS 4.wow 🔥</h1>
	<nav>
		<?php if ( $subpage ) : ?>
			<a href="<?php echo esc_url( $current_url ); ?>">🛝 Playground</a>
		<?php endif; ?>

		<?php foreach ( $subpages as $slug => $page_title ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'subpage', $slug, $current_url ) ); ?>"  class="<?php echo esc_attr( $subpage === $slug ? 'active' : '' ); ?>">
				<?php echo esc_html( $page_title ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
	<?php if ( $subpage ) : ?>
		<?php include $subpage . '.php'; ?>
		<?php else : ?>
		<section>
			<?php require 'components/table.php'; ?>
			<?php require 'components/avatar.php'; ?>
			<?php require 'components/pagination.php'; ?>
			<?php require 'components/button.php'; ?>
			<?php require 'components/popover.php'; ?>
			<?php require 'components/file-uploader.php'; ?>
			<?php require 'components/preview-trigger.php'; ?>
			<?php require 'components/tabs.php'; ?>
			<?php require 'components/nav.php'; ?>
			<?php require 'components/skeleton.php'; ?>
			<?php require 'components/progress.php'; ?>
			<?php require 'components/statics.php'; ?>
			<?php require 'components/badge.php'; ?>
			<?php require 'components/card.php'; ?>
			<?php require 'components/section-separator.php'; ?>
			<?php require 'components/accordion.php'; ?>
			<?php require 'components/stepper-dropdown.php'; ?>
			<?php require 'components/modal.php'; ?>
			<?php require 'components/input.php'; ?>
			<?php require 'components/form.php'; ?>
			<?php require 'components/select.php'; ?>
			<?php require 'components/attachment-card.php'; ?>
		</section>
	<?php endif ?>
</body>
</html>
