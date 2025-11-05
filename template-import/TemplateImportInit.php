<?php
/**
 * Initialize template importer
 *
 * @package Tutor\TemplateImporter
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.9.2
 */

namespace Tutor\TemplateImport;

use TUTOR\Container;

/**
 * Class TemplateImportInit
 */
final class TemplateImportInit {

	/**
	 * Register hooks
	 *
	 * @since 3.9.2
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Init packages
	 *
	 * @since 3.9.2
	 *
	 * @return void
	 */
	public function init() {
		Container::make( TemplateImporter::class );
	}
}
