<?php
/**
 * Skeleton Component Class.
 *
 * Responsible for rendering loading skeleton wireframes
 * matching design tokens and components across Tutor LMS.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

/**
 * Class Skeleton
 *
 * Example Usage:
 * ```php
 * // Basic single line skeleton
 * Skeleton::make()->render();
 *
 * // Custom width & height
 * Skeleton::make()->width( '60%' )->height( 20 )->render();
 *
 * // Stat cards skeleton (4 cards)
 * Skeleton::make()->type( 'stat-card' )->count( 4 )->render();
 *
 * // Overview chart skeleton
 * Skeleton::make()->type( 'chart' )->height( 240 )->render();
 *
 * // Table rows skeleton
 * Skeleton::make()->type( 'table' )->count( 5 )->render();
 *
 * // Top courses skeleton list
 * Skeleton::make()->type( 'top-courses' )->count( 3 )->render();
 *
 * // Recent reviews skeleton list
 * Skeleton::make()->type( 'reviews' )->count( 3 )->render();
 * ```
 *
 * @since 4.0.0
 */
class Skeleton extends BaseComponent {

	/**
	 * Skeleton Type Constants
	 */
	const TYPE_LINE             = 'line';
	const TYPE_AVATAR           = 'avatar';
	const TYPE_STAT_CARD        = 'stat-card';
	const TYPE_CHART            = 'chart';
	const TYPE_COMPLETION_CHART = 'completion-chart';
	const TYPE_TOP_COURSES      = 'top-courses';
	const TYPE_UPCOMING_TASKS   = 'upcoming-tasks';
	const TYPE_REVIEWS          = 'reviews';
	const TYPE_TABLE            = 'table';
	const TYPE_BOX_CARD         = 'box-card';

	/**
	 * Type of skeleton
	 *
	 * @var string
	 */
	protected $type = self::TYPE_LINE;

	/**
	 * Width of skeleton
	 *
	 * @var string|int
	 */
	protected $width = '100%';

	/**
	 * Height of skeleton
	 *
	 * @var string|int
	 */
	protected $height = '';

	/**
	 * Repetition count
	 *
	 * @var int
	 */
	protected $count = 1;

	/**
	 * Number of text lines
	 *
	 * @var int
	 */
	protected $lines = 1;

	/**
	 * Border radius style
	 *
	 * @var string
	 */
	protected $radius = '';

	/**
	 * Set the skeleton type
	 *
	 * @param string $type Type name.
	 * @return self
	 */
	public function type( string $type ): self {
		$this->type = $type;
		return $this;
	}

	/**
	 * Set width
	 *
	 * @param string|int $width Width value.
	 * @return self
	 */
	public function width( $width ): self {
		$this->width = is_numeric( $width ) ? "{$width}px" : $width;
		return $this;
	}

	/**
	 * Set height
	 *
	 * @param string|int $height Height value.
	 * @return self
	 */
	public function height( $height ): self {
		$this->height = is_numeric( $height ) ? "{$height}px" : $height;
		return $this;
	}

	/**
	 * Set repetition count
	 *
	 * @param int $count Number of items.
	 * @return self
	 */
	public function count( int $count ): self {
		$this->count = max( 1, $count );
		return $this;
	}

	/**
	 * Set number of lines
	 *
	 * @param int $lines Number of lines.
	 * @return self
	 */
	public function lines( int $lines ): self {
		$this->lines = max( 1, $lines );
		return $this;
	}

	/**
	 * Set rounded radius
	 *
	 * @param string $radius (circle|full|md|sm).
	 * @return self
	 */
	public function rounded( string $radius ): self {
		$this->radius = $radius;
		return $this;
	}

	/**
	 * Get the component output as an HTML string.
	 *
	 * @return string
	 */
	public function get(): string {
		ob_start();

		switch ( $this->type ) {
			case self::TYPE_STAT_CARD:
				$this->render_stat_cards();
				break;

			case self::TYPE_BOX_CARD:
				$this->render_box_cards();
				break;

			case self::TYPE_CHART:
				$this->render_chart();
				break;

			case self::TYPE_COMPLETION_CHART:
				$this->render_completion_chart();
				break;

			case self::TYPE_TOP_COURSES:
				$this->render_top_courses();
				break;

			case self::TYPE_UPCOMING_TASKS:
				$this->render_upcoming_tasks();
				break;

			case self::TYPE_REVIEWS:
				$this->render_reviews();
				break;

			case self::TYPE_TABLE:
				$this->render_table();
				break;

			case self::TYPE_AVATAR:
				$this->render_avatar();
				break;

			case self::TYPE_LINE:
			default:
				$this->render_lines();
				break;
		}

		return ob_get_clean();
	}

	/**
	 * Render single or multi lines
	 */
	protected function render_lines(): void {
		$height = $this->height ? $this->height : '16px';
		$class  = 'tutor-skeleton' . ( 'circle' === $this->radius || 'full' === $this->radius ? ' tutor-skeleton-round' : '' );

		for ( $i = 0; $i < $this->count; $i++ ) {
			for ( $l = 0; $l < $this->lines; $l++ ) {
				$width = ( $this->lines > 1 && $l === $this->lines - 1 ) ? '60%' : $this->width;
				?>
				<span class="<?php echo esc_attr( $class ); ?>" style="width: <?php echo esc_attr( $width ); ?>; height: <?php echo esc_attr( $height ); ?>;" <?php $this->render_attributes(); ?>></span>
				<?php
			}
		}
	}

	/**
	 * Render Avatar skeleton
	 */
	protected function render_avatar(): void {
		$size = $this->height ? $this->height : ( $this->width ? $this->width : '40px' );
		for ( $i = 0; $i < $this->count; $i++ ) {
			?>
			<span class="tutor-skeleton tutor-skeleton-round" style="width: <?php echo esc_attr( $size ); ?>; height: <?php echo esc_attr( $size ); ?>;" <?php $this->render_attributes(); ?>></span>
			<?php
		}
	}

	/**
	 * Render single stat card skeleton markup
	 */
	protected function render_single_stat_card(): void {
		?>
		<div class="tutor-stat-card">
			<div class="tutor-stat-card-header">
				<div class="tutor-stat-card-title">
					<span class="tutor-skeleton" style="width: 85px; height: 16px; display: inline-block;"></span>
				</div>
				<div class="tutor-stat-card-icon tutor-flex">
					<span class="tutor-skeleton tutor-skeleton-round" style="width: 24px; height: 24px; display: inline-block;"></span>
				</div>
			</div>
			<div class="tutor-stat-card-content">
				<div class="tutor-stat-card-value">
					<span class="tutor-skeleton" style="width: 60px; height: 28px; display: inline-block;"></span>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Stat Cards skeleton (matching .tutor-stat-card layout)
	 */
	protected function render_stat_cards(): void {
		?>
		<div class="tutor-flex tutor-flex-wrap tutor-gap-5 tutor-z-positive" <?php $this->render_attributes(); ?>>
			<?php for ( $i = 0; $i < $this->count; $i++ ) : ?>
				<div class="tutor-flex-1">
					<?php $this->render_single_stat_card(); ?>
				</div>
			<?php endfor; ?>
		</div>
		<?php
	}

	/**
	 * Render Box Cards skeleton (for Report Overview 3 KPI cards)
	 */
	protected function render_box_cards(): void {
		?>
		<div class="tutor-analytics-info-cards" <?php $this->render_attributes(); ?>>
			<?php for ( $i = 0; $i < $this->count; $i++ ) : ?>
				<?php $this->render_single_stat_card(); ?>
			<?php endfor; ?>
		</div>
		<?php
	}

	/**
	 * Render Chart skeleton (matching .tutor-dashboard-home-chart)
	 */
	protected function render_chart(): void {
		$chart_height = $this->height ? $this->height : '179px';
		?>
		<div class="tutor-dashboard-home-chart" <?php $this->render_attributes(); ?>>
			<div class="tutor-dashboard-home-chart-header">
				<div class="tutor-small">
					<span class="tutor-skeleton" style="width: 140px; height: 16px; display: inline-block;"></span>
				</div>
				<div class="tutor-flex tutor-align-center tutor-gap-6">
					<div class="tutor-dashboard-home-chart-legend" data-color="brand">
						<span class="tutor-skeleton" style="width: 50px; height: 12px; display: inline-block;"></span>
					</div>
					<div class="tutor-dashboard-home-chart-legend" data-color="success">
						<span class="tutor-skeleton" style="width: 50px; height: 12px; display: inline-block;"></span>
					</div>
				</div>
			</div>
			<div class="tutor-px-6">
				<div class="tutor-skeleton tutor-rounded-lg" style="width: 100%; height: <?php echo esc_attr( $chart_height ); ?>;"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Course Completion chart skeleton (horizontal stacked bar matching design)
	 */
	protected function render_completion_chart(): void {
		?>
		<div class="tutor-flex tutor-gap-6" <?php $this->render_attributes(); ?>>
			<div class="tutor-dashboard-home-chart tutor-flex-1" data-stacked="true">
				<div class="tutor-small">
					<span class="tutor-skeleton" style="width: 170px; height: 18px; display: inline-block;"></span>
				</div>
				<div class="tutor-skeleton tutor-rounded-lg tutor-mt-5" style="width: 100%; height: 118px;"></div>
				<div class="tutor-flex tutor-flex-wrap tutor-gap-5 tutor-mt-6">
					<?php for ( $i = 0; $i < 5; $i++ ) : ?>
						<div class="tutor-dashboard-home-chart-legend" style="min-width: 99px;">
							<div class="tutor-flex tutor-flex-column tutor-gap-1">
								<span class="tutor-skeleton" style="width: 60px; height: 12px; display: inline-block;"></span>
								<span class="tutor-skeleton" style="width: 25px; height: 16px; display: inline-block;"></span>
							</div>
						</div>
					<?php endfor; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Top Performing Courses skeleton
	 */
	protected function render_top_courses(): void {
		$course_widths = array( '85%', '70%', '92%', '60%' );
		?>
		<div class="tutor-dashboard-home-card" <?php $this->render_attributes(); ?>>
			<div class="tutor-flex tutor-row tutor-justify-between tutor-items-center tutor-gap-9">
				<div class="tutor-small">
					<span class="tutor-skeleton" style="width: 170px; height: 18px; display: inline-block;"></span>
				</div>
				<span class="tutor-skeleton tutor-rounded-md" style="width: 95px; height: 28px; display: inline-block;"></span>
			</div>
			<div class="tutor-dashboard-home-card-body tutor-gap-4">
				<?php for ( $i = 0; $i < $this->count; $i++ ) : ?>
					<div class="tutor-dashboard-home-course">
						<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-flex-1 tutor-overflow-hidden">
							<div class="tutor-dashboard-home-course-index tutor-flex-shrink-0">
								<span class="tutor-skeleton" style="width: 14px; height: 14px; display: inline-block;"></span>
							</div>
							<div class="tutor-p3 tutor-flex-1">
								<span class="tutor-skeleton" style="width: <?php echo esc_attr( $course_widths[ $i % 4 ] ); ?>; max-width: 240px; height: 16px; display: inline-block;"></span>
							</div>
						</div>
						<div class="tutor-flex tutor-items-center tutor-gap-7 tutor-flex-shrink-0 tutor-ml-4">
							<div class="tutor-flex tutor-flex-column tutor-items-center tutor-gap-1">
								<span class="tutor-skeleton" style="width: 55px; height: 12px; display: inline-block;"></span>
								<span class="tutor-skeleton" style="width: 52px; height: 14px; display: inline-block;"></span>
							</div>
							<div class="tutor-flex tutor-flex-column tutor-items-center tutor-gap-1">
								<span class="tutor-skeleton" style="width: 55px; height: 12px; display: inline-block;"></span>
								<span class="tutor-skeleton" style="width: 18px; height: 14px; display: inline-block;"></span>
							</div>
						</div>
					</div>
				<?php endfor; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Upcoming Tasks skeleton
	 */
	protected function render_upcoming_tasks(): void {
		?>
		<div class="tutor-dashboard-home-card tutor-flex-1" <?php $this->render_attributes(); ?>>
			<div class="tutor-small">
				<span class="tutor-skeleton" style="width: 130px; height: 16px; display: inline-block;"></span>
			</div>
			<div class="tutor-dashboard-home-card-body">
				<?php for ( $i = 0; $i < $this->count; $i++ ) : ?>
					<div class="tutor-dashboard-home-task">
						<div class="tutor-dashboard-home-task-icon">
							<span class="tutor-skeleton tutor-skeleton-round" style="width: 18px; height: 18px; display: inline-block;"></span>
						</div>
						<div class="tutor-flex tutor-flex-column tutor-mt-1 tutor-gap-2">
							<div class="tutor-flex tutor-items-center tutor-gap-2">
								<span class="tutor-skeleton" style="width: 100px; height: 12px; display: inline-block;"></span>
							</div>
							<div class="tutor-small">
								<span class="tutor-skeleton" style="width: 180px; height: 14px; display: inline-block;"></span>
							</div>
							<div class="tutor-dashboard-home-task-live-tag">
								<span class="tutor-skeleton tutor-rounded-full" style="width: 90px; height: 22px; display: inline-block;"></span>
							</div>
						</div>
					</div>
				<?php endfor; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Recent Reviews skeleton
	 */
	protected function render_reviews(): void {
		?>
		<div class="tutor-dashboard-home-card" <?php $this->render_attributes(); ?>>
			<div class="tutor-small">
				<span class="tutor-skeleton" style="width: 180px; height: 18px; display: inline-block;"></span>
			</div>
			<div class="tutor-dashboard-home-card-body tutor-gap-6">
				<?php for ( $i = 0; $i < $this->count; $i++ ) : ?>
					<div class="tutor-dashboard-home-review tutor-flex tutor-flex-column tutor-gap-2">
						<div class="tutor-flex tutor-items-center tutor-gap-3">
							<span class="tutor-skeleton tutor-skeleton-round" style="width: 32px; height: 32px; display: inline-block;"></span>
							<div class="tutor-flex tutor-flex-column tutor-gap-1">
								<span class="tutor-skeleton" style="width: 120px; height: 14px; display: inline-block;"></span>
								<span class="tutor-skeleton" style="width: 80px; height: 12px; display: inline-block;"></span>
							</div>
						</div>
						<span class="tutor-skeleton tutor-mt-2" style="width: 90%; height: 14px; display: inline-block;"></span>
					</div>
				<?php endfor; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Table skeleton
	 */
	protected function render_table(): void {
		?>
		<div class="tutor-table-wrapper tutor-rounded-2xl tutor-border tutor-p-6" <?php $this->render_attributes(); ?>>
			<div class="tutor-flex tutor-justify-between tutor-items-center tutor-mb-6 tutor-border-b tutor-pb-4">
				<span class="tutor-skeleton" style="width: 30%; height: 14px;"></span>
				<span class="tutor-skeleton" style="width: 20%; height: 14px;"></span>
				<span class="tutor-skeleton" style="width: 20%; height: 14px;"></span>
			</div>
			<div class="tutor-flex tutor-flex-column tutor-gap-4">
				<?php for ( $i = 0; $i < $this->count; $i++ ) : ?>
					<div class="tutor-flex tutor-items-center tutor-justify-between tutor-py-2">
						<div class="tutor-flex tutor-items-center tutor-gap-3" style="width: 40%;">
							<span class="tutor-skeleton tutor-rounded-md" style="width: 40px; height: 32px;"></span>
							<span class="tutor-skeleton" style="width: 60%; height: 14px;"></span>
						</div>
						<span class="tutor-skeleton" style="width: 15%; height: 14px;"></span>
						<span class="tutor-skeleton" style="width: 15%; height: 14px;"></span>
					</div>
				<?php endfor; ?>
			</div>
		</div>
		<?php
	}
}
