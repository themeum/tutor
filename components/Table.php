<?php
/**
 * Table Component Class.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

/**
 * Class Table
 *
 * Responsible for rendering table component with variable number
 * of rows and columns.
 *
 * ```
 * // Component Data Structure
 * Table::make()
 *   ->headings(
 *      [
 *          [
 *              'content' => '',
 *          ]
 *      ]
 *   )
 *  ->contents(
 *      [
 *        [
 *          'columns' => [
 *              [
 *                  'content' => '',
 *              ]
 *           ],
 *        ]
 *      ]
 *   )
 * ->attributes('')
 * ->render();
 * ```
 *
 *
 * ```
 * // Example Usage:
 * $heading = array(
 *       array(
 *           'content' => __( 'Quiz Info', 'tutor' ),
 *       ),
 *       array(
 *           'content' => __( 'Marks', 'tutor' ),
 *       ),
 *   );
 *
 * $content = array(
 *       array(
 *           'columns' => array(
 *               array(
 *                   'content' => '<div class="tutor-flex tutor-gap-3 tutor-items-center">' . tutor_utils()->get_svg_icon( Icon::QUESTION_CIRCLE ) . __( 'Questions', 'tutor' ) . '</div>',
 *               ),
 *               array( 'content' => 20 ),
 *           ),
 *       ),
 *  );
 *
 *   echo Table::make()
 *       ->headings( $heading )
 *       ->contents( $content )
 *       ->attributes( 'tutor-table-wrapper tutor-table-column-borders tutor-mb-6' )
 *       ->render();
 * ```
 *
 * @since 4.0.0
 */
class Table extends BaseComponent {

	/**
	 * Table header content array.
	 *
	 * @var array
	 */
	protected $cell_headers;

	/**
	 * A 2D array of cell content.
	 *
	 * @var array
	 */
	protected $cell_content;

	/**
	 * Table class names.
	 *
	 * @var string
	 */
	protected $attribute;

	/**
	 * Set table column headings.
	 *
	 * @since 4.0.0
	 *
	 * Headings must be provided in the following format.
	 *
	 * ```
	 * [
	 *   [
	 *       'content' => '',
	 *   ]
	 * ]
	 * ```
	 *
	 * @param array $headings the table cell headings.
	 *
	 * @return self
	 */
	public function headings( array $headings ): self {
		$this->cell_headers = $headings;
		return $this;
	}

	/**
	 * Sets the custom table class.
	 *
	 * @since 4.0.0
	 *
	 * @param string $attribute the attribute string.
	 *
	 * @return self
	 */
	public function attributes( string $attribute ): self {
		$this->attribute = $attribute;
		return $this;
	}

	/**
	 * Set table cell contents.
	 *
	 * @since 4.0.0
	 *
	 * Content must be given in the following format
	 * ```
	 * [
	 *   [
	 *     'columns' => [
	 *         [
	 *             'content' => '',
	 *         ]
	 *      ],
	 *   ]
	 * ]
	 * ```
	 * @param array $contents the cell contents.
	 *
	 * @return self
	 */
	public function contents( array $contents ): self {
		$this->cell_content = $contents;
		return $this;
	}

	/**
	 * Render table heading.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML
	 */
	protected function render_table_headings(): string {
		$headings = '';
		if ( ! tutor_utils()->count( $this->cell_headers ) ) {
			return $headings;
		}

		foreach ( $this->cell_headers as $heading ) {
			$headings .= sprintf(
				'<th>%1$s</th>',
				apply_filters( 'tutor_table_heading', $heading['content'] )
			);
		}

		return $headings;
	}

	/**
	 * Render table body.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML
	 */
	protected function render_table_body(): string {
		$rows = '';

		if ( ! count( $this->cell_content ) ) {
			return $rows;
		}

		foreach ( $this->cell_content as $row ) {
			$columns = '';
			foreach ( $row['columns'] as $column ) {
				$columns .= sprintf(
					'<td>%s</td>',
					apply_filters( 'tutor_table_content', $column['content'] )
				);
			}

			$rows .= sprintf( '<tr>%s</tr>', $columns );
		}

		return $rows;
	}

	/**
	 * Get the final Table HTML Content.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML
	 */
	public function get(): string {
		return sprintf(
			'<div class="%s">
				<table class="tutor-table">
					<thead>
						%s
					</thead>
					<tbody>
						%s
					</tbody>
				</table>
			</div>',
			esc_attr( $this->attribute ),
			$this->render_table_headings(),
			$this->render_table_body()
		);
	}
}
