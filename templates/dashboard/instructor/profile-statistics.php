<?php
/**
 * Tutor dashboard profile statistics.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\SvgIcon;
?>	
		
<h5 class="tutor-statistic-title">
	<?php esc_html_e( 'Statistics', 'tutor' ); ?>
</h5>


<div class="tutor-statistic-cards">
	<?php foreach ( $statistics as $stat ) : ?>
	<div class="tutor-statistic-card">
		<div class="tutor-statistic-card-icon">
		<?php
		SvgIcon::make()
			->name( $stat['icon'] )
			->size( 24 )
			->attr( 'class', $stat['icon_class'] )
			->render();
		?>
		</div>
		<div class="tutor-statistic-card-content">
			<h3 class="tutor-statistic-card-value tutor-my-none"><?php echo esc_html( $stat['value'] ); ?></h3>
			<div class="tutor-statistic-card-label"><?php echo esc_html( $stat['label'] ); ?></div>
		</div>
	</div>
	<?php endforeach; ?>
</div>
