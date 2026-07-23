# Tutor LMS Cart Button Integration Guide

This guide explains how to integrate the Tutor LMS native ecommerce cart button into any WordPress theme, similar to WooCommerce's cart button integration.

## Overview

The Tutor LMS plugin now provides a global cart button function that can be used by any theme to display the shopping cart button in the header or any other location. This works with Tutor's native ecommerce system (when monetization is set to 'tutor').

## Available Functions

### 1. `tutor_ecommerce_cart_button( $args = array() )`

Displays the cart button with icon and item count.

**Parameters:**
- `$args` (array) - Optional arguments to customize the cart button:
  - `class` (string) - CSS class for the cart button. Default: 'cart-contents'
  - `title` (string) - Title attribute for the cart link. Default: 'View your shopping cart'
  - `show_icon` (bool) - Whether to show the cart icon. Default: true
  - `show_count` (bool) - Whether to show the cart item count. Default: true
  - `icon_svg` (string) - Custom SVG icon. If not provided, default cart icon will be used
  - `before_count` (string) - Text before cart count. Default: '('
  - `after_count` (string) - Text after cart count. Default: ')'

**Example Usage:**

```php
// Basic usage - default cart button
<?php tutor_ecommerce_cart_button(); ?>

// Custom CSS class
<?php tutor_ecommerce_cart_button( array( 'class' => 'my-theme-cart' ) ); ?>

// Hide count, show only icon
<?php tutor_ecommerce_cart_button( array( 'show_count' => false ) ); ?>

// Custom icon and styling
<?php 
tutor_ecommerce_cart_button( array(
    'class' => 'header-cart-btn',
    'title' => 'View Cart',
    'before_count' => '',
    'after_count' => ' items',
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
- `show_count` - "true" or "false"
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

**Features:**
- Automatically detects if Tutor native ecommerce is enabled
- Shows cart icon and item count
- Links to the cart page
- Works in any block template (header, footer, etc.)

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

For block themes, the `wp_nav_menu_items` filter does NOT work because block themes use the Navigation block instead of `wp_nav_menu()`.

**Important Limitation:** WordPress Navigation blocks have a strict whitelist of allowed blocks. Neither custom blocks (like Tutor Cart Button) nor the Shortcode block can be added directly inside Navigation blocks. This is a WordPress core restriction.

#### Recommended Approach for Block Themes

Place the **Tutor Cart Button Gutenberg block** in your header template (outside the Navigation block):

1. Go to Appearance → Editor (Site Editor)
2. Edit your Header template
3. Add the "Tutor Cart Button" block in the header area, alongside or near your Navigation block
4. The block will display the cart icon and count

This is the standard approach used by WooCommerce and other plugins - the cart button is placed in the header template, not inside the Navigation block itself.

#### Alternative: Custom Template Part with PHP

For more control, create a custom template part:

1. Go to Appearance → Editor (Site Editor)
2. Create a new template part (e.g., "Header Cart")
3. Add a "Custom HTML" block with the shortcode: `[tutor_cart_button]`
4. Insert this template part in your header

#### Alternative: Theme Customization

If you need the cart inside the navigation area and have access to theme files:

1. Create a child theme
2. Override the header template
3. Use the PHP function: `<?php tutor_ecommerce_cart_button(); ?>`

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
            'show_count' => true,
            'before_count' => '<span class="cart-count">',
            'after_count' => '</span>',
        ) ); 
        ?>
    </div>
<?php endif; ?>
```

### Example 4: Replacing the Current Starter Theme Implementation

The current implementation in tutorstarter theme can be simplified:

**Before (Current):**
```php
if ( class_exists( 'Tutor\Ecommerce\CartController' ) && 'tutor' === tutor_utils()->get_option( 'monetize_by' ) && 'header_fullwidth_center' !== get_theme_mod( 'header_type_select' ) ) {
    $tutor_native_cart_controller = new CartController();
    if ( true === get_theme_mod( 'cart_btn_toggle', true ) ) {
        $items = $tutor_native_cart_controller->get_cart_items()['courses'];
        ?>
        <a class="cart-contents" href="<?php echo esc_url( $tutor_native_cart_controller->get_page_url() ); ?>"
            title="<?php esc_attr_e( 'View your shopping cart', 'tutorstarter' ); ?>">
            <span class="btn-cart">
                <svg>...</svg>
                <span class="tutor_native_cart_count"> 
                <?php
                if ( $items && $items['total_count'] ) {
                    echo esc_html( $tutor_native_cart_controller->get_cart_items()['courses']['total_count'] );
                } ?>
                </span>
            </span>
        </a>
        <?php
    }
}
```

**After (Using Global Function):**
```php
if ( 'header_fullwidth_center' !== get_theme_mod( 'header_type_select' ) && true === get_theme_mod( 'cart_btn_toggle', true ) ) {
    tutor_ecommerce_cart_button();
}
```

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

The cart button uses the following CSS classes by default:
- `.cart-contents` - Main cart link
- `.btn-cart` - Icon container
- `.tutor_cart_count` - Count badge

You can override these using the `class` parameter or custom CSS.

## Compatibility

- Works only when Tutor LMS native ecommerce is active
- Requires monetization setting to be set to 'tutor'
- Automatically returns nothing if conditions are not met
- Compatible with any WordPress theme

## Notes

- The function automatically checks if Tutor native ecommerce is enabled
- If WooCommerce is used for monetization, this function will not display
- The cart count only shows when there are items in the cart (configurable via `show_count`)
- The default icon is the same as used in the tutorstarter theme

## Support

For issues or questions, please refer to the Tutor LMS documentation or contact Themeum support.
