<?php
/**
 * Manage Rest API Authentication
 *
 * Token create, invoke etc
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.1
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Rest API authentication
 *
 * @since 2.2.1
 */
class RestAuth {
    /**
     * Permissions
     *
     * @var string
     */
    const READ = 'read';

    /**
     * Get available permission
     *
     * @since 2.2.1
     *
     * @return array
     */
    public static function available_permissions(): array {
        $permissions = array(
            array(
                'value' => self::READ,
                'label' => __( 'Read', 'tutor' ),
            )
        );
        return $permissions;
    }
}