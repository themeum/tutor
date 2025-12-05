<?php
/**
 * Pagination Component Class.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

/**
 * Class Pagination
 *
 * Responsible for rendering pagination component.
 *
 * // Example Usage :
 *
 * ```
 *  Pagination::make()
 *   ->current( 2 )
 *   ->total( 200 )
 *   ->limit( tutor_utils()->get_option( 'pagination_per_page' ) )
 *   ->prev( tutor_utils()->get_svg_icon( Icon::CHEVRON_LEFT_2 ) )
 *   ->next( tutor_utils()->get_svg_icon( Icon::CHEVRON_RIGHT_2 ) )
 *   ->render();
 * ```
 *
 * @since 4.0.0
 */
class Pagination extends BaseComponent {

	/**
	 * Current paginated value.
	 *
	 * @var int
	 */
	protected $pagination_current = 0;

	/**
	 * Total paginated value.
	 *
	 * @var int
	 */
	protected $pagination_total = 0;

	/**
	 * Pagination based url.
	 *
	 * @var string
	 */
	protected $base_url;

	/**
	 * Pagination previous text.
	 *
	 * @var  string
	 */
	protected $prev;

	/**
	 * Pagination next text.
	 *
	 * @var  string
	 */
	protected $next;

	/**
	 * Pagination limit.
	 *
	 * @var  int
	 */
	protected $pagination_limit = 1;

	/**
	 * Whether to show all links or not.
	 *
	 * @var bool
	 */
	protected $show_all = false;

	/**
	 * Pagination url format.
	 *
	 * @var string
	 */
	protected $format;

	/**
	 * Set the current number of pagination's.
	 *
	 * @since 4.0.0
	 *
	 * @var int $current the current number of pagination's.
	 *
	 * @return self
	 */
	public function current( int $current = 1 ): self {
		$this->pagination_current = $current;
		return $this;
	}


	/**
	 * Set the total number of pagination's.
	 *
	 * @since 4.0.0
	 *
	 * @var int $total the current number of pagination's.
	 *
	 * @return self
	 */
	public function total( int $total = 1 ): self {
		$this->pagination_total = $total;
		return $this;
	}


	/**
	 * Set the pagination limit for each page.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $limit the pagination limit.
	 *
	 * @return self
	 */
	public function limit( int $limit = 1 ): self {
		$this->pagination_limit = $limit;
		return $this;
	}

	/**
	 * Set pagination url format.
	 *
	 * @since 4.0.0
	 *
	 * @param string $format the url format.
	 *
	 * @return self
	 */
	public function format( string $format ): self {
		$this->format = $format;
		return $this;
	}

	/**
	 * Whether to show all pagination links.
	 *
	 * @since 4.0.0
	 *
	 * @param boolean $show_all Show all pagination links or not.
	 *
	 * @return self
	 */
	public function show_all( bool $show_all = false ): self {
		$this->show_all = $show_all;
		return $this;
	}


	/**
	 * Set the base url for pagination.
	 *
	 * @since 4.0.0
	 *
	 * @param string $base the base url.
	 *
	 * @return self
	 */
	public function base( string $base ): self {
		$this->base_url = $base;
		return $this;
	}

	/**
	 * Set previous text for pagination.
	 *
	 * @since 4.0.0
	 *
	 * @param string $prev the previous text.
	 *
	 * @return self
	 */
	public function prev( string $prev ): self {
		$this->prev = $prev;
		return $this;
	}


	/**
	 * Set next text for pagination.
	 *
	 * @since 4.0.0
	 *
	 * @param string $next the next text.
	 *
	 * @return self
	 */
	public function next( string $next ): self {
		$this->next = $next;
		return $this;
	}


	/**
	 * Render pagination info content.
	 *
	 * @since 4.0.0
	 *
	 * @return string the HTML content.
	 */
	protected function render_pagination_info(): string {
		$total   = $this->pagination_limit ? ceil( $this->pagination_total / $this->pagination_limit ) : 1;
		$current = $this->pagination_current ? $this->pagination_current : 1;

		return sprintf(
			'<span class="tutor-pagination-info" aria-live="polite">
				%s
				<span class="tutor-pagination-current">%d</span>
				%s
				<span class="tutor-pagination-total">%d</span>
			</span>',
			__( 'Page', 'tutor' ),
			$current,
			__( 'of', 'tutor' ),
			$total
		);
	}

	/**
	 * Get Paginated links.
	 *
	 * @since 4.0.0
	 *
	 * @return array|null
	 */
	protected function get_paginated_links_list() {

		$total     = $this->pagination_limit ? ceil( $this->pagination_total / $this->pagination_limit ) : 1;
		$current   = $this->pagination_current ? $this->pagination_current : 1;
		$format    = ! empty( $this->format ) ? $this->format : '?paged=%#%';
		$base_url  = str_replace( PHP_INT_MAX, '%#%', esc_url( admin_url( PHP_INT_MAX ) . 'admin.php?paged=%#%' ) );
		$base      = ! empty( $this->base_url ) ? $this->base_url : $base_url;
		$prev_text = ! empty( $this->prev ) ? $this->prev : __( '&laquo; Previous', 'tutor' );
		$next_text = ! empty( $this->next ) ? $this->next : __( 'Next &raquo;', 'tutor' );

		return paginate_links(
			array(
				'base'         => $base,
				'format'       => $format,
				'current'      => $current,
				'total'        => $total,
				'prev_text'    => $prev_text,
				'next_text'    => $next_text,
				'add_fragment' => 'tutor-pagination-item',
				'type'         => 'array',
			)
		);
	}

	/**
	 * Get the final Pagination HTML Component.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function get(): string {
		$pagination_links = $this->get_paginated_links_list() ?? array();
		$pagination_info  = $this->render_pagination_info() ?? '';
		$links            = '';

		if ( count( $pagination_links ) ) {
			foreach ( $pagination_links as $link ) {
				$link = str_replace( 'page-numbers dots', 'tutor-pagination-ellipsis', $link );
				$link = str_replace( 'page-numbers', 'tutor-pagination-item', $link );
				$link = str_replace( 'current', 'tutor-pagination-item-active', $link );
				$link = str_replace( 'prev', 'tutor-pagination-item-prev', $link );
				$link = str_replace( 'next', 'tutor-pagination-item-next', $link );
				$link = str_replace( 'tutor-pagination-item-activeColor', 'currentColor', $link );

				$links .= sprintf( '<li>%s</li>', $link );
			}
		}

		return sprintf(
			'<nav class="tutor-pagination" role="navigation" aria-label="Pagination Navigation">
				%s
				<ul class="tutor-pagination-list">
				%s
				</ul>
			</nav>',
			$pagination_info,
			$links
		);
	}
}
