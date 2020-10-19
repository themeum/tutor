<?php

namespace TUTOR;

if (!defined('ABSPATH'))
    exit;

class Private_Course_Access {

    private $necessary_child_types = [
        '\'courses\'',
        '\'topics\'',
        '\'lesson\'',
        '\'tutor_quiz\'',
        '\'tutor_announcements\'',
        '\'tutor_assignments\''
    ];

    public function __construct() {
        add_action('pre_get_posts', array($this, 'enable_private_access'));
    }

    public function enable_private_access($query = null) {

        if (!is_admin()) {
            global $wpdb;
            $p_type = implode(',', $this->necessary_child_types);
            $p_name = isset($query->query['name']) ? esc_sql($query->query['name']) : '';

            if (empty($p_name)) {
                $query->set('post_status', array('private', 'publish'));
                return;
            }

            // Get using raw query to speed up
            $private_query = "SELECT ID, post_parent FROM {$wpdb->posts} WHERE post_type IN ({$p_type}) AND post_name='{$p_name}' AND post_status='private'";
            $private_result = $wpdb->get_results($private_query);
            $private_course_id = count($private_result) > 0;

            if (count($private_result) > 0) {
                add_action('pre_get_posts', array($this, 'enable_private_access'));
                $query->set('post_status', array('private', 'publish'));
            }
        }
    }
}
