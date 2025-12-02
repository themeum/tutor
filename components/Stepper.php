<?php
/**
 * Stepper Component Class.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

class Stepper extends BaseComponent {
    
    /**
     * Stepper options.
     *
     * @var array
     */
    protected $stepper_options;

    /**
     * Placeholder for stepper.
     *
     * @var string
     */
    protected $stepper_placeholder;

    /**
     * Stepper component value.
     *
     * @var string
     */
    protected $stepper_value;

    /**
     * Whether stepper is enabled or disabled.
     */
    protected $stepper_disabled;

    /**
     * Stepper name.
     */
    protected $stepper_name;

    
    public function render(): string
    {
        throw new \Exception('Not implemented');
    }

}