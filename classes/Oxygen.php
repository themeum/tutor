<?php
/**
 * Oxygen class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.5.0
 */


namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Oxygen {

	public function __construct() {

		// Register +Add Tutor section
		add_action('oxygen_add_plus_sections', array($this, 'register_add_plus_section'));

		// Register +Add Tutor subsections
		// oxygen_add_plus_{$id}_section_content
		add_action('oxygen_add_plus_tutor_section_content', array($this, 'register_add_plus_subsections'));
	}

	public function register_add_plus_section() {
		\CT_Toolbar::oxygen_add_plus_accordion_section("tutor",__("Tutor LMS", 'tutor'));
	}

	public function register_add_plus_subsections() { ?>

		<h2><?php _e("Single Course", 'tutor');?></h2>
		<?php do_action("oxygen_add_plus_tutor_single"); ?>

		<h2><?php _e("Archive & Product List", 'tutor');?></h2>
		<?php do_action("oxygen_add_plus_tutor_archive"); ?>

		<h2><?php _e("Tutor Pages", 'tutor');?></h2>
		<?php do_action("oxygen_add_plus_tutor_pages"); ?>

		<h2><?php _e("Other Elements", 'tutor');?></h2>
		<?php do_action("oxygen_add_plus_tutor_other"); ?>

	<?php }


}