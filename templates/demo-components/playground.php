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
		'dashboard'     => 'Dashboard',
		'learning-area' => 'Learning Area',
	);

	$subpage = $_GET['subpage'] ?? '';

	?>
	<h1>
		Tutor LMS 4.wow 🔥
	</h1>
	<nav>
		<?php if ( $subpage ) : ?>
			<a href="<?php echo esc_url( $current_url ); ?>">🛝 Playground</a>
		<?php endif; ?>

		<?php foreach ( $subpages as $slug => $title ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'subpage', $slug, $current_url ) ); ?>"  class="<?php echo esc_attr( $subpage === $slug ? 'active' : '' ); ?>">
				<?php echo esc_html( $title ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
	<?php if ( $subpage ) : ?>
		<?php include $subpage . '.php'; ?>
		<?php else : ?>
		<section>
			<?php require 'avatar.php'; ?>
			<?php require 'pagination.php'; ?>
		</section>
		<section>
			<?php require 'button.php'; ?>
			<?php require 'popover.php'; ?>
			<?php require 'file-uploader.php'; ?>
		</section>
		<section>
			<?php require 'tabs.php'; ?>
		</section>
		<section>
			<?php require 'skeleton.php'; ?>
		</section>
		<section>
			<?php require 'progress.php'; ?>
		</section>
		<section>
			<?php require 'statics.php'; ?>
		</section>
		<section>
			<?php require 'badge.php'; ?>
			<?php require 'card.php'; ?>
		</section>
		<section>
			<?php require 'section-separator.php'; ?>
		</section>
		<section>
			<?php require 'accordion.php'; ?>
		</section>
		<section>
			<?php require 'select-dropdown.php'; ?>
		</section>
		<section>
			<?php require 'stepper-dropdown.php'; ?>
		</section>
		<section>
			<?php require 'modal.php'; ?>
		</section>
		<section>
			<?php require 'input.php'; ?>
		</section>
		<section>
			<?php require 'form.php'; ?>
		</section>
	<?php endif ?>

</body>
</html>
