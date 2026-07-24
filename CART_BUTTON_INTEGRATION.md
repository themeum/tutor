# Cart Button

Tutor LMS provides a cart button for its native ecommerce system that can be integrated into both classic and block themes.

The cart button is available only when **Tutor LMS native ecommerce** is the active monetization method.

## PHP Function

Use the following function to display the cart button:

```php
<?php tutor_ecommerce_cart_button(); ?>
```

### Parameters

| Parameter      | Type     | Default                   | Description                                                                  |
| -------------- | -------- | ------------------------- | ---------------------------------------------------------------------------- |
| `class`        | `string` | `tutor-cart-button`       | Custom CSS class.                                                            |
| `title`        | `string` | `View your shopping cart` | Title attribute for the cart link.                                           |
| `show_icon`    | `bool`   | `true`                    | Whether to display the cart icon.                                            |
| `show_count`   | `string` | `if_has_items`            | Display the cart count. Supported values: `always`, `if_has_items`, `never`. |
| `icon_svg`     | `string` | Default icon              | Custom SVG icon.                                                             |
| `before_count` | `string` | `''`                      | Content displayed before the count.                                          |
| `after_count`  | `string` | `''`                      | Content displayed after the count.                                           |

### Examples

Display the default cart button:

```php
<?php tutor_ecommerce_cart_button(); ?>
```

Hide the cart count:

```php
<?php
tutor_ecommerce_cart_button(
	array(
		'show_count' => 'never',
	)
);
?>
```

Always display the cart count:

```php
<?php
tutor_ecommerce_cart_button(
	array(
		'show_count' => 'always',
	)
);
?>
```

## Cart Count

Retrieve the current cart item count:

```php
$count = tutor_ecommerce_get_cart_count();
```

## Shortcode

Display the cart button using:

```text
[tutor_cart_button]
```

Supported attributes:

* `class`
* `title`
* `show_icon`
* `show_count`
* `before_count`
* `after_count`

Example:

```text
[tutor_cart_button class="header-cart" show_count="never"]
```

## Gutenberg Block

Tutor LMS includes a dynamic **Tutor Cart Button** block for block themes.

### Block Name

```text
tutor-gutenberg/cart-button
```

The block can be inserted into any template or template part using the Site Editor.

> **Note:** WordPress Navigation blocks only allow navigation-related child blocks. The Tutor Cart Button cannot be inserted directly inside a Navigation block. Instead, place it alongside the Navigation block within the header layout.

## Theme Integration

Tutor LMS does not automatically insert the cart button into a theme. Theme developers decide where it should appear.

### Classic Themes

Render the cart button anywhere in your theme:

```php
<?php
if ( function_exists( 'tutor_ecommerce_cart_button' ) ) {
	tutor_ecommerce_cart_button();
}
?>
```

If desired, themes may also append the cart button to a navigation menu using the `wp_nav_menu_items` filter or render it through their own header hooks.

### Block Themes

Add the **Tutor Cart Button** block to your header template using the Site Editor.

## Filters

Customize the default arguments:

```php
add_filter( 'tutor_ecommerce_cart_button_args', function( $args ) {
	$args['class'] = 'my-cart-button';

	return $args;
} );
```

## Compatibility

* Available only when Tutor LMS native ecommerce is enabled.
* Automatically outputs nothing when another monetization method is active.
* Compatible with both classic and block themes.
