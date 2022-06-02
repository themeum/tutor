<?php
namespace Tutor\Models;

/**
 * Class Course
 * @since 2.0.6
 */
class Course {
    /**
     * WordPress course type name
     * @var string
     */
    const POST_TYPE         = 'courses';

    const STATUS_PUBLISH    = 'publish';
    const STATUS_DRAFT      = 'draft';
}