# Skill: WordPress Plugin Directory Structure and Coding Pattern

## Purpose
This skill defines:

- Where new files should be placed.
- How to organize plugin features.
- Which PHP design patterns to use.
- How to develop new functionality consistently.
- How to keep code modular, testable, and WordPress-compatible.

---

# Standard Plugin Structure

plugin-name/
│
├── plugin-name.php                # Main bootstrap file
├── uninstall.php                  # Cleanup on uninstall
├── readme.txt
├── composer.json                  # Optional if autoloading
│
├── inc/                           # Core PHP code
│   │
│   ├── Core/
│   │   ├── Loader.php
│   │   ├── Hooks.php
│   │   ├── ServiceProvider.php
│   │   └── Container.php
│   │
│   ├── Features/
│   │   ├── FeatureOne/
│   │   │   ├── FeatureOne.php
│   │   │   ├── Controller.php
│   │   │   ├── Repository.php
│   │   │   ├── AjaxHandler.php
│   │   │   └── Validator.php
│   │   │
│   │   └── FeatureTwo/
│   │       ├── FeatureTwo.php
│   │       ├── Controller.php
│   │       └── Repository.php
│   │
│   ├── Integrations/
│   │   ├── WooCommerce/
│   │   └── Elementor/
│   │
│   └── Helpers/
│       └── functions.php
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── src/                      # Optional source files (scss/js)
│
├── templates/                    # Frontend templates
│   └── course-card.php
│
├── views/                        # Admin views
│   └── settings-page.php
│
├── languages/                    # Translation files
│
├── tests/
│   ├── Unit/
│   └── Integration/
│
└── vendor/                       # Composer dependencies


## PART 1 — FILE PLACEMENT RULES

### 1.1 New Feature

Always create under:

```
inc/Features/FeatureName/
```

A feature folder must contain only the files it actually needs:

| File | Purpose |
|---|---|
| `Feature.php` | Feature bootstrap |
| `Controller.php` | Request handling |
| `Repository.php` | Data access (only if data handling exists) |
| `Validator.php` | Input validation (only if input exists) |
| `AjaxHandler.php` | AJAX logic (only if AJAX exists) |

**Example:**

```
inc/Features/Enrollment/
├── Feature.php
├── Controller.php
├── Repository.php
├── Validator.php
└── AjaxHandler.php
```

---

### 1.2 Frontend UI

Place in:

```
templates/
```

**Allowed:**
- HTML markup
- Escaped output
- Minimal conditionals

**Not allowed:**
- Database queries
- Feature/business logic
- Hook registration

---

### 1.3 Admin UI

Place in:

```
views/
```

Same rules as templates — no business logic.

---

### 1.4 CSS / JS Assets

Place in:

```
assets/css/
assets/js/
```

Feature-specific assets should follow the naming convention:

```
assets/js/feature-name.js
assets/css/feature-name.css
```

---

### 1.5 Tests

| Test Type | Location |
|---|---|
| Unit tests | `tests/Unit/` |
| Integration tests | `tests/Integration/` |

---

## PART 2 — CODING PATTERN RULES

### 2.1 Use Feature-Based Modular Architecture

Each feature must be fully isolated from other features.

```
// ❌ Bad — all logic dumped in one place
inc/functions.php

// ✅ Good — isolated feature folder
inc/Features/Certificates/
```

---

### 2.2 Required PHP Design Patterns

#### Pattern 1: Singleton

**Use for:**
- Main plugin bootstrap
- Service container
- Hook loader

```php
// ✅ Example
Plugin::instance();
```

---

#### Pattern 2: Factory

**Use for:**
- Dynamic object creation
- Gateway creation
- Quiz types
- Payment handlers

```php
// ✅ Example
GatewayFactory::make( 'paypal' );
```

---

#### Pattern 3: Strategy

**Use for interchangeable behavior:**
- Payment methods
- Validation engines
- Export formats
- Quiz grading logic

```php
// ✅ Example
$processor->setStrategy( new StripePayment() );
```

---

#### Pattern 4: Repository

**Use for:**
- Database access
- `WP_Query` abstraction
- Custom table access

Never write raw queries inside controllers.

```php
// ✅ Use
EnrollmentRepository::get_by_user( $user_id );

// ❌ Never
global $wpdb;
$wpdb->get_results( "SELECT ..." ); // inside a controller
```

---

#### Pattern 5: Observer (WordPress Hooks)

Use WordPress actions and filters as the Observer pattern. Prefer hooks over tightly coupled code.

```php
// ✅ Example
add_action( 'myplugin_enrollment_complete', array( $this, 'send_confirmation' ) );
add_filter( 'myplugin_certificate_data', array( $this, 'append_metadata' ) );
```

---

#### Pattern 6: Dependency Injection

Use constructor injection. Never rely on globals inside classes.

```php
// ✅ Good — constructor injection
new EnrollmentController( new EnrollmentRepository() );

// ❌ Bad — global dependency
class EnrollmentController {
    public function __construct() {
        global $wpdb; // avoid this
    }
}
```

---

## PART 3 — FEATURE DEVELOPMENT WORKFLOW

Follow these steps in order when building any new feature:

**Step 1 — Create the feature folder:**

```
inc/Features/NewFeature/
```

**Step 2 — Choose the right design pattern:**

| Situation | Pattern |
|---|---|
| Multiple interchangeable behaviors | Strategy |
| Dynamic object creation | Factory |
| Database access involved | Repository |
| Shared plugin-wide service | Singleton + DI |
| Integrating with WordPress events | Observer (hooks) |

**Step 3 — Create only the files actually needed:**

```
Feature.php
Controller.php
Repository.php    ← only if DB involved
Validator.php     ← only if input exists
AjaxHandler.php   ← only if AJAX exists
```

**Step 4 — Register the feature in:**

```
Core/ServiceProvider.php
```

**Step 5 — Load hooks in:**

```
Hooks.php
```

**Step 6 — Add tests:**

```
tests/Unit/Features/NewFeature/
tests/Integration/Features/NewFeature/
```

---

## PART 4 — WORDPRESS CODING RULES

All code must follow [WordPress Coding Standards (WPCS)](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).

### 4.1 Prefix Everything

All functions, classes, hooks, and constants must be prefixed with the plugin slug:

```php
// ✅ Correct
function myplugin_get_enrollment( $user_id ) {}
define( 'MYPLUGIN_VERSION', '1.0.0' );
do_action( 'myplugin_enrollment_complete', $enrollment_id );

// ❌ Incorrect
function get_enrollment( $user_id ) {}
define( 'VERSION', '1.0.0' );
```

### 4.2 Escape Output

Always escape immediately before rendering:

```php
echo esc_html( $title );
echo esc_attr( $value );
echo wp_kses_post( $content );
```

### 4.3 Sanitize Input

Always sanitize after `wp_unslash()` before saving or using:

```php
$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
$user_id = absint( $_POST['user_id'] ?? 0 );
```

### 4.4 Verify Nonces

Every form and AJAX request must verify a nonce:

```php
// Form
wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'myplugin_action' );

// AJAX
check_ajax_referer( 'myplugin_ajax_action', 'nonce' );
```

---

## PART 5 — NAMING RULES

| Construct | Convention | Example |
|---|---|---|
| Classes | `FeatureNameController` | `EnrollmentController` |
| Repositories | `FeatureNameRepository` | `EnrollmentRepository` |
| Validators | `FeatureNameValidator` | `EnrollmentValidator` |
| Files | `class-feature-name-controller.php` | `class-enrollment-controller.php` |
| Hooks | `pluginslug_feature_action` | `myplugin_enrollment_complete` |

---

## PART 6 — WHAT NOT TO DO

| ❌ Never Do This | ✅ Do This Instead |
|---|---|
| Put all code in the main plugin file | Isolate into `inc/Features/` |
| Write SQL inside templates | Use a Repository class |
| Put feature logic inside views | Keep views to output only |
| Build giant utility/helper classes | Keep logic inside feature classes |
| Use `static` methods everywhere | Use Dependency Injection |
| Create feature code outside `inc/Features/` | Always follow the feature folder structure |

---

## PART 7 — MAIN PLUGIN FILE RESPONSIBILITY

`plugin-name.php` must only:

- Define constants
- Load the autoloader
- Boot the plugin

```php
// ✅ Correct — main plugin file
<?php
/**
 * Plugin Name: My Plugin
 * Version:     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MYPLUGIN_VERSION', '1.0.0' );
define( 'MYPLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'MYPLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once MYPLUGIN_PATH . 'vendor/autoload.php';

Plugin::instance()->boot();
```

No feature logic, no hooks, no database calls here.

---

## PART 9 — AI MODEL INSTRUCTIONS

When adding or modifying any feature, follow these rules strictly:

1. Place all feature files inside `inc/Features/FeatureName/`.
2. Select the appropriate design pattern from Part 2 before writing any code.
3. Keep each feature fully isolated — no cross-feature direct calls.
4. Register the feature via `Core/ServiceProvider.php`.
5. Load all hooks through `Hooks.php` — never inline in class constructors.
6. Never place business logic inside `templates/` or `views/`.
7. Always add unit and integration tests for new features.
8. Follow existing feature structure before inventing new patterns.
9. Prefer extending the existing architecture over creating new abstractions.
10. The main plugin file boots only — no feature code ever goes there.

---

## QUICK REFERENCE CHECKLIST

Before submitting or merging any feature, verify:

- [ ] Feature files are inside `inc/Features/FeatureName/`
- [ ] Correct design pattern selected and applied
- [ ] Feature registered in `Core/ServiceProvider.php`
- [ ] Hooks loaded via `Hooks.php`
- [ ] No business logic in `templates/` or `views/`
- [ ] No raw `$wpdb` queries inside controllers
- [ ] Constructor injection used — no `global` inside classes
- [ ] All output escaped with `esc_*()` functions
- [ ] All input sanitized with `sanitize_*()` after `wp_unslash()`
- [ ] Nonces verified on all forms and AJAX requests
- [ ] All classes, hooks, and constants prefixed with plugin slug
- [ ] Unit tests added in `tests/Unit/`
- [ ] Integration tests added in `tests/Integration/`
- [ ] Main plugin file contains no feature logic