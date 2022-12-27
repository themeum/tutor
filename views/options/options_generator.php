<?php
/**
 * Options generator
 *
 * @package Tutor\Views
 * @subpackage Tutor\Options
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<div class="wrap">
	<h1><?php esc_html_e( 'Tutor Settings', 'tutor' ); ?></h1>

	<form id="tutor-option-form" class="tutor-option-form" method="post" data-toast_success_message="<?php esc_html_e( 'Settings Saved', 'tutor' ); ?>">
		<input type="hidden" name="action" value="tutor_option_save" >

		<?php
		$options_attr = $this->options_attr();

		if ( is_array( $options_attr ) && count( $options_attr ) ) {
			$first_item = null;
			?>
			<ul class="tutor-option-nav-tabs">
			<?php
				$tab_page = sanitize_text_field( tutils()->array_get( 'tab_page', $_GET ) );
			foreach ( $options_attr as $key => $option_group ) {
				if ( empty( $option_group ) ) {
					continue;
				}
				if ( ! $first_item ) {
					$first_item = $key;
				}
				$current_page  = ( $first_item === $key );
				$current_class = $current_page ? 'current' : '';
				if ( $tab_page ) {
					$current_class = $tab_page === $key ? 'current' : '';
				}

				$nav_url = add_query_arg( array( 'tab_page' => $key ) );
				echo '<li class="option-nav-item ' . esc_attr( $current_class ) . '">
						<a href="' . esc_url( $nav_url ) . '" data-tab="#' . esc_attr( $key ) . '" class="tutor-option-nav-item">' .
							esc_attr( $option_group['label'] ) .
						'</a> 
					</li>';
			}
			?>
			</ul>

			<?php
			foreach ( $options_attr as $key => $option_group ) {
				if ( empty( $option_group ) ) {
					continue;
				}
				$current_page = ( $first_item === $key );
				if ( $tab_page ) {
					$current_page = $tab_page === $key ? 'current' : '';
				}

				?>

				<div id="<?php echo esc_attr( $key ); ?>" class="tutor-option-nav-page <?php echo esc_attr( $current_page ) ? 'current-page' : ''; ?> " style="display: <?php echo esc_attr( $current_page ? 'block' : 'none' ); ?>;" >

				<?php
				do_action( 'tutor_options_before_' . $key );

				if ( ! empty( $option_group['sections'] ) ) {
					foreach ( $option_group['sections'] as $fgKey => $field_group ) {
						?>

							<div class="tutor-option-field-row">
								<h2><?php echo esc_attr( $field_group['label'] ); ?></h2>
							</div>

						<?php
							do_action( 'tutor_options_' . esc_attr( $key ) . '_' . esc_attr( $fgKey ) . '_before' );
						if ( ! empty( $field_group['fields'] ) && tutor_utils()->count( $field_group['fields'] ) ) {
							foreach ( $field_group['fields'] as $field_key => $field ) {
									$field['field_key'] = $field_key;
									$this->generate_field( $field );
							}
						}
						do_action( 'tutor_options_' . esc_attr( $key ) . '_' . esc_attr( $fgKey ) . '_after' );
					}
				}

				do_action( 'tutor_options_after_' . esc_attr( $key ) );

				?>
				</div>
				<?php
			}
		}
		?>

		<p class="submit">
			<button type="button" id="save_tutor_option" class="button button-primary"><?php esc_html_e( 'Save Settings', 'tutor' ); ?></button>
		</p>
	</form>
</div>
