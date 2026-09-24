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

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

/**
 * Class Pagination
 *
 * Responsible for rendering pagination component.
 *
 * Example Usage:
 *
 * ```php
 * // Minimal — current page from URL, default prev/next icons
 * Pagination::make()
 *     ->current( (int) Input::get( 'current_page', 1 ) )
 *     ->total( $total_items )
 *     ->limit( 10 )
 *     ->render();
 *
 * // With custom prev/next icon markup
 * Pagination::make()
 *     ->current( 2 )
 *     ->total( 200 )
 *     ->limit( tutor_utils()->get_option( 'pagination_per_page' ) )
 *     ->prev( SvgIcon::make()->name( Icon::CHEVRON_LEFT_2 )->flip_rtl()->get() )
 *     ->next( SvgIcon::make()->name( Icon::CHEVRON_RIGHT_2 )->flip_rtl()->get() )
 *     ->render();
 *
 * // Custom URL format (e.g., pretty-permalink pagination)
 * Pagination::make()
 *     ->current( get_query_var( 'paged', 1 ) )
 *     ->total( $total_items )
 *     ->limit( 20 )
 *     ->format( '/page/%#%/' )
 *     ->render();
 *
 * // Show all page links (no ellipsis collapsing)
 * Pagination::make()
 *     ->current( 1 )
 *     ->total( 50 )
 *     ->limit( 5 )
 *     ->show_all( true )
 *     ->render();
 *
 * // Extra CSS class on the nav wrapper
 * Pagination::make()
 *     ->current( 1 )
 *     ->total( 100 )
 *     ->limit( 10 )
 *     ->attr( 'class', 'tutor-mt-6' )
 *     ->render();
 *
 * // Retrieve HTML without echoing
 * $html = Pagination::make()->current( 1 )->total( 50 )->limit( 10 )->get();
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
	protected $pagination_current = 1;

	/**
	 * Total paginated value.
	 *
	 * @var int
	 */
	protected $pagination_total = 1;

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
	protected $pagination_limit = 10;

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
	 * @param int $current the current number of pagination's.
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
	 * @param int $total the current number of pagination's.
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
	public function limit( int $limit = 10 ): self {
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
		$per_page = max( ceil( $this->pagination_total / $this->pagination_limit ), 1 );
		$current  = max( intval( $this->pagination_current ), 1 );

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
			$per_page
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
		$per_page  = max( ceil( $this->pagination_total / $this->pagination_limit ), 1 );
		$current   = max( intval( $this->pagination_current ), 1 );
		$format    = ! empty( $this->format ) ? $this->format : '?current_page=%#%';
		$prev_text = ! empty( $this->prev ) ? $this->prev : SvgIcon::make()->name( Icon::CHEVRON_LEFT_2 )->flip_rtl()->get();
		$next_text = ! empty( $this->next ) ? $this->next : SvgIcon::make()->name( Icon::CHEVRON_RIGHT_2 )->flip_rtl()->get();
		return paginate_links(
			array(
				'format'    => $format,
				'current'   => $current,
				'total'     => $per_page,
				'prev_text' => $prev_text,
				'next_text' => $next_text,
				'type'      => 'array',
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
		if ( $this->pagination_total <= $this->pagination_limit ) {
			return '';
		}

		$pagination_links = $this->get_paginated_links_list() ?? array();
		$pagination_info  = $this->render_pagination_info() ?? '';
		$links            = '';

		$classes = array( 'tutor-pagination' );
		if ( isset( $this->attributes['class'] ) ) {
			$classes[] = $this->attributes['class'];
		}

		if ( count( $pagination_links ) ) {
			foreach ( $pagination_links as $link ) {
				$link = str_replace( 'page-numbers dots', 'tutor-pagination-ellipsis', $link );
				$link = str_replace( 'page-numbers', 'tutor-pagination-item', $link );
				$link = preg_replace( '/\bcurrent\b(?=[^"]*"[^"]*$)/', 'tutor-pagination-item-active', $link );
				$link = str_replace( 'prev', 'tutor-pagination-item-prev', $link );
				$link = str_replace( 'next', 'tutor-pagination-item-next', $link );
				$link = str_replace( 'tutor-pagination-item-activeColor', 'currentColor', $link );

				if ( str_contains( $link, 'tutor-pagination-item-prev' ) ) {
					$link = preg_replace(
						'/<a\b/',
						'<a aria-label="' . esc_attr__( 'Previous page', 'tutor' ) . '"',
						$link,
						1
					);
				}

				if ( str_contains( $link, 'tutor-pagination-item-next' ) ) {
					$link = preg_replace(
						'/<a\b/',
						'<a aria-label="' . esc_attr__( 'Next page', 'tutor' ) . '"',
						$link,
						1
					);
				}

				$links .= sprintf( '<li>%s</li>', $link );
			}
		}

		return sprintf(
			'<nav class="%s" role="navigation" aria-label="%s">
				%s
				<ul class="tutor-pagination-list">
				%s
				</ul>
			</nav>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr__( 'Pagination', 'tutor' ),
			$pagination_info,
			$links
		);
	}
}
