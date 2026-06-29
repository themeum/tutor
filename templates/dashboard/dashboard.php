<?php
/**
 * Frontend Dashboard Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 *
 * @var int $total_students passed from tutor\templates\dashboard.php
 */

defined( 'ABSPATH' ) || exit;

do_action( 'tutor_before_dashboard_content' );
tutor_load_template( 'dashboard.components.profile-completion' );
tutor_load_template( 'dashboard.instructor.home', array( 'total_students' => $total_students ?? 0 ) );
