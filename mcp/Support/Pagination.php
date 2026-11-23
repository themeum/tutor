<?php
/**
 * MCP pagination helper.
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Shared pagination bounds for MCP list abilities.
 *
 * @since 4.3.0
 */
class Pagination {

	/**
	 * Default items per page.
	 *
	 * @since 4.3.0
	 *
	 * @var int
	 */
	const DEFAULT_PER_PAGE = 20;

	/**
	 * Maximum items per page.
	 *
	 * @since 4.3.0
	 *
	 * @var int
	 */
	const MAX_PER_PAGE = 50;

	/**
	 * Extract page, per_page, offset from MCP input.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input MCP input array.
	 * @return array{page: int, per_page: int, offset: int}
	 */
	public static function bounds( array $input ): array {
		$page     = max( 1, absint( $input['page'] ?? 1 ) );
		$per_page = absint( $input['per_page'] ?? self::DEFAULT_PER_PAGE );
		$per_page = min( max( 1, $per_page ), self::MAX_PER_PAGE );
		$offset   = ( $page - 1 ) * $per_page;

		return array(
			'page'     => $page,
			'per_page' => $per_page,
			'offset'   => $offset,
		);
	}
}
