<?php
/**
 * Meta box wrapper class
 *
 * @package  Tutor\Metabox
 *
 * @since v2.0.2
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MetaboxWrapper {

    protected static $custom_class;

    public static function register_meta_box_from_wrapper(
        string $id,
        string $title,
        $callback,
        string $screen,
        string $context,
        string $priority,
        string $custom_class = ''
    ) {
        self::$custom_class = $custom_class;
        add_meta_box( $id, $title, $callback, $screen, $context, $priority );
        $post_type = tutor()->course_post_type;
        add_filter( "postbox_classes_{$post_type}_{$id}", array( __CLASS__, 'add_custom_meta_box_class' ) );
    }

    public static function add_custom_meta_box_class( $classes ) {
        if ( '' !== self::$custom_class ) {
            if ( ! in_array( self::$custom_class, $classes ) ) {
                $classes[] = self::$custom_class;
            }
        }
        return $classes;
    }
}