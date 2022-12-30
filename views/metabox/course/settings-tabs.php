<?php
/**
 * Settings tabs
 *
 * TODO: file maybe unused will be removed later on
 *
 * @package Tutor\Views
 * @subpackage Tutor\MetaBox
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

$args        = $this->args;
$current_tab = tutils()->array_get( 'settings_tab', tutor_sanitize_data( $_GET ) );

?>

<div id="tutor-metabox-course-settings-tabs" class="tutor-course-settings-tabs">

	<?php
	if ( ! $this->is_gutenberg_enable ) {
		?>
		<div class="settings-tabs-heading">
			<h3><?php esc_html_e( 'Course Settings', 'tutor' ); ?></h3>
		</div>
		<?php
	}
	?>

	<div class="course-settings-tabs-container">
		<div class="settings-tabs-navs-wrap">
			<ul class="settings-tabs-navs">
				<?php
				$i = 0;
				foreach ( $args as $key => $arg ) {
					$i++;

					if ( $current_tab ) {
						$active = $current_tab === $key ? 'active' : '';
					} else {
						$active = 1 === $i ? 'active' : '';
					}

					$label      = tutils()->array_get( 'label', $arg );
					$icon_class = tutils()->array_get( 'icon_class', $arg );
					$url        = add_query_arg( array( 'settings_tab' => $key ) );

					$icon = '';
					if ( $icon_class ) {
						$icon = '<i class="' . esc_attr( $icon_class ) . '"></i>';
					}

					echo '<li class="' . esc_attr( $active ) . '">
							<a href="' . esc_url( $url ) . '" data-target="#settings-tab-' . esc_attr( $key ) . '">' .
								wp_kses( $icon, tutor_utils()->allowed_icon_tags() ) . ' ' . esc_html( $label ) .
							'</a>
						</li>';
				}
				?>
			</ul>
		</div>

		<div class="settings-tabs-container">
			<?php
			$i = 0;
			foreach ( $args as $key => $tab ) {
				$i++;

				$label    = tutils()->array_get( 'label', $tab );
				$callback = tutils()->array_get( 'callback', $tab );
				$fields   = tutils()->array_get( 'fields', $tab );

				if ( $current_tab ) {
					$active  = $current_tab === $key ? 'active' : '';
					$display = $current_tab === $key ? 'block' : 'none';
				} else {
					$active  = 1 === $i ? 'active' : '';
					$display = 1 === $i ? 'block' : 'none';
				}

				echo '<div id="settings-tab-' . esc_attr( $key ) . '" class="settings-tab-wrap ' . esc_attr( $active ) . '" style="display: ' . esc_attr( $display ) . ';">';

				do_action( 'tutor_course/settings_tab_content/before', $key, $tab );
				do_action( 'tutor_course/settings_tab_content/before/' . esc_attr( $key ) . '', $tab );

				if ( tutils()->count( $fields ) ) {
					$this->generate_field( $fields );
				}

				/**
				 * Handling Callback
				 */
				if ( $callback && is_callable( $callback ) ) {
					call_user_func( $callback, $key, $tab );
				}

				do_action( 'tutor_course/settings_tab_content/after', $key, $tab );
				do_action( 'tutor_course/settings_tab_content/after/' . esc_attr( $key ) . '', $tab );

				echo '</div>';
			}
			?>
		</div>

	</div>


</div>
