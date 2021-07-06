<?php

/**
 * Options generator
 */
$url_page = isset( $_GET['tab_page'] ) ? $_GET['tab_page'] : null;
?>

<!-- .tutor-backend-wrap -->
<section class="tutor-backend-wrap">
	<header class="tutor-option-header px-3 py-2">
		<div class="title"><?php _e( 'Settings', 'tutor' ); ?></div>
		<div class="search-field">
			<div class="tutor-input-group tutor-form-control-has-icon">
				<span class="las la-search tutor-input-group-icon"></span>
				<input type="search" class="tutor-form-control" placeholder="<?php _e( 'Search', 'tutor' ); ?>" />
			</div>
		</div>
		<div class="save-button">
			<button class="tutor-btn"><?php _e( 'Save Changes', 'tutor' ); ?></button>
		</div>
	</header>
	<!-- end /.tutor-option-header -->

	<!-- .tutor-option-body -->
	<div class="tutor-option-body">
		<form class="tutor-option-form py-4 px-3">
			<div class="tutor-option-tabs">
			<?php $i = 0;
					foreach ( $this->options_attr as $args ) : ?>
					<!-- .tutor-option-nav -->
					<ul class="tutor-option-nav">

						<li class="tutor-option-nav-item">
							<h4><?php echo $args->label ?></h4>
						</li>
						<?php foreach ( $args->sections as $key => $section ) :
							$i += 1;
							$icon = tutor()->url . 'assets/images/images-v2/icons/' . $section->slug . '.svg';
						?>
							<li class="tutor-option-nav-item">
								<?php echo file_exists( tutor()->url . 'assets/images/images-v2/icons/' . $section->slug . '.svg' ); ?>
								<a data-tab="<?php echo $section->slug ?>" class="<?php echo $this->get_active($i, $url_page, $section->slug ) ?>">
									<img src="<?php echo $icon ?>" alt="general icon" />
									<span><?php echo $section->label ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endforeach; ?>
				<!-- end /.tutor-option-nav -->
			</div>
			<!-- end /.tutor-option-tabs -->

			<div class="tutor-option-tab-pages">
				<!-- #general .tutor-option-nav-page  -->

				<?php
				$i = 0;
				foreach ( $this->options_attr as $args ) : ?>
					<?php foreach ( $args->sections as $key => $section ) :
						$i += 1; ?>

						<div id="<?php echo $section->slug ?>" class="tutor-option-nav-page <?php echo $this->get_active( $i, $url_page, $section->slug ) ?>">
							<!-- .tutor-option-main-title -->
							<div class="tutor-option-main-title">
								<h2><?php echo $section->label ?></h2>
								<a href="#">
									<i class="las la-undo-alt"></i>
									<?php _e( 'Reset to Default', 'tutor' ) ?>
								</a>
							</div>

							<!-- end /.tutor-option-main-title -->
							<?php foreach ( $section->blocks as $blocks ) : ?>
								<?php if ( empty( $blocks->label ) ) : ?>
									<div class="tutor-option-single-item">
										<?php echo $this->blocks( $blocks ) ?>
									</div>
								<?php else : ?>
									<?php echo $this->blocks( $blocks ) ?>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</div>
			<!-- end /.tutor-option-tab-pages -->
		</form>
	</div>
	<!-- end /.tutor-option-body -->
</section>
<!-- end /.tutor-backend-wrap -->


<?php
echo '<pre>';
print_r($this->options_attr);
echo '</pre>';
?>