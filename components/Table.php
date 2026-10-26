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
 * ```php
 * // Minimal table with headings and rows
 * $headings = array(
 *     array( 'content' => __( 'Student',  'tutor' ) ),
 *     array( 'content' => __( 'Progress', 'tutor' ) ),
 *     array( 'content' => __( 'Score',    'tutor' ) ),
 * );
 *
 * $contents = array(
 *     array(
 *         'columns' => array(
 *             array( 'content' => 'Jane Doe' ),
 *             array( 'content' => '80%' ),
 *             array( 'content' => '92/100' ),
 *         ),
 *     ),
 *     array(
 *         'columns' => array(
 *             array( 'content' => 'John Smith' ),
 *             array( 'content' => '55%' ),
 *             array( 'content' => '61/100' ),
 *         ),
 *     ),
 * );
 *
 * Table::make()
 *     ->headings( $headings )
 *     ->contents( $contents )
 *     ->render();
 *
 * // Table with rich HTML inside cells and column borders
 * $headings = array(
 *     array( 'content' => __( 'Quiz Info', 'tutor' ) ),
 *     array( 'content' => __( 'Marks',     'tutor' ) ),
 * );
 *
 * $contents = array(
 *     array(
 *         'columns' => array(
 *             array(
 *                 'content' =>
 *                     '<div class="tutor-flex tutor-gap-3 tutor-items-center">' .
 *                     SvgIcon::make()->name( Icon::QUESTION_CIRCLE )->size( 16 )->get() .
 *                     esc_html__( 'Total Questions', 'tutor' ) .
 *                     '</div>',
 *             ),
 *             array( 'content' => 20 ),
 *         ),
 *     ),
 *     array(
 *         'columns' => array(
 *             array(
 *                 'content' =>
 *                     '<div class="tutor-flex tutor-gap-3 tutor-items-center">' .
 *                     SvgIcon::make()->name( Icon::STAR_FILL )->size( 16 )->get() .
 *                     esc_html__( 'Pass Mark', 'tutor' ) .
 *                     '</div>',
 *             ),
 *             array( 'content' => 14 ),
 *         ),
 *     ),
 * );
 *
 * Table::make()
 *     ->headings( $headings )
 *     ->contents( $contents )
 *     ->attrs( array( 'class' => 'tutor-table-column-borders tutor-mb-6' ) )
 *     ->render();
 *
 * // Table with a custom class on a specific heading column
 * $headings = array(
 *     array( 'content' => 'Name',    'class' => 'tutor-text-left' ),
 *     array( 'content' => 'Actions', 'class' => 'tutor-text-right' ),
 * );
 *
 * Table::make()
 *     ->headings( $headings )
 *     ->contents( $contents )
 *     ->render();
 *
 * // Retrieve HTML without echoing
 * $html = Table::make()->headings( $headings )->contents( $contents )->get();
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
				'<th class="%s">%s</th>',
				$heading['class'] ?? '',
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
		if ( isset( $this->attributes['class'] ) && ! empty( $this->attributes['class'] ) ) {
			$this->attributes['class'] = 'tutor-table ' . $this->attributes['class'];
		} else {
			$this->attributes['class'] = 'tutor-table';
		}

		ob_start();
		$this->render_attributes();
		$attrs = ob_get_clean();
		return sprintf(
			'<table %s>
					<thead>
						%s
					</thead>
					<tbody>
						%s
					</tbody>
				</table>
			',
			$attrs,
			$this->render_table_headings(),
			$this->render_table_body()
		);
	}
}
