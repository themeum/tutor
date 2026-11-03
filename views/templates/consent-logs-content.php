<?php
/**
 * Consent logs modal content template (timeline and user card).
 *
 * @package Tutor\Views
 * @since 4.1.3
 *
 * @var \WP_User $user
 * @var array    $consents
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $consents ) ) {
	?>
	<div class="tutor-d-flex tutor-align-center tutor-justify-center tutor-py-48 tutor-color-muted tutor-fs-6">
		<?php esc_html_e( 'No consent logs found.', 'tutor' ); ?>
	</div>
	<?php
	return;
}

$user_name   = $user->display_name ?? '';
$user_joined = $user->user_registered ?? '';
$avatar_url  = tutor_utils()->get_user_avatar_url( $user->ID );
?>
<div class="tutor-consent-timeline">
	<?php
	foreach ( $consents as $log ) :
		if ( ! empty( $log->consent_title ) ) {
			$consent_title = sprintf(
				/* translators: %s: consent title */
				__( 'Accepted %s', 'tutor' ),
				$log->consent_title
			);
		} else {
			$consent_title = __( 'Accepted Consent', 'tutor' );
		}

		$date = ! empty( $log->created_at_gmt ) ? tutor_i18n_get_formated_date( $log->created_at_gmt ) : '';
		$ago  = ! empty( $log->time_ago ) ? $log->time_ago : '';

		$meta_lines = array();
		if ( ! empty( $log->ip_address ) ) {
			$meta_lines[] = esc_html__( 'IP: ', 'tutor' ) . esc_html( $log->ip_address );
		}
		if ( ! empty( $log->source ) ) {
			$meta_lines[] = esc_html__( 'Source: ', 'tutor' ) . esc_html( $log->source );
		}
		if ( ! empty( $log->user_agent ) ) {
			$meta_lines[] = esc_html__( 'Agent: ', 'tutor' ) . esc_html( $log->user_agent );
		}
		?>
		<div class="tutor-consent-timeline-item">
			<div class="tutor-consent-timeline-track">
				<span class="tutor-consent-timeline-dot"></span>
			</div>
			<div class="tutor-consent-timeline-content">
				<p class="tutor-consent-timeline-title"><?php echo esc_html( $consent_title ); ?></p>
				<p class="tutor-consent-timeline-date"><?php echo esc_html( $date ); ?></p>
				<?php if ( ! empty( $meta_lines ) ) : ?>
					<div class="tutor-consent-timeline-meta">
						<?php echo implode( '<br>', $meta_lines ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="tutor-consent-timeline-ago"><?php echo esc_html( $ago ); ?></div>
		</div>
	<?php endforeach; ?>
</div>

<div class="tutor-consent-user-card">
	<?php if ( ! empty( $avatar_url ) ) : ?>
		<img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $user_name ); ?>" />
	<?php endif; ?>
	<div class="tutor-consent-user-card-info">
		<span class="tutor-consent-user-card-name"><?php echo esc_html( $user_name ); ?></span>
		<?php if ( ! empty( $user_joined ) ) : ?>
			<span class="tutor-consent-user-card-joined">
				<?php
				printf(
					/* translators: %s: registration date */
					esc_html__( 'Joined %s', 'tutor' ),
					esc_html( tutor_i18n_get_formated_date( $user_joined, get_option( 'date_format' ) ) )
				);
				?>
			</span>
		<?php endif; ?>
	</div>
</div>
