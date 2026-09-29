# WordPress Coding Standards & Security Skill

## Overview

This skill enforces strict WordPress coding standards (WPCS) and security best practices for all WordPress PHP, JavaScript, CSS, and HTML code. Apply these rules automatically whenever writing or reviewing WordPress plugin, theme, or core-adjacent code.

**Official References:**

- WPCS: https://developer.wordpress.org/coding-standards/wordpress-coding-standards/
- PHP Standards: https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/
- JS Standards: https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/
- CSS Standards: https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/
- Security API: https://developer.wordpress.org/apis/security/
- Sanitizing: https://developer.wordpress.org/apis/security/sanitizing/
- Escaping: https://developer.wordpress.org/apis/security/escaping/
- Nonces: https://developer.wordpress.org/apis/security/nonces/
- Data Validation: https://developer.wordpress.org/apis/security/data-validation/
- Common Vulnerabilities: https://developer.wordpress.org/apis/security/common-vulnerabilities/

---

## PART 1 — PHP CODING STANDARDS

### 1.1 PHP Tags

- Always use full `<?php ?>` tags. Never use shorthand `<?` or `<?=`.
- In multi-line PHP-within-HTML blocks, opening and closing tags must be on their own lines.

```php
// ✅ Correct
<input name="<?php echo esc_attr( $name ); ?>" />

// ❌ Incorrect
<? echo $name; ?>
<?= $name ?>
```

### 1.2 Naming Conventions

| Construct                             | Convention                   | Example                       |
| ------------------------------------- | ---------------------------- | ----------------------------- |
| Functions / variables / hooks         | `lowercase_with_underscores` | `my_plugin_get_data()`        |
| Classes / Interfaces / Traits / Enums | `Capitalized_Words`          | `My_Plugin_Handler`           |
| Constants                             | `ALL_CAPS_WITH_UNDERSCORES`  | `MY_PLUGIN_VERSION`           |
| Files (general)                       | `lowercase-hyphenated.php`   | `my-plugin-name.php`          |
| Class files                           | `class-{classname}.php`      | `class-my-plugin-handler.php` |

- Never use `camelCase` for functions or variables.
- Never abbreviate variable names unnecessarily — code must be self-documenting.
- Always prefix functions, hooks, globals, and constants with a unique plugin/theme slug to avoid collisions.

```php
// ✅ Correct
function myplugin_register_settings() {}
define( 'MYPLUGIN_VERSION', '1.0.0' );

// ❌ Incorrect
function registerSettings() {}
define( 'VERSION', '1.0.0' );
```

### 1.3 Spacing & Indentation

- Use **real tabs** (not spaces) for indentation.
- Spaces may be used mid-line for alignment only.
- Put spaces after commas, and on both sides of logical, arithmetic, comparison, and assignment operators.
- Put spaces inside parentheses of control structures, but NOT inside array index brackets for string/integer keys.
- No trailing whitespace at end of lines.
- Omit the closing `?>` PHP tag at end of files.

```php
// ✅ Correct
foreach ( $items as $key => $value ) {
    $result = $key . ': ' . $value;
}

$x = $foo['bar'];    // String key — no spaces inside brackets
$x = $foo[ $bar ];   // Variable key — spaces inside brackets

// ❌ Incorrect
foreach($items as $key=>$value){
    $result=$key.': '.$value;
}
```

### 1.4 Brace Style

- Always use braces for all control structure blocks — even single-line bodies.
- Use `elseif` (not `else if`).
- Opening brace on the same line as the statement.

```php
// ✅ Correct
if ( $condition ) {
    action();
} elseif ( $other ) {
    other_action();
} else {
    default_action();
}

// ❌ Incorrect
if ( $condition )
    action();
else if ( $other ) { other_action(); }
```

### 1.5 Yoda Conditions

Always place the constant/literal on the **left** side of comparisons to prevent accidental assignment.

```php
// ✅ Correct
if ( true === $flag ) {}
if ( 'publish' === $post->post_status ) {}
if ( null === $value ) {}

// ❌ Incorrect
if ( $flag === true ) {}
if ( $post->post_status === 'publish' ) {}
```

Applies to `==`, `!=`, `===`, `!==`. Not required for `<`, `>`, `<=`, `>=`.

### 1.6 Arrays

- Always use long array syntax: `array()` — not `[]`.
- Multi-item arrays must be written with one item per line, trailing comma included.

```php
// ✅ Correct
$args = array(
    'post_type'   => 'post',
    'post_status' => 'publish',
    'numberposts' => 10,
);

// ❌ Incorrect
$args = ['post_type' => 'post', 'post_status' => 'publish'];
```

### 1.7 Object-Oriented PHP

- Declare **visibility** (`public`, `protected`, `private`) on all properties and methods — never use `var`.
- Only **one class/interface/trait/enum per file**.
- Follow modifier order: `abstract|final` → `readonly` → visibility → `static` → type.
- Always use parentheses when instantiating objects: `new Foo()` not `new Foo`.

```php
// ✅ Correct
class My_Plugin_Handler {

    public static $instance = null;

    protected function __construct() {}

    public static function get_instance() {
        if ( null === static::$instance ) {
            static::$instance = new static();
        }
        return static::$instance;
    }
}
```

### 1.8 Database Queries

- **Never** write raw SQL when a WordPress API function exists.
- Always use `$wpdb->prepare()` for any query with user-supplied data.
- Use `%d`, `%s`, `%f`, `%i` placeholders — never quote them inside `prepare()`.
- Capitalize SQL keywords: `SELECT`, `WHERE`, `UPDATE`, `INSERT`, etc.

```php
// ✅ Correct
global $wpdb;
$result = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT post_title FROM $wpdb->posts WHERE ID = %d AND post_status = %s",
        $post_id,
        'publish'
    )
);

// ❌ Incorrect — SQL injection risk
$result = $wpdb->get_var( "SELECT post_title FROM wp_posts WHERE ID = $post_id" );
```

### 1.9 Forbidden Constructs

Never use these — they are security risks or bad practice:

| Forbidden                 | Reason                                 |
| ------------------------- | -------------------------------------- |
| `eval()`                  | Arbitrary code execution               |
| `create_function()`       | Deprecated, wraps `eval()`             |
| `extract()`               | Creates unpredictable variable scope   |
| Backtick operator `` ` `` | Identical to `shell_exec()`            |
| `@` error suppression     | Hides real errors, dangerous pre-PHP 8 |
| `goto`                    | Unreadable, unmaintainable             |
| Short ternary `?:`        | Confusing behavior                     |

### 1.10 Dynamic Hook Names

Use interpolation with curly braces, not concatenation:

```php
// ✅ Correct
do_action( "{$post->post_type}_saved", $post->ID );

// ❌ Incorrect
do_action( $post->post_type . '_saved', $post->ID );
```

### 1.11 Includes & Requires

- Prefer `require_once` over `include_once` for critical files.
- No parentheses around the path.

```php
// ✅ Correct
require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-my-handler.php';

// ❌ Incorrect
include_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
```

---

## PART 2 — SECURITY BEST PRACTICES

Security principle: **Never trust any data.** Validate on input. Sanitize before saving. Escape on output.

### 2.1 Nonces (CSRF Protection)

Every form submission, AJAX request, or state-changing URL must be protected with a nonce.

```php
// ✅ Creating a nonce in a form
wp_nonce_field( 'myplugin_save_settings', 'myplugin_nonce' );

// ✅ Creating a nonce in a URL
$url = wp_nonce_url( admin_url( 'admin.php?action=delete&id=' . $id ), 'myplugin_delete_' . $id );

// ✅ Verifying a nonce before processing
if ( ! isset( $_POST['myplugin_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['myplugin_nonce'] ) ), 'myplugin_save_settings' ) ) {
    wp_die( esc_html__( 'Security check failed.', 'myplugin' ) );
}

// ✅ For AJAX
check_ajax_referer( 'myplugin_ajax_action', 'nonce' );
```

### 2.2 Capability Checks (Authorization)

Always verify the current user has permission before performing privileged actions.

```php
// ✅ Correct — check before processing
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'You do not have permission to do this.', 'myplugin' ) );
}

// ✅ In AJAX handlers
add_action( 'wp_ajax_myplugin_save', 'myplugin_ajax_save' );
function myplugin_ajax_save() {
    check_ajax_referer( 'myplugin_save_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'myplugin' ) ) );
    }
    // ... process
}
```

### 2.3 Sanitizing Input

Sanitize all incoming data before saving to database or using in logic. Choose the most specific sanitizer available.

| Data Type                | Sanitizer                              |
| ------------------------ | -------------------------------------- |
| Plain text (single line) | `sanitize_text_field()`                |
| Multi-line text          | `sanitize_textarea_field()`            |
| Email address            | `sanitize_email()`                     |
| URL                      | `esc_url_raw()`                        |
| Integer                  | `absint()` or `intval()`               |
| Float                    | `floatval()`                           |
| HTML content             | `wp_kses_post()` or `wp_kses()`        |
| File name                | `sanitize_file_name()`                 |
| CSS class/ID             | `sanitize_html_class()`                |
| Slug/key                 | `sanitize_key()` or `sanitize_title()` |
| SQL values               | Use `$wpdb->prepare()`                 |

```php
// ✅ Correct
$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
$user_id = absint( $_POST['user_id'] ?? 0 );
$url     = esc_url_raw( wp_unslash( $_POST['redirect_url'] ?? '' ) );
$content = wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) );

// ❌ Incorrect — raw superglobal usage
$name = $_POST['name'];
update_option( 'my_option', $_POST['value'] );
```

Always call `wp_unslash()` before sanitizing `$_POST`, `$_GET`, `$_REQUEST`, `$_COOKIE` data.

### 2.4 Escaping Output

Escape data as **late as possible** — immediately before rendering. Use the most context-specific escaping function.

| Context                         | Escaping Function              |
| ------------------------------- | ------------------------------ |
| HTML text nodes                 | `esc_html()`                   |
| HTML attributes                 | `esc_attr()`                   |
| URLs in `href`, `src`, `action` | `esc_url()`                    |
| JavaScript values               | `esc_js()`                     |
| CSS values                      | `esc_attr()`                   |
| Translated strings (HTML)       | `esc_html__()`, `esc_html_e()` |
| Translated strings (attr)       | `esc_attr__()`, `esc_attr_e()` |
| Inline JSON for JS              | `wp_json_encode()`             |
| Rich HTML content               | `wp_kses_post()`               |

```php
// ✅ Correct
echo esc_html( $title );
echo '<input value="' . esc_attr( $value ) . '">';
echo '<a href="' . esc_url( $link ) . '">';
echo esc_html__( 'Settings saved.', 'myplugin' );

// In templates:
?>
<h2><?php echo esc_html( $section_title ); ?></h2>
<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
    <?php echo esc_html( $post->post_title ); ?>
</a>
<?php

// ❌ Incorrect — XSS risk
echo $title;
echo '<a href="' . $link . '">';
```

### 2.5 Data Validation (Rejection vs Sanitization)

Validation is stricter than sanitization — **reject** invalid data rather than silently correcting it.

```php
// ✅ Validate then reject
$status = sanitize_key( wp_unslash( $_POST['status'] ?? '' ) );
$allowed_statuses = array( 'publish', 'draft', 'pending' );

if ( ! in_array( $status, $allowed_statuses, true ) ) {
    wp_send_json_error( array( 'message' => __( 'Invalid status.', 'myplugin' ) ) );
    return;
}

// ✅ Validate email before saving
$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
if ( ! is_email( $email ) ) {
    // reject
}

// ✅ Validate integer range
$quantity = absint( $_POST['quantity'] ?? 0 );
if ( $quantity < 1 || $quantity > 100 ) {
    // reject
}
```

### 2.6 Direct File Access Prevention

Every plugin file must include a check to prevent direct access:

```php
// ✅ At the top of every PHP file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

### 2.7 Options & Transients Security

- Use `sanitize_*` functions before saving to options.
- Use `esc_*` functions when reading from options for output.
- Always prefix option names with plugin slug.

```php
// ✅ Saving
update_option( 'myplugin_api_key', sanitize_text_field( $api_key ) );

// ✅ Reading for output
echo esc_html( get_option( 'myplugin_api_key', '' ) );
```

### 2.8 HTTP Requests (wp*remote*\*)

Always use WordPress HTTP API — never raw `curl` or `file_get_contents`.

```php
// ✅ Correct
$response = wp_remote_get( esc_url_raw( $endpoint_url ), array(
    'timeout' => 15,
    'headers' => array(
        'Accept' => 'application/json',
    ),
) );

if ( is_wp_error( $response ) ) {
    // Handle error gracefully
    return;
}

$body = wp_remote_retrieve_body( $response );
$data = json_decode( $body, true );
```

### 2.9 Preventing Common Vulnerabilities

**SQL Injection** — always use `$wpdb->prepare()` (covered in 1.8).

**XSS (Cross-Site Scripting)** — always escape output (covered in 2.4).

**CSRF (Cross-Site Request Forgery)** — always use nonces (covered in 2.1).

**Path Traversal** — validate and sanitize file paths:

```php
// ✅ Correct
$file = sanitize_file_name( wp_unslash( $_GET['file'] ?? '' ) );
$full_path = realpath( WP_CONTENT_DIR . '/uploads/' . $file );
if ( false === $full_path || 0 !== strpos( $full_path, realpath( WP_CONTENT_DIR . '/uploads/' ) ) ) {
    wp_die( 'Invalid file path.' );
}
```

**Object Injection / Unsafe Unserialize** — never unserialize user data:

```php
// ❌ NEVER do this
$data = unserialize( $_POST['data'] );

// ✅ Use JSON instead
$data = json_decode( wp_unslash( $_POST['data'] ), true );
```

**Open Redirects** — always validate redirect URLs:

```php
// ✅ Correct
$redirect = wp_validate_redirect( wp_unslash( $_GET['redirect_to'] ?? '' ), admin_url() );
wp_safe_redirect( $redirect );
exit;

// ❌ Incorrect
wp_redirect( $_GET['redirect_to'] );
```

---

## PART 3 — JAVASCRIPT CODING STANDARDS

- Use `===` and `!==` (strict equality) — never `==` or `!=`.
- Declare variables with `const` or `let` — never `var`.
- Always use semicolons.
- Use single quotes for strings unless the string contains a single quote.
- Indent with tabs (matching PHP convention for consistency in WP context).
- All AJAX requests must include a nonce:

```javascript
// ✅ Correct AJAX with nonce
jQuery.post(
  myPlugin.ajaxUrl,
  {
    action: 'myplugin_save',
    nonce: myPlugin.nonce,
    data: inputValue,
  },
  function (response) {
    if (response.success) {
      // handle success
    }
  },
);
```

- Localize scripts with `wp_localize_script()` to pass PHP data to JS — never hardcode URLs or nonces.

```php
// ✅ Correct PHP side
wp_localize_script( 'myplugin-script', 'myPlugin', array(
    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
    'nonce'   => wp_create_nonce( 'myplugin_ajax' ),
) );
```

---

## PART 4 — CSS CODING STANDARDS

- Use lowercase and hyphens for selectors: `.my-plugin-wrapper`.
- One selector per line in multi-selector rules.
- Opening brace on same line as selector, closing brace on its own line.
- One property per line with a colon + space.
- Prefix all plugin-specific selectors to avoid conflicts: `.myplugin-*`.

```css
/* ✅ Correct */
.myplugin-container,
.myplugin-wrapper {
  display: flex;
  align-items: center;
}

/* ❌ Incorrect */
.container,
.wrapper {
  display: flex;
  align-items: center;
}
```

---

## PART 5 — INLINE DOCUMENTATION STANDARDS

All functions, classes, methods, and hooks must have PHPDoc blocks.

```php
/**
 * Retrieves posts for a given author.
 *
 * @since 1.0.0
 *
 * @param int    $author_id  The author's user ID.
 * @param string $post_type  Optional. Post type slug. Default 'post'.
 * @return WP_Post[]|false Array of post objects on success, false on failure.
 */
function myplugin_get_author_posts( int $author_id, string $post_type = 'post' ) {
    // ...
}

/**
 * Fires after plugin settings are saved.
 *
 * @since 1.0.0
 *
 * @param int $user_id ID of the user who saved settings.
 */
do_action( 'myplugin_settings_saved', $user_id );
```

---

## PART 6 — PLUGIN STRUCTURE BEST PRACTICES

```
my-plugin/
├── my-plugin.php              # Main plugin file with header
├── includes/
│   ├── class-my-plugin.php    # Core plugin class
│   ├── class-admin.php        # Admin-specific logic
│   └── class-public.php       # Front-end logic
├── admin/
│   ├── css/
│   ├── js/
│   └── partials/
├── public/
│   ├── css/
│   ├── js/
│   └── partials/
└── languages/                 # .pot/.po/.mo files
```

- Main plugin file must contain the plugin header comment.
- Always check `ABSPATH` at the top of every file.
- Use `plugin_dir_path( __FILE__ )` and `plugin_dir_url( __FILE__ )` for paths.
- Enqueue scripts and styles only with `wp_enqueue_scripts` / `admin_enqueue_scripts` hooks — never hardcode `<script>` tags.

```php
// ✅ Correct script enqueue
add_action( 'wp_enqueue_scripts', 'myplugin_enqueue_scripts' );
function myplugin_enqueue_scripts() {
    wp_enqueue_style(
        'myplugin-styles',
        plugin_dir_url( __FILE__ ) . 'public/css/myplugin.css',
        array(),
        MYPLUGIN_VERSION
    );

    wp_enqueue_script(
        'myplugin-script',
        plugin_dir_url( __FILE__ ) . 'public/js/myplugin.js',
        array( 'jquery' ),
        MYPLUGIN_VERSION,
        true // Load in footer
    );
}
```

---

## PART 7 — AUTOMATED TOOLING

Install and run these tools to enforce standards automatically:

```bash
# Install PHP_CodeSniffer + WordPress Coding Standards
composer require --dev squizlabs/php_codesniffer wp-coding-standards/wpcs
./vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs

# Run PHPCS against your plugin
./vendor/bin/phpcs --standard=WordPress path/to/my-plugin/

# Auto-fix fixable issues
./vendor/bin/phpcbf --standard=WordPress path/to/my-plugin/
```

Recommended `.phpcs.xml.dist` configuration:

```xml
<?xml version="1.0"?>
<ruleset name="My Plugin">
    <description>WordPress Coding Standards for My Plugin</description>

    <file>.</file>
    <exclude-pattern>/vendor/*</exclude-pattern>
    <exclude-pattern>/node_modules/*</exclude-pattern>

    <arg name="basepath" value="."/>
    <arg name="extensions" value="php"/>
    <arg name="parallel" value="8"/>
    <arg value="sp"/>

    <rule ref="WordPress"/>
    <rule ref="WordPress-Extra"/>
    <rule ref="WordPress-Docs"/>

    <config name="minimum_supported_wp_version" value="6.0"/>
    <config name="testVersion" value="7.4-"/>
</ruleset>
```

---

## QUICK REFERENCE CHECKLIST

Before submitting or merging WordPress code, verify:

- [ ] All output escaped with appropriate `esc_*()` function
- [ ] All input sanitized with appropriate `sanitize_*()` function, after `wp_unslash()`
- [ ] All forms and AJAX requests protected with nonces (`wp_nonce_field` / `check_ajax_referer`)
- [ ] All privileged actions check `current_user_can()`
- [ ] All database queries use `$wpdb->prepare()` or WP API functions
- [ ] No `eval()`, `extract()`, `create_function()`, or backtick operators used
- [ ] No raw `$_POST`/`$_GET`/`$_REQUEST` used without sanitization
- [ ] No direct `unserialize()` of user-supplied data
- [ ] `if ( ! defined( 'ABSPATH' ) ) { exit; }` at top of every PHP file
- [ ] Functions/hooks/options prefixed with plugin slug
- [ ] Long array syntax `array()` used throughout
- [ ] Yoda conditions used for `==`, `!=`, `===`, `!==`
- [ ] `real tabs` used for indentation (not spaces)
- [ ] `elseif` used (not `else if`)
- [ ] All functions/classes/hooks have PHPDoc blocks
- [ ] PHPCS with WordPress standard passes with zero errors
