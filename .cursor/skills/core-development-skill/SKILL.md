# Tutor LMS — Core Development Skill

## Overview

This skill governs all development, bug fixing, and feature work inside the **Tutor LMS** plugin codebase (`github.com/themeum/tutor`). It covers project structure, namespace conventions, coding patterns, PHPDoc standards, available helpers/utilities, security rules, and AI agent instructions.

**Always read and apply this skill before writing, editing, or reviewing any Tutor LMS PHP code.**

**Repository:** https://github.com/themeum/tutor  
**Text Domain:** `tutor`  
**PHP Namespace (core classes):** `TUTOR`  
**PHP Namespace (helpers/models):** `Tutor\Helpers`, `Tutor\Models`, `Tutor\Traits`, `Tutor\Cache`, `Tutor\Ecommerce`  
**Current Version:** 3.9.6 (as of April 2026)  
**Min PHP:** 7.4 | **Min WP:** 5.3

---

## PART 1 — PROJECT STRUCTURE

```
tutor/
├── tutor.php                        # Plugin header, constants, boot only
├── composer.json                    # PHP autoloader (PSR-4)
├── vendor/                          # Composer autoload
├── classes/                         # Core TUTOR\ namespace classes
│   ├── Tutor.php                    # Main singleton (extends Singleton)
│   ├── Tutor_Base.php               # Base class for content classes
│   ├── Singleton.php                # Base Singleton class
│   ├── Utils.php                    # Global utility methods (tutor_utils())
│   ├── Input.php                    # Sanitized input helper
│   ├── Course.php                   # Course operations
│   ├── Lesson.php                   # Lesson operations
│   ├── Quiz.php                     # Quiz operations
│   ├── User.php                     # User roles & operations
│   ├── Instructor.php               # Instructor registration/management
│   ├── Enrollment.php               # Enrollment logic
│   ├── Assets.php                   # Script/style enqueue
│   ├── Post_types.php               # Post type registration
│   ├── Shortcode.php                # Shortcode registration
│   ├── Ajax.php                     # Central AJAX handler registration
│   ├── Tools.php                    # Admin tools
│   └── ...                          # Other core classes
├── models/                          # Tutor\Models namespace
│   ├── CourseModel.php
│   ├── LessonModel.php
│   ├── QuizModel.php
│   ├── EnrollmentModel.php
│   ├── UserModel.php
│   ├── WithdrawModel.php
│   └── ...
├── helpers/                         # Tutor\Helpers namespace
│   ├── QueryHelper.php              # DB query helpers
│   ├── HttpHelper.php               # HTTP status codes + response helpers
│   ├── DateTimeHelper.php           # Date/time utilities
│   ├── ValidationHelper.php         # Input validation
│   └── ...
├── traits/                          # Tutor\Traits namespace
│   ├── JsonResponse.php             # json_response() method
│   └── ...
├── restapi/                         # REST API controllers
├── ecommerce/                       # Tutor\Ecommerce namespace
├── migrations/                      # DB migration classes
├── cache/                           # Tutor\Cache namespace (TutorCache)
├── templates/                       # Frontend PHP templates
│   ├── dashboard/                   # Dashboard page templates
│   ├── single-course/               # Course single page templates
│   └── ...
├── views/                           # Admin UI views (output only)
├── assets/
│   ├── css/                         # Compiled CSS
│   ├── js/                          # Compiled JS
│   └── lib/                         # Third-party libs (select2, etc.)
├── v2-library/                      # React/TS frontend source (rsbuild)
├── tests/
│   ├── Unit/
│   └── Integration/
└── languages/                       # Translation files (.pot/.po/.mo)
```

---

## PART 2 — NAMESPACES & AUTOLOADING

Tutor LMS uses **PSR-4 autoloading** via Composer. Always declare the correct namespace at the top of every file.

| Location | Namespace |
|---|---|
| `classes/` | `TUTOR` |
| `models/` | `Tutor\Models` |
| `helpers/` | `Tutor\Helpers` |
| `traits/` | `Tutor\Traits` |
| `cache/` | `Tutor\Cache` |
| `ecommerce/` | `Tutor\Ecommerce` |
| `restapi/` | `Tutor\RestAPI` |
| `migrations/` | `Tutor\Migrations` |

### Standard file header (all PHP files)

```php
<?php
/**
 * Brief description of what this class/file does.
 *
 * @package Tutor
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   x.x.x
 */

namespace TUTOR; // or Tutor\Models, Tutor\Helpers, etc.

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\QueryHelper;
use Tutor\Models\CourseModel;
use Tutor\Traits\JsonResponse;
```

---

## PART 3 — CORE BOOTSTRAP & SINGLETON PATTERN

### Main plugin bootstrap (`tutor.php`)

The main plugin file does **only** these four things:

```php
<?php
/**
 * Plugin Name: Tutor LMS
 * ...
 */

use TUTOR\Tutor;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

define( 'TUTOR_VERSION', '3.9.6' );
define( 'TUTOR_FILE', __FILE__ );

add_action( 'init', fn () => load_plugin_textdomain( 'tutor', false, basename( __DIR__ ) . '/languages' ) );

register_activation_hook( TUTOR_FILE, array( Tutor::class, 'tutor_activate' ) );
register_deactivation_hook( TUTOR_FILE, array( Tutor::class, 'tutor_deactivation' ) );
register_uninstall_hook( TUTOR_FILE, array( Tutor::class, 'tutor_uninstall' ) );

function tutor_lms() {
    return Tutor::get_instance();
}

$GLOBALS['tutor'] = tutor_lms();
```

### Accessing the main instance

```php
// Primary access — returns Tutor singleton
tutor();        // alias of tutor_lms()
tutor_lms();    // returns Tutor::get_instance()

// Properties available on tutor()
tutor()->path               // Plugin directory path
tutor()->url                // Plugin directory URL
tutor()->version            // Plugin version string
tutor()->has_pro            // bool — Pro version active
tutor()->course_post_type   // 'courses'
tutor()->lesson_post_type   // 'lesson'
tutor()->nonce_action       // Nonce action string
tutor()->nonce              // Nonce key string
tutor()->utils              // Utils class instance (same as tutor_utils())
```

### Singleton base pattern

All main service classes that need a single instance extend `Singleton`:

```php
namespace TUTOR;

final class My_Service extends Singleton {

    /**
     * Initialize the service.
     *
     * @since 1.0.0
     * @return void
     */
    protected function __construct() {
        parent::__construct();
        // register hooks here
    }
}

// Usage — never use `new My_Service()` directly
My_Service::get_instance();
```

---

## PART 4 — CLASS PATTERNS

### 4.1 Content class (extends Tutor_Base)

Use for classes that handle AJAX, hooks, and post-type operations (Course, Lesson, Quiz, etc.):

```php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\QueryHelper;
use Tutor\Models\CourseModel;
use Tutor\Traits\JsonResponse;

/**
 * Handle [Feature] operations.
 *
 * @package Tutor
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   1.0.0
 */
class My_Feature extends Tutor_Base {

    use JsonResponse;

    /**
     * Register hooks.
     *
     * @since 1.0.0
     *
     * @param bool $register_hooks Whether to register hooks. Default true.
     * @return void
     */
    public function __construct( $register_hooks = true ) {
        parent::__construct();

        if ( ! $register_hooks ) {
            return;
        }

        add_action( 'wp_ajax_tutor_my_action', array( $this, 'ajax_my_action' ) );
        add_action( 'wp_ajax_nopriv_tutor_my_public_action', array( $this, 'ajax_my_public_action' ) );
    }

    /**
     * Handle my AJAX action.
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_my_action() {
        tutor_utils()->checking_nonce();

        if ( ! current_user_can( 'edit_posts' ) ) {
            $this->json_response(
                tutor_utils()->error_message( 'forbidden' ),
                null,
                HttpHelper::STATUS_FORBIDDEN
            );
        }

        // ... logic
        $this->json_response( __( 'Success', 'tutor' ), $data );
    }
}
```

### 4.2 Model class (Tutor\Models namespace)

Models handle only data access — no hooks, no output:

```php
namespace Tutor\Models;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Tutor\Helpers\QueryHelper;

/**
 * My Feature Model.
 *
 * @package Tutor\Models
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   x.x.x
 */
class MyFeatureModel {

    /**
     * Table name without prefix.
     *
     * @since x.x.x
     * @var string
     */
    const TABLE = 'tutor_my_feature';

    /**
     * Get records by user ID.
     *
     * @since x.x.x
     *
     * @param int $user_id  WordPress user ID.
     * @param int $limit    Max rows to return. Default -1 (all).
     * @return array
     */
    public static function get_by_user( int $user_id, int $limit = -1 ): array {
        global $wpdb;

        return QueryHelper::get_all(
            $wpdb->prefix . self::TABLE,
            array( 'user_id' => $user_id ),
            'id',
            $limit
        );
    }

    /**
     * Insert a new record.
     *
     * @since x.x.x
     *
     * @param array $data Associative array of column => value pairs.
     * @return int|false Inserted row ID on success, false on failure.
     */
    public static function create( array $data ) {
        global $wpdb;
        $inserted = QueryHelper::insert( $wpdb->prefix . self::TABLE, $data );
        return $inserted ? $wpdb->insert_id : false;
    }
}
```

### 4.3 REST API controller

```php
namespace Tutor\RestAPI;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Tutor\Helpers\HttpHelper;
use Tutor\Traits\JsonResponse;

/**
 * My Feature REST Controller.
 *
 * @package Tutor\RestAPI
 * @since   x.x.x
 */
class MyFeatureController extends \WP_REST_Controller {

    use JsonResponse;

    /**
     * Namespace.
     *
     * @var string
     */
    protected $namespace = 'tutor/v1';

    /**
     * Rest base.
     *
     * @var string
     */
    protected $rest_base = 'my-feature';

    /**
     * Register routes.
     *
     * @since x.x.x
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_items' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                ),
            )
        );
    }

    /**
     * Check permissions.
     *
     * @since x.x.x
     *
     * @param \WP_REST_Request $request Full request data.
     * @return bool|\WP_Error
     */
    public function get_items_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    /**
     * Get a collection of items.
     *
     * @since x.x.x
     *
     * @param \WP_REST_Request $request Full request data.
     * @return \WP_REST_Response
     */
    public function get_items( $request ) {
        // ... logic
        return rest_ensure_response( array( 'data' => $data ) );
    }
}
```

---

## PART 5 — AVAILABLE HELPERS & UTILITIES

### 5.1 `tutor_utils()` / `tutils()` — Global Utility

The `Utils` class is the primary utility hub. Access via `tutor_utils()` or its alias `tutils()`.

```php
// Plugin options
tutor_utils()->get_option( 'option_key', $default );
tutor_utils()->get_option( 'option_key', null, true, true ); // return bool

// Nonce verification (dies on failure)
tutor_utils()->checking_nonce();

// Post ID resolution (uses current post if 0)
tutor_utils()->get_post_id( $post_id );

// User ID resolution (uses current user if 0)
tutor_utils()->get_user_id( $user_id );

// Enrollment checks
tutor_utils()->is_enrolled( $course_id, $user_id );
tutor_utils()->is_instructor_of_this_course( $user_id, $course_id );

// Array utilities
tutor_utils()->array_get( 'key', $array, $default );   // safe array access
tutor_utils()->avalue_dot( 'parent.child', $array );   // dot notation access

// Standardized error messages
tutor_utils()->error_message();             // Generic error
tutor_utils()->error_message( 'nonce' );    // Nonce failure message
tutor_utils()->error_message( 'forbidden' );// Permission denied message

// Input (legacy — prefer Input:: class)
tutor_utils()->input_old( 'field_name' );   // POST/GET value with sanitization

// Permissions
tutor_utils()->can_user_manage( 'course', $course_id );
tutor_utils()->can_user_manage( 'topic', $topic_id );

// Dashboard
tutor_utils()->get_tutor_dashboard_page_permalink( $tab );
tutor_utils()->course_edit_link( $course_id, 'frontend' );

// Time
tutor_time(); // current timestamp
```

### 5.2 `Input` class — Sanitized Input

Always use `Input::` class to read request data. Never access `$_POST`, `$_GET`, `$_REQUEST` directly.

```php
use TUTOR\Input;

// GET: string (default)
$page   = Input::get( 'page', '' );

// GET: integer
$id     = Input::get( 'id', 0, Input::TYPE_INT );

// GET: boolean
$active = Input::get( 'active', false, Input::TYPE_BOOL );

// POST: string
$title  = Input::post( 'title', '' );

// POST: integer
$count  = Input::post( 'count', 0, Input::TYPE_INT );

// POST: sanitized array
$ids    = Input::post( 'ids', array(), Input::TYPE_ARRAY );

// Any request method
$value  = Input::sanitize_data( $raw_value );
```

Available type constants: `Input::TYPE_INT`, `Input::TYPE_BOOL`, `Input::TYPE_FLOAT`, `Input::TYPE_ARRAY`, `Input::TYPE_EMAIL`, `Input::TYPE_URL`.

### 5.3 `QueryHelper` — Database Abstraction

```php
use Tutor\Helpers\QueryHelper;

global $wpdb;

// SELECT all rows matching conditions
$rows = QueryHelper::get_all(
    $wpdb->prefix . 'tutor_enrollments',
    array( 'course_id' => $course_id ),  // WHERE conditions
    'id',                                // ORDER BY column
    10                                   // limit (-1 = all)
);

// SELECT single row
$row = QueryHelper::get_row(
    $wpdb->prefix . 'tutor_enrollments',
    array( 'id' => $enrollment_id )
);

// INSERT
QueryHelper::insert( $wpdb->prefix . 'tutor_enrollments', $data );

// UPDATE
QueryHelper::update(
    $wpdb->prefix . 'tutor_enrollments',
    array( 'status' => 'completed' ),   // data to set
    array( 'id' => $enrollment_id )      // WHERE
);

// DELETE
QueryHelper::delete(
    $wpdb->prefix . 'tutor_enrollments',
    array( 'id' => $enrollment_id )
);

// COUNT
$total = QueryHelper::get_count(
    $wpdb->prefix . 'tutor_enrollments',
    array( 'course_id' => $course_id )
);

// IN clause helper
$clause = QueryHelper::prepare_in_clause( $ids_array ); // returns %d,%d,%d
```

### 5.4 `HttpHelper` — HTTP Status Constants

Always use `HttpHelper` constants for response codes — never hardcode numbers:

```php
use Tutor\Helpers\HttpHelper;

HttpHelper::STATUS_OK                    // 200
HttpHelper::STATUS_CREATED               // 201
HttpHelper::STATUS_BAD_REQUEST           // 400
HttpHelper::STATUS_UNAUTHORIZED          // 401
HttpHelper::STATUS_FORBIDDEN             // 403
HttpHelper::STATUS_NOT_FOUND             // 404
HttpHelper::STATUS_UNPROCESSABLE_ENTITY  // 422
HttpHelper::STATUS_INTERNAL_SERVER_ERROR // 500
```

### 5.5 `JsonResponse` trait

Use in any class that sends AJAX or REST responses. Never call `wp_send_json_success/error` directly when this trait is available.

```php
use Tutor\Traits\JsonResponse;

class My_Class {
    use JsonResponse;

    public function my_ajax_handler() {
        // Success
        $this->json_response( __( 'Done', 'tutor' ), $data );

        // Success with custom status
        $this->json_response( __( 'Created', 'tutor' ), $data, HttpHelper::STATUS_CREATED );

        // Error
        $this->json_response(
            tutor_utils()->error_message( 'nonce' ),
            null,
            HttpHelper::STATUS_BAD_REQUEST
        );
    }
}
```

Signature: `json_response( string $message, mixed $data = null, int $status_code = 200 ): void`

### 5.6 `DateTimeHelper` — Date & Time

```php
use Tutor\Helpers\DateTimeHelper;

// Format constants
DateTimeHelper::FORMAT_MYSQL      // 'Y-m-d H:i:s'
DateTimeHelper::FORMAT_DATE       // 'Y-m-d'

// Convert GMT datetime to user timezone
DateTimeHelper::get_gmt_to_user_timezone_date( $gmt_datetime_string );

// Formatting
date( DateTimeHelper::FORMAT_MYSQL, $timestamp );
```

### 5.7 `ValidationHelper`

```php
use Tutor\Helpers\ValidationHelper;

$errors = ValidationHelper::validate(
    array(
        'title'    => Input::post( 'title', '' ),
        'course_id'=> Input::post( 'course_id', 0, Input::TYPE_INT ),
    ),
    array(
        'title'     => 'required',
        'course_id' => 'required|numeric',
    )
);

if ( count( $errors ) ) {
    $this->json_response(
        __( 'Invalid input', 'tutor' ),
        $errors,
        HttpHelper::STATUS_UNPROCESSABLE_ENTITY
    );
}
```

### 5.8 `TutorCache`

```php
use Tutor\Cache\TutorCache;

// Get cached value
$data = TutorCache::get( 'cache-key' );

// Set cache
TutorCache::set( 'cache-key', $data );

// Delete cache
TutorCache::delete( 'cache-key' );
```

---

## PART 6 — MODEL REFERENCE

### CourseModel

```php
use Tutor\Models\CourseModel;

// Post status constants
CourseModel::STATUS_PUBLISH
CourseModel::STATUS_PENDING
CourseModel::STATUS_DRAFT
CourseModel::STATUS_FUTURE
CourseModel::STATUS_PRIVATE

// Methods
CourseModel::get_courses_by_instructor( $user_id, $status, $offset, $per_page, $count_only, $post_type );
CourseModel::get_post_types( $post );   // check if valid course post type
```

### User class

```php
use TUTOR\User;

User::STUDENT      // 'subscriber'
User::INSTRUCTOR   // 'tutor_instructor'
User::ADMIN        // 'administrator'

User::is_admin();  // static — checks if current user is admin
```

### Post type constants (via tutor())

```php
tutor()->course_post_type   // 'courses'
tutor()->lesson_post_type   // 'lesson'
// Quiz post type:           'tutor_quiz'
// Topic post type:          'topics'
// Assignment post type:     'tutor_assignments'
```

---

## PART 7 — PHPDoc DOCUMENTATION STANDARDS

All classes, methods, properties, constants, hooks, and filters **must** have PHPDoc blocks.

### Class doc block

```php
/**
 * Brief one-line description.
 *
 * Optional longer description spanning
 * multiple lines if needed.
 *
 * @package Tutor
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   1.0.0
 */
```

### Method doc block

```php
/**
 * Brief description of what this method does.
 *
 * Extended description if the method has complex behavior,
 * side effects, or important notes for future developers.
 *
 * @since 1.0.0
 * @since 2.5.0 Added $limit param.
 *
 * @param int    $course_id  The course post ID.
 * @param int    $user_id    Optional. WordPress user ID. Default 0 (current user).
 * @param int    $limit      Optional. Max records to return. Default -1 (all).
 * @return array|false       Array of records on success, false on failure.
 */
public function get_enrollments( int $course_id, int $user_id = 0, int $limit = -1 ) {
```

### Property doc block

```php
/**
 * Whether bulk action is enabled on this list page.
 *
 * @since 1.0.0
 * @var bool
 */
public $bulk_action = true;

/**
 * Cache key for instructor list.
 *
 * @since 2.0.0
 * @var string
 */
const INSTRUCTOR_LIST_CACHE_KEY = 'tutor-instructors-list';
```

### Hook documentation

```php
/**
 * Fires after a student successfully enrolls in a course.
 *
 * @since 1.0.0
 *
 * @param int $course_id    The course post ID.
 * @param int $user_id      The enrolled user ID.
 * @param int $enroll_id    The enrollment record ID.
 */
do_action( 'tutor_after_enroll', $course_id, $user_id, $enroll_id );

/**
 * Filters the course archive query arguments.
 *
 * @since 1.0.0
 *
 * @param array $args  WP_Query arguments.
 * @return array
 */
$args = apply_filters( 'tutor_course_archive_args', $args );
```

---

## PART 8 — SECURITY BEST PRACTICES

### 8.1 Nonce verification

**Every AJAX handler must verify a nonce first.** Use the Tutor utility wrapper:

```php
// ✅ Tutor standard — checks tutor()->nonce_action and dies on failure
tutor_utils()->checking_nonce();

// ✅ WP standard when you need a specific action
if ( ! wp_verify_nonce(
    sanitize_text_field( wp_unslash( $_POST['_tutor_nonce'] ?? '' ) ),
    tutor()->nonce_action
) ) {
    $this->json_response(
        tutor_utils()->error_message( 'nonce' ),
        null,
        HttpHelper::STATUS_BAD_REQUEST
    );
}
```

### 8.2 Capability checks

**All privileged AJAX/REST handlers must check permissions before processing:**

```php
// Admin operations
if ( ! current_user_can( 'manage_options' ) ) {
    $this->json_response(
        tutor_utils()->error_message( 'forbidden' ),
        null,
        HttpHelper::STATUS_FORBIDDEN
    );
}

// Course management (admin or instructor of the course)
$is_instructor = tutor_utils()->is_instructor_of_this_course( get_current_user_id(), $course_id );
if ( ! current_user_can( 'administrator' ) && ! $is_instructor ) {
    $this->json_response(
        tutor_utils()->error_message( 'forbidden' ),
        null,
        HttpHelper::STATUS_FORBIDDEN
    );
}

// Resource management via helper
if ( ! tutor_utils()->can_user_manage( 'course', $course_id ) ) {
    wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
}
```

### 8.3 Input — always use Input:: class

```php
// ✅ Always use Input:: — never raw superglobals
$course_id = Input::post( 'course_id', 0, Input::TYPE_INT );
$title     = Input::post( 'title', '' );
$page      = Input::get( 'page', '' );

// ❌ Never do this
$course_id = $_POST['course_id'];
$title     = $_POST['title'];
```

### 8.4 Output escaping

Follow context-specific escaping. This is always required in templates and views:

```php
// HTML text
echo esc_html( $title );
echo esc_html__( 'Enroll Now', 'tutor' );

// HTML attributes
echo esc_attr( $class );
echo '<div id="' . esc_attr( $id ) . '">';

// URLs
echo esc_url( get_permalink( $course_id ) );
echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( 'my-courses' ) );

// Rich HTML (trusted content only)
echo wp_kses_post( $description );

// In templates
?>
<h2><?php echo esc_html( $course->post_title ); ?></h2>
<a href="<?php echo esc_url( get_permalink( $course->ID ) ); ?>">
    <?php echo esc_html( $course->post_title ); ?>
</a>
<span class="<?php echo esc_attr( $status_class ); ?>">
    <?php echo esc_html( $status_label ); ?>
</span>
<?php
```

### 8.5 Database queries

**Never write raw SQL when QueryHelper covers the operation.** Use `$wpdb->prepare()` for custom queries:

```php
// ✅ Use QueryHelper for standard CRUD
$rows = QueryHelper::get_all( $wpdb->prefix . 'tutor_enrollments', array( 'course_id' => $course_id ) );

// ✅ Use $wpdb->prepare() for custom queries
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}tutor_enrollments WHERE course_id = %d AND status = %s LIMIT %d",
        $course_id,
        'completed',
        $limit
    )
);

// ❌ Never interpolate variables into queries
$results = $wpdb->get_results( "SELECT * FROM wp_tutor_enrollments WHERE course_id = $course_id" );
```

### 8.6 Direct file access prevention

All PHP files must have this check at the top, immediately after the namespace declaration:

```php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

---

## PART 9 — AJAX HANDLER PATTERN

The complete, correct pattern for every Tutor AJAX handler:

```php
/**
 * Handle AJAX action to save lesson progress.
 *
 * @since 1.0.0
 * @return void
 */
public function ajax_save_lesson_progress() {
    // Step 1: Verify nonce (always first)
    tutor_utils()->checking_nonce();

    // Step 2: Verify permissions
    if ( ! is_user_logged_in() ) {
        $this->json_response(
            tutor_utils()->error_message( 'forbidden' ),
            null,
            HttpHelper::STATUS_FORBIDDEN
        );
    }

    // Step 3: Read and validate input via Input:: class
    $lesson_id = Input::post( 'lesson_id', 0, Input::TYPE_INT );
    $course_id = Input::post( 'course_id', 0, Input::TYPE_INT );

    if ( ! $lesson_id || ! $course_id ) {
        $this->json_response(
            __( 'Invalid input', 'tutor' ),
            null,
            HttpHelper::STATUS_BAD_REQUEST
        );
    }

    // Step 4: Business logic / data operation
    $result = MyModel::save_progress( $lesson_id, get_current_user_id() );

    if ( ! $result ) {
        $this->json_response(
            __( 'Could not save progress', 'tutor' ),
            null,
            HttpHelper::STATUS_INTERNAL_SERVER_ERROR
        );
    }

    // Step 5: Fire action hook for extensibility
    do_action( 'tutor_lesson_progress_saved', $lesson_id, $course_id, get_current_user_id() );

    // Step 6: Return success response
    $this->json_response( __( 'Progress saved', 'tutor' ) );
}
```

Register handlers in the constructor:

```php
add_action( 'wp_ajax_tutor_save_lesson_progress', array( $this, 'ajax_save_lesson_progress' ) );
// For logged-out users:
add_action( 'wp_ajax_nopriv_tutor_save_lesson_progress', array( $this, 'ajax_save_lesson_progress' ) );
```

---

## PART 10 — HOOK NAMING CONVENTIONS

All Tutor hooks are prefixed with `tutor_`. Follow this naming structure:

| Type | Pattern | Example |
|---|---|---|
| Action (before) | `tutor_before_{event}` | `tutor_before_enroll` |
| Action (after) | `tutor_after_{event}` | `tutor_after_enroll` |
| Action (AJAX) | `tutor_action_{name}` | `tutor_action_regenerate_tutor_pages` |
| Filter (data) | `tutor_{object}_{property}` | `tutor_course_archive_args` |
| Filter (template) | `tutor_get_template_path` | — |
| Filter (localize) | `tutor_localize_data` | — |

Dynamic hooks use **interpolation**, not concatenation:

```php
// ✅ Correct
do_action( "{$post->post_type}_saved", $post->ID );
apply_filters( "tutor_{$context}_data", $data );

// ❌ Incorrect
do_action( $post->post_type . '_saved', $post->ID );
```

---

## PART 11 — TEMPLATE & VIEW RULES

### Templates (`templates/`)

Templates are rendered on the **frontend**. Strict output-only rules:

```php
<?php
/**
 * Template: My Feature Template.
 *
 * @package Tutor
 * @since   x.x.x
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use TUTOR\Input;
use Tutor\Models\CourseModel;

// ✅ Allowed: read data and pass to template vars
$current_user_id = get_current_user_id();
$per_page        = tutor_utils()->get_option( 'courses_per_page', 10 );
$paged           = Input::get( 'current_page', 1, Input::TYPE_INT );
$results         = CourseModel::get_courses_by_instructor( $current_user_id, 'publish', 0, $per_page );
?>

<div class="tutor-my-feature">
    <?php foreach ( $results as $item ) : ?>
        <div class="tutor-item">
            <h3><?php echo esc_html( $item->post_title ); ?></h3>
            <a href="<?php echo esc_url( get_permalink( $item->ID ) ); ?>">
                <?php esc_html_e( 'View', 'tutor' ); ?>
            </a>
        </div>
    <?php endforeach; ?>
</div>
```

**Templates must NOT contain:**
- Database queries (use Models)
- Hook registration
- Business logic / calculations
- Nonce generation (pass via template variables from controller)

### Views (`views/`)

Views are rendered in the **WordPress admin**. Same rules as templates — output only.

---

## PART 12 — ASSETS & ENQUEUEING

Always enqueue via the `Assets` class hooks. Never hardcode `<script>` or `<link>` tags:

```php
/**
 * Register hooks.
 *
 * @since 1.0.0
 * @return void
 */
public function __construct() {
    add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
}

/**
 * Enqueue admin scripts and styles.
 *
 * @since 1.0.0
 * @return void
 */
public function admin_scripts() {
    wp_enqueue_style(
        'tutor-my-feature-admin',
        tutor()->url . 'assets/css/my-feature-admin.min.css',
        array(),
        TUTOR_VERSION
    );

    wp_enqueue_script(
        'tutor-my-feature-admin',
        tutor()->url . 'assets/js/my-feature-admin.min.js',
        array( 'jquery', 'wp-i18n' ),
        TUTOR_VERSION,
        true
    );

    // Pass data to JS — never hardcode in JS files
    wp_localize_script(
        'tutor-my-feature-admin',
        'tutorMyFeature',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( tutor()->nonce_action ),
            'i18n'    => array(
                'success' => __( 'Saved successfully', 'tutor' ),
                'error'   => __( 'Something went wrong', 'tutor' ),
            ),
        )
    );
}
```

---

## PART 13 — CODING STANDARDS RULES

### Naming

```php
// ✅ Class names: Capitalized_Words (TUTOR namespace)
class Enrollment_Controller {}

// ✅ Functions/variables: lowercase_with_underscores
function tutor_get_enrollment_count( $course_id ) {}
$enrollment_count = 0;

// ✅ Constants: ALL_CAPS_WITH_UNDERSCORES
const ENROLLMENT_STATUS_ACTIVE = 'active';
define( 'TUTOR_VERSION', '3.9.6' );

// ❌ Never camelCase for PHP functions/variables
function tutorGetEnrollmentCount() {} // wrong
$enrollmentCount = 0;                 // wrong
```

### Arrays

```php
// ✅ Always long array syntax
$args = array(
    'post_type'   => 'courses',
    'post_status' => 'publish',
    'numberposts' => 10,
);

// ❌ Short array syntax not used in Tutor core
$args = ['post_type' => 'courses'];
```

### Yoda conditions

```php
// ✅ Required for ==, !=, ===, !==
if ( 'publish' === $post->post_status ) {}
if ( null === $value ) {}
if ( true === $is_enrolled ) {}

// ❌ Incorrect
if ( $post->post_status === 'publish' ) {}
```

### Brace style & spacing

```php
// ✅ Spaces inside parentheses of control structures
foreach ( $courses as $course ) {
    process( $course );
}

if ( $condition ) {
    action_one();
} elseif ( $other ) {
    action_two();
} else {
    default_action();
}

// ✅ Function calls — spaces inside parens
tutor_utils()->get_option( 'key', 'default' );
Input::post( 'field', '', Input::TYPE_INT );
```

---

## PART 14 — WHAT NOT TO DO IN TUTOR LMS

| ❌ Never Do | ✅ Do Instead |
|---|---|
| `$_POST['field']` directly | `Input::post( 'field', '', Input::TYPE_* )` |
| `$_GET['param']` directly | `Input::get( 'param', '', Input::TYPE_* )` |
| Raw SQL in controllers/classes | `QueryHelper::*` or `$wpdb->prepare()` |
| `wp_send_json_success/error` in classes with `JsonResponse` | `$this->json_response(...)` |
| Hardcode HTTP status numbers (`400`, `403`) | `HttpHelper::STATUS_BAD_REQUEST`, etc. |
| `new Tutor()` or `new Singleton_Class()` | `Tutor::get_instance()` or `tutor()` |
| `echo $var` without escaping | `echo esc_html( $var )` |
| Business logic in `templates/` | Move to Model or class method |
| `global $wpdb` in templates | Use Models or pass data from controller |
| `wp_verify_nonce()` without `wp_unslash()` | `tutor_utils()->checking_nonce()` or `wp_verify_nonce( sanitize_text_field( wp_unslash( ... ) ), ... )` |
| Hook registration in Models | Only in class constructors (`classes/`) |
| `eval()`, `extract()`, backtick operator | Never — forbidden |
| Omit `@since` in PHPDoc | Always include `@since x.x.x` |
| Skip `@param`/`@return` docs | Always document all params and return values |

---

## PART 15 — AI AGENT INSTRUCTIONS

When developing, fixing, or updating Tutor LMS code, follow these rules strictly:

1. **Read existing patterns first.** Before creating new code, check if a similar class/model/helper already exists in `classes/`, `models/`, or `helpers/`. Prefer extending what exists.

2. **Use the correct namespace.** Core classes → `TUTOR`, models → `Tutor\Models`, helpers → `Tutor\Helpers`.

3. **Always use `Input::` class** for reading any request data — never raw superglobals.

4. **Always use `QueryHelper::`** for standard CRUD — only use raw `$wpdb->prepare()` for complex custom queries.

5. **Always use `JsonResponse` trait** in any class that sends AJAX responses. Use `HttpHelper` constants for status codes.

6. **Security is non-negotiable in every handler:** nonce → capability → input → logic → response.

7. **Always use `tutor_utils()->checking_nonce()`** at the top of AJAX handlers.

8. **All output in templates and views must be escaped** with the appropriate `esc_*()` function.

9. **Write complete PHPDoc blocks** for every class, method, property, constant, and hook. Always include `@since`, `@param`, `@return`.

10. **Hook names must be prefixed `tutor_`** and use snake_case. Dynamic hooks use `"{$var}_event"` interpolation — never concatenation.

11. **All AJAX action names must be prefixed `tutor_`:** `wp_ajax_tutor_my_action`.

12. **Templates and views are output-only.** No database queries, no business logic, no hook registration.

13. **Test files go in `tests/Unit/` or `tests/Integration/`** and must correspond to the class being tested.

14. **Never add feature logic to `tutor.php`** — it boots the plugin only.

15. **Run PHPCS before committing:** `./vendor/bin/phpcs --standard=WordPress` must pass with zero errors.

---

## QUICK REFERENCE CHECKLIST

Before submitting any Tutor LMS code change, verify:

- [ ] Correct namespace declared (`TUTOR`, `Tutor\Models`, `Tutor\Helpers`, etc.)
- [ ] `if ( ! defined( 'ABSPATH' ) ) { exit; }` present in every PHP file
- [ ] `Input::post()`/`Input::get()` used for all request data — no raw superglobals
- [ ] `tutor_utils()->checking_nonce()` called first in every AJAX handler
- [ ] `current_user_can()` or `tutor_utils()->can_user_manage()` checked before privileged actions
- [ ] `QueryHelper::*` used for standard CRUD, `$wpdb->prepare()` for custom SQL
- [ ] `JsonResponse` trait used for AJAX responses with `HttpHelper` status constants
- [ ] All output escaped with correct `esc_*()` function
- [ ] PHPDoc on every class, method, property, constant, hook (`@since`, `@param`, `@return`)
- [ ] Long array syntax `array()` used throughout
- [ ] Yoda conditions for `==`, `!=`, `===`, `!==`
- [ ] Hook/action names prefixed with `tutor_`
- [ ] AJAX action names prefixed with `wp_ajax_tutor_`
- [ ] No business logic in `templates/` or `views/`
- [ ] No database queries or hook registration in templates
- [ ] PHPCS `--standard=WordPress` passes with zero errors