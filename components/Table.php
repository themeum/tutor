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
 *              'class'   => '', // the <th> tag class
 *          ]
 *      ]
 *   )
 *  ->contents(
 *      [
 *        [
 *          'content' => [
 *              [
 *                  'content' => '',
 *                  'class' => '', // the <td> tag class
 *                  'icon' =>
 *                      [
 *                           'svg' => '', // sanitized svg of icon
 *                           'position' => '' // left or right
 *                       ]
 *              ]
 *           ],
 *          'class' => '' // the <tr> tag class
 *        ]
 *      ]
 *   )
 * ->attributes('')
 * ->render();
 * ```
 *
 *
 * ```
 *
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
 *   $content = array(
 *       array(
 *           'content' => array(
 *               array(
 *                   'content' => 'Questions',
 *                   'class'   => 'tutor-bg-blue',
 *                   'icon'    => array(
 *                       'svg'      => tutor_utils()->get_svg_icon( Icon::QUESTION_CIRCLE ),
 *                       'position' => 'left',
 *                   ),
 *               ),
 *               array( 'content' => 20 ),
 *           ),
 *           'class'   => 'tutor-bg-red',
 *       ),
 *   );
 *
 *   echo Table::make()
 *       ->headings( $heading )
 *       ->contents( $content )
 *       ->attributes( "tutor-table-wrapper tutor-table-column-borders tutor-mb-6" )
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
	private $cell_headers;

	/**
	 * A 2D array of cell content.
	 *
	 * @var array
	 */
	private $cell_content;

	/**
	 * Table class names.
	 *
	 * @var string
	 */
	private $attribute;

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
	 *       'class'   => 'the <th> tag class',
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
	 *     'content' => [
	 *         [
	 *             'content' => '',
	 *             'class' => 'the <td> tag class',
	 *             'icon' =>
	 *                 [
	 *                      'svg' => 'icon svg',
	 *                      'position' => 'left|right'
	 *                  ]
	 *         ]
	 *      ],
	 *     'class' => 'the <tr> tag class'
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
	private function render_table_headings(): string {
		$headings = '';
		if ( ! count( $this->cell_headers ) ) {
			return $headings;
		}

		foreach ( $this->cell_headers as $heading ) {
			if ( isset( $heading['class'] ) ) {
				$headings .= sprintf(
					'<th class="%s">%s</th>',
					esc_attr( $heading['class'] ),
					esc_attr( $heading['content'] )
				);
			} else {
				$headings .= sprintf( '<th>%1$s</th>', esc_attr( $heading['content'] ) );
			}
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
	private function render_table_body(): string {
		$rows = '';

		if ( ! count( $this->cell_content ) ) {
			return $rows;
		}

		foreach ( $this->cell_content as $row ) {
			$columns = '';
			foreach ( $row['content'] as $column ) {
				$column_content = wp_kses_post( $column['content'] );
				if ( isset( $column['icon'] ) ) {
					$position       = $column['icon']['position'];
					$svg            = $column['icon']['svg'];
					$column_content = sprintf(
						'left' === $position ?
						'<div class="tutor-flex tutor-gap-3 tutor-items-center">%1$s%2$s</div>' :
						'<div class="tutor-flex tutor-gap-3 tutor-items-center">%2$s%1$s</div>',
						$svg,
						wp_kses_post( $column['content'] )
					);
				}

				if ( isset( $column['class'] ) ) {
					$columns .= sprintf(
						'<td class="%s">%s</td>',
						esc_attr( $column['class'] ),
						$column_content
					);
				} else {
					$columns .= sprintf(
						'<td>%s</td>',
						$column_content
					);
				}
			}

			if ( isset( $row['class'] ) ) {
				$rows .= sprintf( '<tr class="%s">%s</tr>', $row['class'], $columns );
			} else {
				$rows .= sprintf( '<tr>%s</tr>', $columns );
			}
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
