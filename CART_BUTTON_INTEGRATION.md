# Tutor LMS Cart Button Integration Guide

This guide explains how to integrate the Tutor LMS native ecommerce cart button into any WordPress theme, similar to WooCommerce's cart button integration.

## Overview

The Tutor LMS plugin provides multiple ways to display the shopping cart button for Tutor's native ecommerce system (when monetization is set to 'tutor'):

1. **PHP Function** - For classic themes and direct theme integration
2. **Shortcode** - For theme developers who prefer shortcode-based integration
3. **Gutenberg Block** - For block-based themes (FSE) and site editor integration

## Available Functions

### 1. `tutor_ecommerce_cart_button( $args = array() )`

Displays the cart button with icon and item count.

**Parameters:**
- `$args` (array) - Optional arguments to customize the cart button:
  - `class` (string) - CSS class for the cart button. Default: 'tutor-cart-button'
  - `title` (string) - Title attribute for the cart link. Default: 'View your shopping cart'
  - `show_icon` (bool) - Whether to show the cart icon. Default: true
  - `show_count` (string) - When to show the cart item count: 'always', 'if_has_items', or 'never'. Default: 'if_has_items'
  - `icon_svg` (string) - Custom SVG icon. If not provided, default cart icon will be used
  - `before_count` (string) - Text before cart count. Default: ''
  - `after_count` (string) - Text after cart count. Default: ''

**Example Usage:**

```php
// Basic usage - default cart button
<?php tutor_ecommerce_cart_button(); ?>

// Custom CSS class
<?php tutor_ecommerce_cart_button( array( 'class' => 'my-theme-cart' ) ); ?>

// Hide count, show only icon
<?php tutor_ecommerce_cart_button( array( 'show_count' => 'never' ) ); ?>

// Always show count even when empty
<?php tutor_ecommerce_cart_button( array( 'show_count' => 'always' ) ); ?>

// Custom icon and styling
<?php 
tutor_ecommerce_cart_button( array(
    'class' => 'header-cart-btn',
    'title' => 'View Cart',
    'before_count' => '<span class="cart-count">',
    'after_count' => '</span>',
) ); 
?>
```

### 2. `tutor_ecommerce_get_cart_count()`

Returns the current cart item count as an integer.

**Example Usage:**

```php
<?php 
$count = tutor_ecommerce_get_cart_count();
echo "You have $count items in your cart";
?>
```

## Shortcode Integration

For themes that prefer shortcode integration, use:

```
[tutor_cart_button]
```

**Shortcode Attributes:**
- `class` - CSS class for the cart button
- `title` - Title attribute
- `show_icon` - "true" or "false"
- `show_count` - "true", "false", or "if_has_items"
- `before_count` - Text before count
- `after_count` - Text after count

**Example:**
```
[tutor_cart_button class="my-cart" show_count="false"]
```

## Block Theme Integration (Gutenberg)

For block-based themes (FSE - Full Site Editing), Tutor LMS provides a dedicated Gutenberg block:

### Tutor Cart Button Block

**Block Name:** `tutor-gutenberg/cart-button`
**Category:** Tutor
**Icon:** Cart

**How to Use:**
1. In the WordPress block editor, search for "Tutor Cart Button"
2. Add the block to your header template or any other template
3. The block will automatically render the cart button with icon and count

**Block Settings:**
- **Show Icon** - Toggle to show/hide the cart icon
- **Show Count** - Toggle to show/hide the cart item count
- **Custom CSS Class** - Add custom CSS class for styling

**Block Style Options:**
- **Icon Color** - Color picker for the cart icon
- **Count Background Color** - Color picker for the badge background
- **Count Text Color** - Color picker for the badge text

**Features:**
- Automatically detects if Tutor native ecommerce is enabled
- Shows cart icon and item count
- Links to the cart page
- Works in any block template (header, footer, etc.)
- Server-side rendering for optimal performance
- Dynamic cart count updates via JavaScript

**Note:** The block uses dynamic rendering and will display the cart button only when Tutor native ecommerce is active.

**Important:** This block cannot be added directly to navigation menus. For navigation menu integration, see the section below.

## Navigation Menu Integration

### Classic Themes (PHP-based)

For classic themes using `wp_nav_menu()`, you can manually add the cart button to your navigation menus by editing your theme's header template or using the `wp_nav_menu_items` filter in your theme.

**Example - Adding to specific menu location via theme:**

```php
// In your theme's functions.php
add_filter( 'wp_nav_menu_items', function( $items, $args ) {
    if ( 'primary' === $args->theme_location && function_exists( 'tutor_ecommerce_cart_button' ) ) {
        ob_start();
        tutor_ecommerce_cart_button();
        $cart_button = ob_get_clean();
        $items .= '<li class="menu-item tutor-cart-menu-item">' . $cart_button . '</li>';
    }
    return $items;
}, 10, 2 );
```

This approach gives themes full control over where the cart appears, similar to WooCommerce's philosophy.

### Block Themes (FSE - Full Site Editing)

For block themes, use the **Tutor Cart Button Gutenberg block** in your header template:

1. Go to Appearance → Editor (Site Editor)
2. Edit your Header template
3. Add the "Tutor Cart Button" block in the header area, alongside or near your Navigation block
4. The block will display the cart icon and count

**Alternative Methods:**

- **Shortcode in Custom HTML:** Add a Custom HTML block with `[tutor_cart_button]` in a template part
- **PHP in Theme Files:** Override the header template in a child theme and use `<?php tutor_ecommerce_cart_button(); ?>`

**Note:** WordPress Navigation blocks have a strict whitelist of allowed blocks, so the cart button cannot be added directly inside Navigation blocks. This is a WordPress core restriction. The standard approach (used by WooCommerce and other plugins) is to place the cart button in the header template, outside the Navigation block.

## Theme Integration Examples

### Example 1: Simple Header Integration

```php
// In your theme's header.php file
<?php if ( function_exists( 'tutor_ecommerce_cart_button' ) ) : ?>
    <div class="header-cart">
        <?php tutor_ecommerce_cart_button(); ?>
    </div>
<?php endif; ?>
```

### Example 2: Conditional Display (Like WooCommerce)

```php
// Only show if Tutor native ecommerce is active
<?php 
if ( function_exists( 'tutor_utils' ) && tutor_utils()->is_monetize_by_tutor() ) {
    tutor_ecommerce_cart_button();
}
?>
```

### Example 3: Custom Styling Integration

```php
// In your theme's header with custom wrapper
<?php if ( function_exists( 'tutor_ecommerce_cart_button' ) ) : ?>
    <div class="theme-header-cart">
        <?php 
        tutor_ecommerce_cart_button( array(
            'class' => 'theme-cart-link',
            'show_count' => 'if_has_items',
            'before_count' => '<span class="cart-count">',
            'after_count' => '</span>',
        ) ); 
        ?>
    </div>
<?php endif; ?>
```

## Dynamic Cart Count Updates

The cart button includes JavaScript functionality for dynamic cart count updates. When items are added or removed from the cart, the count automatically updates across all cart button instances on the page.

**JavaScript Events:**
- `tutorAddToCartEvent` - Dispatched when an item is added to cart
- `tutorRemoveCartEvent` - Dispatched when an item is removed from cart

**CSS Classes for JavaScript:**
- `.tutor-cart-count` - Targeted by JavaScript for count updates
- `data-show-count` - Attribute to control when count is displayed ('always', 'if_has_items', 'never')

## Filters

The function supports a filter for customization:

```php
add_filter( 'tutor_ecommerce_cart_button_args', 'my_custom_cart_args' );

function my_custom_cart_args( $args ) {
    $args['class'] = 'my-custom-class';
    $args['title'] = 'My Custom Title';
    return $args;
}
```

## CSS Classes

The cart button uses the following CSS classes (same for both PHP function and Gutenberg block):
- `.tutor-cart-button` - Main cart link/wrapper (default class)
- `.tutor-btn-cart` - Icon container
- `.tutor-cart-count` - Count badge

**Note:** The Gutenberg block wraps the cart button in a div with class `.tutor-cart-button` (from `get_block_wrapper_attributes`). The inner cart button uses the same classes as the PHP function.

You can override the main class using the `class` parameter (PHP function) or Custom CSS Class setting (Gutenberg block).

## File Structure

**Gutenberg Block:**
- `assets/src/js/gutenberg/cart-button/block.json` - Block metadata
- `assets/src/js/gutenberg/cart-button/index.js` - Block registration
- `assets/src/js/gutenberg/cart-button/edit.js` - React edit component
- `assets/src/js/gutenberg/cart-button/save.js` - Save component (returns null for dynamic rendering)
- `assets/src/js/gutenberg/cart-button/render.php` - Server-side render callback
- `assets/src/js/gutenberg/cart-button/style.scss` - Block styles

**Frontend JavaScript:**
- `assets/src/js/front/tutor-cart-button.js` - Cart count update listeners
- `assets/src/js/front/tutor-front.js` - Imports cart-button.js

**PHP Functions**:**
- `includes/ecommerce-functions.php` - `tutor_ecommerce_cart_button()` and `tutor_ecommerce_get_cart_count()`
- `classes/Shortcode.php` - `[tutor_cart_button]` shortcode handler
- `classes/Gutenberg.php` - Block registration

**Build Configuration:**
- `rspack.config.mjs` - Build entries for `tutor-gutenberg-cart-button` (JS and SCSS)

## Compatibility

- Works only when Tutor LMS native ecommerce is active
- Requires monetization setting to be set to 'tutor'
- Automatically returns nothing if conditions are not met
- Compatible with any WordPress theme
- Supports both classic and block themes

## Notes

- The function automatically checks if Tutor native ecommerce is enabled
- If WooCommerce is used for monetization, this function will not display
- The cart count display behavior is configurable via `show_count` parameter
- The default icon uses Tutor's built-in SVG icon system
- The Gutenberg block uses modern WordPress block API (block.json, ES6 modules, React/JSX)
- Cart count updates are handled via custom JavaScript events for real-time updates

## Support

For issues or questions, please refer to the Tutor LMS documentation or contact Themeum support.
