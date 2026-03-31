# Tutor Design System Core Library

A standalone design system library providing consistent UI components, themes, and utilities for modern web applications.

## Features

- 🎨 **Multi-theme Support**: Light and dark themes with CSS custom properties based on Figma design tokens
- 🌍 **RTL Language Support**: Built-in right-to-left language support with automatic adaptation
- 🧩 **Component Library**: Pre-built components with Alpine.js integration and TypeScript support
- 📱 **Responsive Design**: Mobile-first responsive utilities with smart defaults
- ⚡ **Performance Optimized**: Single CSS and JS files for easy distribution
- 🔧 **TypeScript Support**: Full TypeScript definitions with proper type safety (no `any` types)
- 🎯 **Figma Integration**: Design tokens directly mapped from Figma specifications
- 🔄 **RTL-Aware Utilities**: All utility classes automatically adapt to RTL layouts
- 🎨 **Complete Color System**: Brand, Gray, Success, Warning, Error, Yellow, and Exception color scales
- 📏 **Precise Spacing**: 22-step spacing scale (0-21) matching Figma specifications exactly

## Design Token System

This design system is built on a comprehensive token system that maps directly to Figma design specifications:

### Color Tokens

- **Brand Colors**: 10-step scale (100-950) for primary brand colors
- **Gray Colors**: 10-step scale (25-950) for neutral colors and surfaces
- **Semantic Colors**: Success, Warning, Error with 10-step scales each
- **Exception Colors**: Special colors for unique use cases (kids theme, highlights)
- **Yellow Colors**: 10-step scale for attention and highlight colors

### Typography Tokens

- **Headings**: H1 (40px) to H5 (16px) with corresponding line heights
- **Paragraphs**: P1 (16px), P2 (14px), P3 (12px) with optimal line heights
- **Font Weights**: Light (300) to Bold (700) with semantic naming

### Spacing Tokens

- **Scale**: 22 steps (0-21) from 0px to 200px
- **Semantic Names**: Based on usage patterns and visual hierarchy
- **RTL Support**: All spacing automatically adapts to text direction

### Border Radius Tokens

- **Scale**: From none (0px) to full (1000px)
- **Semantic Names**: Small, medium, large, and contextual variants

## Quick Start

### Installation

Include the compiled CSS and JavaScript files in your HTML:

```html
<!DOCTYPE html>
<html lang="en" dir="ltr" data-theme="light">
  <head>
    <!-- Include the design system CSS -->
    <link rel="stylesheet" href="assets/css/tutor-core.min.css" />
  </head>
  <body>
    <!-- Your content here -->

    <!-- Include Alpine.js and the design system JS -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="assets/js/tutor-core.min.js"></script>
  </body>
</html>
```

### Theme Switching

Switch themes by changing the `data-theme` attribute:

```javascript
// Switch to dark theme
document.documentElement.setAttribute('data-theme', 'dark');

// Switch to light theme
document.documentElement.setAttribute('data-theme', 'light');
```

### Font Scaling

The design system supports font scaling for accessibility. All typography uses `rem` units and scales proportionally:

```javascript
// Set font scale to 120% (larger text for better accessibility)
TutorCore.utils.setFontScale(120);

// Set font scale to 80% (smaller text)
TutorCore.utils.setFontScale(80);

// Reset to default size (100%)
TutorCore.utils.resetFontScale();

// Get current font scale percentage
const currentScale = TutorCore.utils.getFontScale(); // Returns 80, 90, 100, 110, or 120
```

You can also use CSS classes directly:

```html
<!-- Apply 120% font scaling to entire page -->
<html class="tutor-font-scale-120">
  <!-- Apply 80% font scaling to a specific section -->
  <div class="tutor-font-scale-80">
    <h2>This heading will be smaller</h2>
    <p>This paragraph will also be smaller</p>
  </div>
</html>
```

**Available font scales:** 80%, 90%, 100% (default), 110%, 120%

### RTL Support

Enable RTL layout by setting the `dir` attribute:

```html
<html lang="ar" dir="rtl" data-theme="light"></html>
```

## Components

### Buttons

```html
<!-- Primary button -->
<button class="tutor-btn tutor-btn--primary">Primary Button</button>

<!-- Secondary button -->
<button class="tutor-btn tutor-btn--secondary">Secondary Button</button>

<!-- Outline button -->
<button class="tutor-btn tutor-btn--outline">Outline Button</button>

<!-- Ghost button -->
<button class="tutor-btn tutor-btn--ghost">Ghost Button</button>

<!-- Button sizes -->
<button class="tutor-btn tutor-btn--primary tutor-btn--small">Small</button>
<button class="tutor-btn tutor-btn--primary tutor-btn--medium">Medium</button>
<button class="tutor-btn tutor-btn--primary tutor-btn--large">Large</button>
```

### Cards

```html
<div class="tutor-card">
  <div class="tutor-card__header">
    <h3 class="tutor-card__title">Card Title</h3>
    <p class="tutor-card__subtitle">Card subtitle</p>
  </div>
  <div class="tutor-card__body">
    <p>Card content goes here...</p>
  </div>
  <div class="tutor-card__footer">
    <button class="tutor-btn tutor-btn--primary">Action</button>
  </div>
</div>
```

### Forms

```html
<div class="tutor-form-group">
  <label class="tutor-label" for="email">Email</label>
  <input type="email" id="email" class="tutor-input" placeholder="Enter your email" />
  <p class="tutor-help-text">We'll never share your email.</p>
</div>

<div class="tutor-form-group">
  <label class="tutor-label" for="message">Message</label>
  <textarea id="message" class="tutor-textarea" placeholder="Enter your message"></textarea>
</div>
```

## Alpine.js Components

The TutorCore class provides factory methods for creating Alpine.js component data objects with built-in RTL support, accessibility features, and TypeScript definitions.

### Dropdown

**Configuration Options:**

- `placement`: Position relative to trigger ('bottom-start', 'bottom-end', 'top-start', 'top-end')
- `offset`: Distance from trigger element (default: 4)
- `closeOnClickOutside`: Close when clicking outside (default: true)

```html
<div x-data="TutorCore.dropdown({ placement: 'bottom-start', offset: 8 })" class="tutor-dropdown">
  <button @click="toggle()" class="tutor-btn tutor-btn--primary">
    Options <i class="tutor-icon-chevron-down"></i>
  </button>
  <div x-show="open" @click.outside="close()" class="tutor-dropdown__menu">
    <a href="#" class="tutor-dropdown__item">Profile</a>
    <a href="#" class="tutor-dropdown__item">Settings</a>
    <a href="#" class="tutor-dropdown__item">Logout</a>
  </div>
</div>
```

### Modal

**Configuration Options:**

- `closable`: Allow closing with ESC key or backdrop click (default: true)
- `backdrop`: Show backdrop overlay (default: true)
- `keyboard`: Enable ESC key to close (default: true)

```html
<div x-data="TutorCore.modal({ closable: true, backdrop: true, keyboard: true })">
  <button @click="show()" class="tutor-btn tutor-btn--primary">Open Modal</button>

  <div x-show="open" class="tutor-modal" x-transition>
    <div class="tutor-modal__backdrop" @click="hide()"></div>
    <div class="tutor-modal__content" @keydown.escape="hide()">
      <div class="tutor-modal__header">
        <h3>Modal Title</h3>
        <button @click="hide()" class="tutor-modal__close">&times;</button>
      </div>
      <div class="tutor-modal__body">
        <p>Modal content goes here...</p>
      </div>
      <div class="tutor-modal__footer">
        <button @click="hide()" class="tutor-btn tutor-btn--secondary">Cancel</button>
        <button class="tutor-btn tutor-btn--primary">Confirm</button>
      </div>
    </div>
  </div>
</div>
```

### Tabs

**Configuration Options:**

- `defaultTab`: Initial active tab index (default: 0)

```html
<div x-data="TutorCore.tabs({ defaultTab: 1 })" class="tutor-tabs">
  <div class="tutor-tabs__nav">
    <button @click="setTab(0)" :class="{'active': isActive(0)}" class="tutor-tab">Overview</button>
    <button @click="setTab(1)" :class="{'active': isActive(1)}" class="tutor-tab">Details</button>
    <button @click="setTab(2)" :class="{'active': isActive(2)}" class="tutor-tab">Reviews</button>
  </div>
  <div class="tutor-tabs__content">
    <div x-show="isActive(0)" class="tutor-tab-panel">Overview content...</div>
    <div x-show="isActive(1)" class="tutor-tab-panel">Details content...</div>
    <div x-show="isActive(2)" class="tutor-tab-panel">Reviews content...</div>
  </div>
</div>
```

### Accordion

**Configuration Options:**

- `multiple`: Allow multiple panels open (default: false)
- `defaultOpen`: Array of initially open panel indices (default: [])

```html
<div x-data="TutorCore.accordion({ multiple: true, defaultOpen: [0] })" class="tutor-accordion">
  <div class="tutor-accordion__item">
    <button @click="toggle(0)" class="tutor-accordion__trigger">
      <span>Panel 1</span>
      <i :class="{'rotate-180': isOpen(0)}" class="tutor-icon-chevron-down"></i>
    </button>
    <div x-show="isOpen(0)" x-collapse class="tutor-accordion__content">
      <p>Panel 1 content...</p>
    </div>
  </div>
  <div class="tutor-accordion__item">
    <button @click="toggle(1)" class="tutor-accordion__trigger">
      <span>Panel 2</span>
      <i :class="{'rotate-180': isOpen(1)}" class="tutor-icon-chevron-down"></i>
    </button>
    <div x-show="isOpen(1)" x-collapse class="tutor-accordion__content">
      <p>Panel 2 content...</p>
    </div>
  </div>
</div>
```

### Toast Notifications

**Methods:**

- `show(message, config)`: Display a toast notification
- `success(message)`: Show success toast
- `error(message)`: Show error toast
- `warning(message)`: Show warning toast
- `info(message)`: Show info toast
- `remove(id)`: Remove specific toast
- `clear()`: Remove all toasts

```html
<div x-data="TutorCore.toast()">
  <button @click="success('Operation completed successfully!')" class="tutor-btn tutor-btn--primary">
    Success Toast
  </button>
  <button @click="error('Something went wrong!')" class="tutor-btn tutor-btn--primary">Error Toast</button>
  <button @click="warning('Please check your input!')" class="tutor-btn tutor-btn--primary">Warning Toast</button>
  <button @click="info('Here is some information.')" class="tutor-btn tutor-btn--primary">Info Toast</button>

  <div class="tutor-toast-container">
    <template x-for="toast in toasts" :key="toast.id">
      <div class="tutor-toast" :class="`tutor-toast--${toast.type}`" x-transition>
        <span x-text="toast.message"></span>
        <button @click="remove(toast.id)" class="tutor-toast__close">&times;</button>
      </div>
    </template>
  </div>
</div>
```

### Tooltip

**Configuration Options:**

- `placement`: Tooltip position ('top', 'bottom', 'left', 'right')
- `trigger`: Trigger event ('hover', 'focus', 'click')
- `delay`: Show/hide delay in milliseconds

```html
<div x-data="TutorCore.tooltip({ placement: 'top', trigger: 'hover', delay: 200 })">
  <button @mouseenter="show()" @mouseleave="hide()" class="tutor-btn tutor-btn--primary">Hover me</button>
  <div x-show="visible" x-transition class="tutor-tooltip tutor-tooltip--top">This is a helpful tooltip!</div>
</div>
```

### Popover

**Configuration Options:**

- `placement`: Popover position ('top', 'bottom', 'left', 'right')
- `trigger`: Trigger event ('click', 'hover')
- `closeOnClickOutside`: Close when clicking outside (default: true)

```html
<div x-data="TutorCore.popover({ placement: 'bottom', trigger: 'click' })">
  <button @click="toggle()" class="tutor-btn tutor-btn--primary">Show Popover</button>
  <div x-show="open" @click.outside="close()" x-transition class="tutor-popover tutor-popover--bottom">
    <div class="tutor-popover__arrow"></div>
    <div class="tutor-popover__content">
      <h4>Popover Title</h4>
      <p>This is popover content with more detailed information.</p>
      <button @click="close()" class="tutor-btn tutor-btn--small">Close</button>
    </div>
  </div>
</div>
```

### Sidebar

**Configuration Options:**

- `collapsed`: Initial collapsed state (default: false)
- `breakpoint`: Breakpoint for responsive behavior ('mobile', 'tablet', 'desktop')

```html
<div x-data="TutorCore.sidebar({ collapsed: false, breakpoint: 'tablet' })">
  <button @click="toggle()" class="tutor-btn tutor-btn--primary">Toggle Sidebar</button>

  <div class="tutor-layout">
    <aside :class="{'collapsed': collapsed}" class="tutor-sidebar">
      <nav class="tutor-sidebar__nav">
        <a href="#" class="tutor-sidebar__item">Dashboard</a>
        <a href="#" class="tutor-sidebar__item">Courses</a>
        <a href="#" class="tutor-sidebar__item">Settings</a>
      </nav>
    </aside>
    <main class="tutor-main">
      <p>Main content area</p>
    </main>
  </div>
</div>
```

### Form Validation

**Configuration Options:**

- `rules`: Validation rules object
- `messages`: Custom error messages
- `validateOnInput`: Validate on input change (default: true)

```html
<div
  x-data="TutorCore.formValidation({
    rules: {
        email: ['required', 'email'],
        password: ['required', 'min:8']
    },
    messages: {
        'email.required': 'Email is required',
        'email.email': 'Please enter a valid email',
        'password.required': 'Password is required',
        'password.min': 'Password must be at least 8 characters'
    }
})"
>
  <form @submit.prevent="validate()">
    <div class="tutor-form-group">
      <label class="tutor-label">Email</label>
      <input
        type="email"
        x-model="fields.email"
        @input="validateField('email')"
        :class="{'error': hasError('email')}"
        class="tutor-input"
      />
      <p x-show="hasError('email')" x-text="getError('email')" class="tutor-error-text"></p>
    </div>

    <div class="tutor-form-group">
      <label class="tutor-label">Password</label>
      <input
        type="password"
        x-model="fields.password"
        @input="validateField('password')"
        :class="{'error': hasError('password')}"
        class="tutor-input"
      />
      <p x-show="hasError('password')" x-text="getError('password')" class="tutor-error-text"></p>
    </div>

    <button type="submit" :disabled="!isValid" class="tutor-btn tutor-btn--primary">Submit</button>
  </form>
</div>
```

## Utility Classes

### Spacing

```html
<!-- Margin utilities -->
<div class="tutor-m-4">Margin on all sides</div>
<div class="tutor-mt-2">Margin top</div>
<div class="tutor-mx-6">Horizontal margin</div>

<!-- Padding utilities -->
<div class="tutor-p-4">Padding on all sides</div>
<div class="tutor-pt-2">Padding top</div>
<div class="tutor-px-6">Horizontal padding</div>
```

### Layout

```html
<!-- Flexbox utilities -->
<div class="tutor-flex tutor-flex-center">Centered flex container</div>
<div class="tutor-flex tutor-flex-between">Space between flex container</div>
<div class="tutor-flex-column">Flex column</div>

<!-- Grid utilities -->
<div class="tutor-grid">Grid container</div>

<!-- Container -->
<div class="tutor-container">Responsive container</div>
```

### Typography

```html
<!-- Typography component classes -->
<h1 class="tutor-h1">Heading 1</h1>
<h2 class="tutor-h2">Heading 2</h2>
<p class="tutor-p1">Paragraph 1</p>
<p class="tutor-p2">Paragraph 2</p>

<!-- Individual utility classes (recommended for custom styling) -->
<span class="tutor-text-small tutor-font-medium tutor-text-primary">
  Small text with medium weight and primary color
</span>

<!-- Alternative using typography class with font weight -->
<p class="tutor-p2 tutor-font-medium">Paragraph 2 with medium font weight</p>

<!-- Font size utilities -->
<span class="tutor-text-h3">Large text using H3 size</span>
<span class="tutor-text-p2">Small text using P2 size</span>

<!-- Font weight utilities -->
<span class="tutor-font-weak">Regular weight (400)</span>
<span class="tutor-font-medium">Medium weight (500)</span>
<span class="tutor-font-semi-strong">Semi bold weight (600)</span>
<span class="tutor-font-strong">Bold weight (700)</span>
```

### Colors

```html
<!-- Text colors -->
<p class="tutor-text-primary">Primary text</p>
<p class="tutor-text-secondary">Secondary text</p>
<p class="tutor-text-disabled">Disabled text</p>
<p class="tutor-text-brand">Brand text</p>
<p class="tutor-text-success">Success text</p>
<p class="tutor-text-warning">Warning text</p>
<p class="tutor-text-error">Error text</p>

<!-- Background colors -->
<div class="tutor-bg-surface-base">Base surface</div>
<div class="tutor-bg-surface-l1">Level 1 surface</div>
<div class="tutor-bg-surface-l2">Level 2 surface</div>

<!-- Brand colors (100-950 scale) -->
<div class="tutor-bg-brand-100">Brand 100</div>
<div class="tutor-bg-brand-600">Brand 600</div>
<div class="tutor-bg-brand-950">Brand 950</div>

<!-- Semantic colors -->
<div class="tutor-bg-success-100">Success light</div>
<div class="tutor-bg-warning-500">Warning medium</div>
<div class="tutor-bg-error-600">Error primary</div>
```

## CSS Classes Reference

### Button Classes

```css
/* Base button class */
.tutor-btn

/* Button variants */
.tutor-btn--primary
.tutor-btn--secondary
.tutor-btn--outline
.tutor-btn--ghost

/* Button sizes */
.tutor-btn--small
.tutor-btn--medium
.tutor-btn--large

/* Button states */
.tutor-btn:disabled
.tutor-btn--loading
```

### Card Classes

```css
/* Base card class */
.tutor-card

/* Card sections */
.tutor-card__header
.tutor-card__body
.tutor-card__footer
.tutor-card__title
.tutor-card__subtitle

/* Card variants */
.tutor-card-elevated
.tutor-card-outlined
.tutor-card-interactive
```

### Form Classes

```css
/* Form elements */
.tutor-form-group
.tutor-label
.tutor-input
.tutor-textarea
.tutor-select
.tutor-checkbox
.tutor-radio

/* Form states */
.tutor-input--error
.tutor-input--success
.tutor-input--disabled

/* Form text */
.tutor-help-text
.tutor-error-text
.tutor-success-text
```

### Component Classes

```css
/* Dropdown */
.tutor-dropdown
.tutor-dropdown__menu
.tutor-dropdown__item

/* Modal */
.tutor-modal
.tutor-modal__backdrop
.tutor-modal__content
.tutor-modal__header
.tutor-modal__body
.tutor-modal__footer
.tutor-modal__close

/* Tabs */
.tutor-tabs
.tutor-tabs__nav
.tutor-tab
.tutor-tab.active
.tutor-tabs__content
.tutor-tab-panel

/* Accordion */
.tutor-accordion
.tutor-accordion__item
.tutor-accordion__trigger
.tutor-accordion__content

/* Toast */
.tutor-toast-container
.tutor-toast
.tutor-toast--success
.tutor-toast--error
.tutor-toast--warning
.tutor-toast--info
.tutor-toast__close

/* Tooltip */
.tutor-tooltip
.tutor-tooltip--top
.tutor-tooltip--bottom
.tutor-tooltip--left
.tutor-tooltip--right

/* Popover */
.tutor-popover
.tutor-popover__arrow
.tutor-popover__content

/* Sidebar */
.tutor-sidebar
.tutor-sidebar.collapsed
.tutor-sidebar__nav
.tutor-sidebar__item
```

### Spacing Classes

```css
/* Margin classes (0-21 based on Figma tokens) */
.tutor-m-{size}    /* All sides */
.tutor-mt-{size}   /* Top */
.tutor-me-{size}   /* End (RTL-aware right/left) */
.tutor-mb-{size}   /* Bottom */
.tutor-ms-{size}   /* Start (RTL-aware left/right) */
.tutor-mx-{size}   /* Horizontal */
.tutor-my-{size}   /* Vertical */

/* Padding classes (0-21 based on Figma tokens) */
.tutor-p-{size}    /* All sides */
.tutor-pt-{size}   /* Top */
.tutor-pe-{size}   /* End (RTL-aware right/left) */
.tutor-pb-{size}   /* Bottom */
.tutor-ps-{size}   /* Start (RTL-aware left/right) */
.tutor-px-{size}   /* Horizontal */
.tutor-py-{size}   /* Vertical */

/* Available sizes: 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21 */
/* Corresponding to: 0px, 2px, 4px, 6px, 8px, 12px, 16px, 20px, 24px, 28px, 32px, 36px, 40px, 44px, 48px, 56px, 64px, 80px, 96px, 112px, 128px, 200px */
```

### Layout Classes

```css
/* Container */
.tutor-container

/* Flexbox */
.tutor-flex
.tutor-flex-center
.tutor-flex-between
.tutor-flex-around
.tutor-flex-column
.tutor-flex-wrap

/* Grid */
.tutor-grid

/* Display */
.tutor-block
.tutor-inline
.tutor-inline-block
.tutor-hidden

/* Responsive utilities */
.tutor-md-flex
.tutor-md-grid
.tutor-md-hidden
.tutor-lg-flex
.tutor-lg-grid
.tutor-lg-hidden
```

### Typography Classes

```css
/* Headings */
.tutor-h1
.tutor-h2
.tutor-h3
.tutor-h4
.tutor-h5

/* Paragraphs */
.tutor-p1
.tutor-p2
.tutor-p3

/* Text utilities */
.tutor-text-left
.tutor-text-center
.tutor-text-right
.tutor-text-justify

/* Font weights */
.tutor-font-light
.tutor-font-normal
.tutor-font-medium
.tutor-font-semibold
.tutor-font-bold
```

## SASS Mixins

If you're using SASS, you can use the provided mixins for custom components. All mixins are RTL-aware and use design tokens:

### Button Mixins

```scss
@import 'tutor-design-system/scss/main';

.my-button {
  @include tutor-button-base;
  @include tutor-button-variant(primary); // primary, secondary, outline, ghost
  @include tutor-button-size(large); // small, medium, large
}

.my-button-group {
  @include tutor-button-group(horizontal); // horizontal, vertical
}

.loading-button {
  @include tutor-button-loading;
}

// RTL-aware button with icon
.icon-button {
  @include tutor-button-base;
  @include tutor-button-variant(primary);

  .icon {
    @include margin-end(8px); // Automatically adapts to RTL
  }
}
```

### Card Mixins

```scss
.my-card {
  @include tutor-card-base;
  @include tutor-card-elevation(2); // 0-4
  @include tutor-card-padding(large); // small, medium, large
  @include tutor-card-radius(large); // small, medium, large
}

.interactive-card {
  @include tutor-card-interactive;
}
```

### Form Mixins

```scss
.my-input {
  @include tutor-input-base;
  @include tutor-input-size(large); // small, medium, large
  @include tutor-input-validation(error); // error, success, warning
}

.my-textarea {
  @include tutor-textarea-base;
  @include tutor-textarea-resize(vertical); // none, both, horizontal, vertical
}

.my-select {
  @include tutor-select-base;
  @include tutor-select-arrow;
}
```

### Layout Mixins

```scss
.my-container {
  @include tutor-container;
  @include tutor-container-size(large); // small, medium, large, full
}

.my-flex {
  @include tutor-flex;
  @include tutor-flex-center;
  @include tutor-flex-gap(16px);
}

.my-grid {
  @include tutor-grid;
  @include tutor-grid-columns(3); // Number of columns
  @include tutor-grid-gap(24px);
}
```

### RTL Mixins

```scss
.my-component {
  // Directional margins (automatically adapts to RTL)
  @include margin-start(16px);
  @include margin-end(8px);

  // Directional padding (automatically adapts to RTL)
  @include padding-start(12px);
  @include padding-end(12px);

  // Directional borders (automatically adapts to RTL)
  @include border-start(1px solid var(--tutor-color-border-primary));
  @include border-radius-start(8px);

  // Text alignment (automatically adapts to RTL)
  @include text-align-start;

  // Positioning (automatically adapts to RTL)
  @include inset-inline-start(0);

  // Transform for RTL
  @include rtl-transform(translateX(-100%));

  // Icon positioning (automatically adapts to RTL)
  .icon {
    @include icon-start(8px);
  }
}

// RTL-aware utility mixins
.rtl-aware-component {
  @include rtl-property(margin-left, margin-right, 16px);
  @include rtl-value(text-align, left, right);
  @include rtl-flip-horizontal; // Flips component horizontally in RTL
}
```

### Utility Mixins

```scss
.visually-hidden {
  @include tutor-visually-hidden;
}

.truncate-text {
  @include tutor-truncate;
}

.focus-ring {
  @include tutor-focus-ring;
}

// Responsive mixins
.responsive-component {
  @include tutor-respond-to(md) {
    // Styles for tablet and up
  }

  @include tutor-respond-to(lg) {
    // Styles for desktop and up
  }
}
```

### Animation Mixins

```scss
.fade-in {
  @include tutor-fade-in(0.3s);
}

.slide-up {
  @include tutor-slide-up(0.2s);
}

.bounce {
  @include tutor-bounce;
}

.spin {
  @include tutor-spin(1s);
}
```

## TutorCore API Reference

### Component Factory Methods

All component methods return Alpine.js data objects that can be used with `x-data`:

#### `TutorCore.dropdown(config?: DropdownConfig)`

Creates a dropdown component with RTL-aware positioning and keyboard navigation.

**Config Options:**

```typescript
interface DropdownConfig {
  placement?: 'bottom-start' | 'bottom-end' | 'top-start' | 'top-end';
  offset?: number;
  closeOnClickOutside?: boolean;
}
```

**Returned Methods:**

- `toggle()`: Toggle dropdown visibility
- `open()`: Show dropdown
- `close()`: Hide dropdown
- `handleKeydown(event)`: Handle keyboard navigation

#### `TutorCore.modal(config?: ModalConfig)`

Creates a modal with focus management and accessibility features.

**Config Options:**

```typescript
interface ModalConfig {
  closable?: boolean;
  backdrop?: boolean;
  keyboard?: boolean;
}
```

**Returned Methods:**

- `show()`: Show modal
- `hide()`: Hide modal
- `handleKeydown(event)`: Handle ESC key

#### `TutorCore.toast()`

Creates a toast notification system with stacking and auto-dismiss.

**Returned Methods:**

- `show(message: string, config?: ToastConfig)`: Show toast
- `success(message: string)`: Show success toast
- `error(message: string)`: Show error toast
- `warning(message: string)`: Show warning toast
- `info(message: string)`: Show info toast
- `remove(id: number)`: Remove specific toast
- `clear()`: Remove all toasts

#### `TutorCore.tabs(config?: TabsConfig)`

Creates a tab component with keyboard navigation and ARIA support.

**Config Options:**

```typescript
interface TabsConfig {
  defaultTab?: number;
}
```

**Returned Methods:**

- `setTab(index: number)`: Set active tab
- `isActive(index: number)`: Check if tab is active
- `nextTab()`: Navigate to next tab
- `prevTab()`: Navigate to previous tab

#### `TutorCore.accordion(config?: AccordionConfig)`

Creates an accordion with single or multiple panel support.

**Config Options:**

```typescript
interface AccordionConfig {
  multiple?: boolean;
  defaultOpen?: number[];
}
```

**Returned Methods:**

- `toggle(index: number)`: Toggle panel
- `open(index: number)`: Open panel
- `close(index: number)`: Close panel
- `isOpen(index: number)`: Check if panel is open

### Utility Methods

#### `TutorCore.utils.isRTL(): boolean`

Check if the current document direction is RTL.

#### `TutorCore.utils.getDirection(): 'ltr' | 'rtl'`

Get the current document direction.

#### `TutorCore.utils.adaptPlacement(placement: string): string`

Adapt placement strings for RTL layouts.

#### `TutorCore.utils.generateId(): string`

Generate a unique ID for components.

#### `TutorCore.utils.debounce<T>(func: T, wait: number): T`

Debounce function calls.

#### `TutorCore.utils.throttle<T>(func: T, limit: number): T`

Throttle function calls.

#### `TutorCore.utils.getBreakpoint(): string`

Get current breakpoint ('mobile', 'tablet', 'desktop').

#### `TutorCore.utils.isMobile(): boolean`

Check if current viewport is mobile.

#### `TutorCore.utils.isTablet(): boolean`

Check if current viewport is tablet.

#### `TutorCore.utils.isDesktop(): boolean`

Check if current viewport is desktop.

## TypeScript Support

The library includes full TypeScript definitions:

```typescript
import { TutorCore } from 'tutor-design-system';

// Use component methods with type safety
const dropdownData = TutorCore.dropdown({
  placement: 'bottom-start',
  closeOnClickOutside: true,
});

const modalData = TutorCore.modal({
  keyboard: true,
  backdrop: true,
});

// Access utility methods
const isRTL = TutorCore.utils.isRTL();
const uniqueId = TutorCore.utils.generateId();

// Use with Alpine.js
document.addEventListener('alpine:init', () => {
  Alpine.data('myDropdown', () => TutorCore.dropdown());
});
```

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## For Developers

### Architecture Overview

The Tutor Design System follows a hybrid approach combining the best of both SASS variables and CSS custom properties:

- **SASS Variables**: Used for static values that don't change between themes (typography, spacing, radius)
- **CSS Custom Properties**: Used for dynamic values that change between themes (colors, surfaces)
- **RTL Mixins**: Provide directional-aware styling for international support
- **Component Patterns**: Follow BEM naming convention with design system integration

### When to Use SASS Variables vs CSS Custom Properties

**✅ Use SASS Variables for:**

```scss
// Static values that don't change between themes
font-family: $tutor-font-family-body;
font-size: $tutor-font-size-p1;
line-height: $tutor-line-height-p1;
font-weight: $tutor-font-weight-medium;
padding: $tutor-spacing-4;
border-radius: $tutor-radius-md;
```

**✅ Use CSS Custom Properties for:**

```scss
// Dynamic values that change between themes
color: var(--tutor-text-primary);
background-color: var(--tutor-surface-l1);
border-color: var(--tutor-border-idle);
```

**✅ Best Practice - Mixed Approach:**

```scss
.my-component {
  // Static properties - SASS variables
  font-family: $tutor-font-family-body;
  font-size: $tutor-font-size-p1;
  padding: $tutor-spacing-4;
  border-radius: $tutor-radius-md;

  // Dynamic properties - CSS custom properties
  color: var(--tutor-text-primary);
  background: var(--tutor-surface-l1);
  border: 1px solid var(--tutor-border-idle);
}
```

### Building Custom Components

Follow these patterns when creating custom components:

#### 1. Component Structure Template

```scss
.my-component {
  // Layout & Structure (SASS variables)
  display: flex;
  padding: $tutor-spacing-4;
  border-radius: $tutor-radius-lg;
  font-family: $tutor-font-family-body;
  font-size: $tutor-font-size-p1;

  // Appearance (CSS custom properties)
  background: var(--tutor-surface-l1);
  border: 1px solid var(--tutor-border-idle);
  color: var(--tutor-text-primary);

  // RTL Support
  @include text-align-start();

  // States
  &:hover {
    border-color: var(--tutor-border-brand);
  }

  // Variants
  &--primary {
    background: var(--tutor-actions-brand-primary);
    color: var(--tutor-text-primary-inverse);
  }

  // Elements
  &__header {
    @include margin-end($tutor-spacing-4);
  }
}
```

#### 2. RTL-Aware Development

Always use RTL mixins for directional properties:

```scss
.my-component {
  // ✅ Good - RTL aware
  @include margin-start($tutor-spacing-4);
  @include padding-end($tutor-spacing-2);
  @include border-start(2px solid var(--tutor-border-brand));
  @include text-align-start();

  // ❌ Bad - Not RTL aware
  margin-left: $tutor-spacing-4;
  padding-right: $tutor-spacing-2;
  border-left: 2px solid var(--tutor-border-brand);
  text-align: left;
}
```

#### 3. Component Development Best Practices

**✅ Do's:**

- Use BEM naming convention
- Prefix components with your namespace
- Mix SASS variables and CSS custom properties appropriately
- Use RTL mixins for directional properties
- Test in both light and dark themes
- Test in both LTR and RTL directions
- Use design system tokens instead of hardcoded values
- Include proper accessibility attributes

**❌ Don'ts:**

- Don't use hardcoded colors, sizes, or spacing
- Don't use left/right properties directly
- Don't override design system components directly
- Don't use CSS custom properties for static values
- Don't use SASS variables for theme-dependent colors
- Don't forget RTL testing

### Common Patterns

#### Alert Component Pattern

```scss
.custom-alert {
  padding: $tutor-spacing-4 $tutor-spacing-6;
  border-radius: $tutor-radius-md;
  font-size: $tutor-font-size-p2;
  @include border-start(4px solid);

  &--success {
    background: var(--tutor-actions-success-tertiary);
    border-color: var(--tutor-actions-success-primary);
    color: var(--tutor-text-success);
  }

  &--error {
    background: var(--tutor-actions-critical-secondary);
    border-color: var(--tutor-actions-critical-primary);
    color: var(--tutor-text-critical);
  }
}
```

#### Card Component Pattern

```scss
.custom-card {
  background: var(--tutor-surface-l1);
  border: 1px solid var(--tutor-border-idle);
  border-radius: $tutor-radius-xl;
  padding: $tutor-spacing-6;
  transition: all 0.2s ease;

  &:hover {
    border-color: var(--tutor-border-brand);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  &__header {
    margin-bottom: $tutor-spacing-4;
    @include padding-bottom($tutor-spacing-4);
    border-bottom: 1px solid var(--tutor-border-idle);
  }

  &__title {
    font-size: $tutor-font-size-h4;
    font-weight: $tutor-font-weight-semi-strong;
    color: var(--tutor-text-primary);
    margin: 0;
  }
}
```

## Examples

The library includes comprehensive example files for developers:

- **`examples/basic-usage.html`** - Basic setup and component usage
- **`examples/alpine-components.html`** - All Alpine.js components with examples
- **`examples/rtl-support.html`** - RTL language support demonstration
- **`examples/font-scaling.html`** - Font scaling and accessibility features
- **`examples/sass-development.html`** - SASS development guide with mixins and tokens
- **`examples/design-tokens.html`** - Visual guide to all design tokens
- **`examples/custom-components.html`** - Building custom components following design system patterns
- **`examples/tutor-design-system-components.html`** - Complete TutorCore API examples

To view the examples:

```bash
# Serve the examples directory
npx serve examples/

# Or open directly in browser
open examples/basic-usage.html
```

## Development

### Building from Source

```bash
# Install dependencies
npm install

# Build CSS and JS files
npm run build

# Watch for changes during development
npm run dev

# Run build script with file size summary
node build.js
```

### File Structure

```
assets/core/
├── scss/
│   ├── tokens/          # Design tokens based on Figma specifications
│   │   ├── _colors.scss      # Complete color scales (Brand, Gray, Success, Warning, Error, Yellow, Exception)
│   │   ├── _typography.scss  # Typography scale (H1-H5, P1-P3) with line heights
│   │   ├── _spacing.scss     # Spacing scale (0-21: 0px to 200px)
│   │   ├── _radius.scss      # Border radius scale (none to full)
│   │   └── _breakpoints.scss # Responsive breakpoints
│   ├── themes/          # Theme variants with semantic token mappings
│   │   ├── _light.scss       # Light theme with Figma surface and text colors
│   │   └── _dark.scss        # Dark theme with proper contrast ratios
│   ├── mixins/          # SASS mixins with RTL support
│   │   ├── _buttons.scss     # Button variants and states
│   │   ├── _cards.scss       # Card layouts and elevation
│   │   ├── _forms.scss       # Form elements and validation
│   │   ├── _layout.scss      # Flexbox, grid, and positioning
│   │   ├── _utilities.scss   # Common utility patterns
│   │   └── _rtl.scss         # RTL-aware directional mixins
│   ├── components/      # Component styles with RTL support
│   │   ├── _button.scss      # All button variants and sizes
│   │   ├── _card.scss        # Flexible card layouts
│   │   ├── _form.scss        # Form styling and validation states
│   │   ├── _navigation.scss  # Tabs, breadcrumbs, pagination
│   │   ├── _progress.scss    # Progress bars and indicators
│   │   ├── _dropdown.scss    # Dropdown with RTL positioning
│   │   ├── _modal.scss       # Modal with backdrop and responsive layouts
│   │   ├── _tabs.scss        # Tab component with transitions
│   │   ├── _accordion.scss   # Accordion with animations
│   │   ├── _toast.scss       # Toast notifications with stacking
│   │   ├── _tooltip.scss     # Tooltip with RTL positioning
│   │   ├── _popover.scss     # Popover with arrow positioning
│   │   └── _sidebar.scss     # Sidebar with collapse states
│   ├── utilities/       # RTL-aware utility classes
│   │   ├── _colors.scss      # Color utilities for all themes
│   │   ├── _layout.scss      # Flexbox, grid, display utilities
│   │   ├── _spacing.scss     # RTL-aware margin and padding
│   │   ├── _typography.scss  # Typography and text utilities
│   │   ├── _borders.scss     # Border utilities with smart defaults
│   │   └── _sizing.scss      # Width, height, and sizing utilities
│   └── main.scss        # Main entry point
├── ts/
│   ├── types/           # TypeScript definitions (no 'any' types)
│   │   ├── components.ts     # Component interfaces and types
│   │   └── alpine.ts         # Alpine.js integration types
│   ├── components/      # Component logic with proper typing
│   │   ├── dropdown.ts       # Dropdown with RTL positioning
│   │   ├── modal.ts          # Modal with focus management
│   │   ├── tabs.ts           # Tabs with keyboard navigation
│   │   ├── accordion.ts      # Accordion with animation support
│   │   ├── toast.ts          # Toast system with stacking
│   │   ├── tooltip.ts        # Tooltip with RTL positioning
│   │   ├── popover.ts        # Popover with collision detection
│   │   ├── sidebar.ts        # Sidebar with responsive behavior
│   │   └── form-validation.ts # Form validation with custom rules
│   ├── utils/           # Utility functions
│   │   └── rtl-detection.ts  # RTL detection and utilities
│   └── index.ts         # Main TutorCore class export
├── examples/            # Example HTML files
│   ├── basic-usage.html
│   ├── alpine-components.html
│   ├── rtl-support.html
│   └── tutor-design-system-components.html
├── dist/                # Compiled files
│   ├── tutor-design-system.css     # Single CSS file with all themes
│   ├── tutor-design-system.min.css # Minified CSS
│   ├── tutor-design-system.js      # Single JS file with TutorCore
│   ├── tutor-design-system.min.js  # Minified JS
│   └── tutor-design-system.d.ts    # TypeScript declarations
├── package.json
├── tsconfig.json
├── rollup.config.js
├── build.js
├── CONTRIBUTING.md      # Development guidelines
└── README.md
```

## Troubleshooting

### Common Issues

**CSS not loading properly:**

- Ensure the CSS file is included before any custom styles
- Check that the `data-theme` attribute is set on the `<html>` element
- Verify the file path is correct

**Alpine.js components not working:**

- Make sure Alpine.js is loaded before the design system JS
- Check that the `x-data` directive uses the correct TutorCore method
- Ensure the component is properly initialized

**RTL layout issues:**

- Set the `dir="rtl"` attribute on the `<html>` element
- Check that RTL-specific CSS classes are being applied
- Verify that directional properties are working correctly

**TypeScript errors:**

- Ensure the TypeScript declaration file (`index.d.ts`) is included
- Check that the import path is correct
- Verify that the TypeScript version is compatible

**Build errors:**

- Run `npm install` to ensure all dependencies are installed
- Check that Node.js version is 16 or higher
- Clear the `dist/` directory and rebuild

### Performance Tips

- Use the minified versions (`*.min.css` and `*.min.js`) in production
- Enable gzip compression on your server for better file sizes
- Consider loading the CSS inline for critical above-the-fold content
- Use the `preload` link relation for faster resource loading:

```html
<link rel="preload" href="tutor-design-system.min.css" as="style" />
<link rel="preload" href="tutor-design-system.min.js" as="script" />
```

- The design system uses CSS custom properties for theme switching, which is more performant than class-based theming
- All utility classes are RTL-aware by default, eliminating the need for separate RTL stylesheets
- Single file distribution reduces HTTP requests and improves loading performance

### Browser Compatibility

If you need to support older browsers:

- Include CSS custom property polyfills for IE11
- Use Alpine.js v2 for better IE11 support
- Consider using PostCSS autoprefixer for vendor prefixes
- Test thoroughly in your target browsers

## License

MIT License - see LICENSE file for details.
