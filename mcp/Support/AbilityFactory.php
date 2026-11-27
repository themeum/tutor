<?php
/**
 * Register MCP tool and resource abilities.
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Shared wp_register_ability helpers for Tutor MCP.
 *
 * @since 4.3.0
 */
class AbilityFactory {

	/**
	 * Default read-only tool annotations.
	 *
	 * @since 4.3.0
	 *
	 * @var array<string, bool>
	 */
	public const READ_HINTS = array(
		'readOnlyHint'    => true,
		'destructiveHint' => false,
	);

	/**
	 * Shared write-tool annotations (non-destructive mutation).
	 *
	 * @since 4.3.0
	 *
	 * @var array<string, bool>
	 */
	public const WRITE_HINTS = array(
		'readOnlyHint'    => false,
		'destructiveHint' => false,
	);

	/**
	 * Shared destructive-tool annotations.
	 *
	 * @since 4.3.0
	 *
	 * @var array<string, bool>
	 */
	public const DESTRUCTIVE_HINTS = array(
		'readOnlyHint'    => false,
		'destructiveHint' => true,
	);

	/**
	 * Register a tool ability.
	 *
	 * @since 4.3.0
	 *
	 * @param string                   $id          Ability ID.
	 * @param string                   $category    Category.
	 * @param string                   $label       Label.
	 * @param string                   $desc        Description.
	 * @param array                    $schema      Input schema.
	 * @param callable                 $perm        Permission callback.
	 * @param callable                 $exec        Execute callback.
	 * @param array<string, bool>|null $annotations Optional MCP annotation overrides.
	 * @return void
	 */
	public static function tool( string $id, string $category, string $label, string $desc, array $schema, $perm, $exec, $annotations = null ): void {
		$default_annotations = self::READ_HINTS;
		if ( is_array( $annotations ) ) {
			$default_annotations = array_merge( $default_annotations, $annotations );
		}

		wp_register_ability(
			$id,
			array(
				'label'               => $label,
				'description'         => $desc,
				'category'            => $category,
				'input_schema'        => self::normalize_input_schema( $schema ),
				'permission_callback' => $perm,
				'execute_callback'    => static function ( $input = array() ) use ( $exec ) {
					$input = is_array( $input ) ? $input : array();
					return call_user_func( $exec, $input );
				},
				'meta'                => array(
					'mcp'         => array(
						'public' => true,
						'type'   => 'tool',
					),
					'annotations' => $default_annotations,
				),
			)
		);
	}

	/**
	 * Register a resource ability.
	 *
	 * @since 4.3.0
	 *
	 * @param string        $id     Ability ID.
	 * @param string        $uri    Resource URI.
	 * @param string        $label  Label.
	 * @param callable      $exec   Execute.
	 * @param callable|null $perm   Permission.
	 * @param array|null    $schema Input schema.
	 * @return void
	 */
	public static function resource( string $id, string $uri, string $label, $exec, $perm = null, $schema = null ): void {
		wp_register_ability(
			$id,
			array(
				'label'               => $label,
				'description'         => $label,
				'category'            => 'tutor-mcp-schema',
				'input_schema'        => self::normalize_input_schema(
					$schema ? $schema : array(
						'type'       => 'object',
						'properties' => self::empty_object_schema(),
					)
				),
				'permission_callback' => $perm ? $perm : static function (): bool {
					return Permissions::can_manage_courses();
				},
				'execute_callback'    => static function ( $input = array() ) use ( $exec ) {
					$input = is_array( $input ) ? $input : array();
					return call_user_func( $exec, $input );
				},
				'meta'                => array(
					'uri' => $uri,
					'mcp' => array(
						'public' => true,
						'type'   => 'resource',
					),
				),
			)
		);
	}

	/**
	 * Empty JSON-object schema fragment.
	 *
	 * PHP empty arrays encode as JSON `[]`; MCP clients (e.g. Cursor) require
	 * `inputSchema.properties` to be a JSON object `{}`.
	 *
	 * @since 4.3.0
	 *
	 * @return \stdClass
	 */
	public static function empty_object_schema(): \stdClass {
		return new \stdClass();
	}

	/**
	 * Ensure object-schema maps encode as JSON objects, not arrays.
	 *
	 * @since 4.3.0
	 *
	 * @param array $schema Input schema.
	 * @return array
	 */
	public static function normalize_input_schema( array $schema ): array {
		if ( array_key_exists( 'properties', $schema ) && is_array( $schema['properties'] ) && array() === $schema['properties'] ) {
			$schema['properties'] = self::empty_object_schema();
		}

		return $schema;
	}
}
