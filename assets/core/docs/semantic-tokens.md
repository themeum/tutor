# Semantic Token Files

## Overview

Semantic tokens are organized into separate files by category, each containing CSS variables that are theme-aware. This approach eliminates the need for separate theme override variables since CSS variables automatically handle theme switching.

## File Structure

```
tokens/
├── _actions.scss      # Action states (success, warning, error)
├── _borders.scss      # Border colors and radius
├── _buttons.scss      # Button colors and states
├── _colors.scss       # Primitive color palette
├── _icons.scss        # Icon colors
├── _surfaces.scss     # Background/surface colors
├── _tabs.scss         # Tab navigation colors
├── _text-colors.scss  # Text colors
├── _typography.scss   # Typography tokens
├── _spacing.scss      # Spacing scale
├── _zIndex.scss       # Z-index scale
└── _index.scss        # Exports all tokens
```

## Usage Pattern

All semantic token files follow the same pattern:

```scss
// CSS Variable References (theme-aware)
$tutor-surface-base: var(--tutor-surface-base);
$tutor-text-primary: var(--tutor-text-primary);
$tutor-button-primary: var(--tutor-button-primary);

// Maps for utility generation
$tutor-surfaces: (
  base: $tutor-surface-base,
  l1: $tutor-surface-l1, // ...
);
```

## Benefits

### ✅ **Simplified Structure**

- No need for separate theme override variables
- CSS variables handle theme switching automatically
- Cleaner, more maintainable code

### ✅ **Automatic Theme Support**

- CSS variables like `var(--tutor-surface-base)` automatically switch between light/dark themes
- Theme definitions are handled in `themes/_light.scss` and `themes/_dark.scss`

### ✅ **Better Organization**

- Each category has its own file
- Easy to find and update specific token types
- Clear separation of concerns

## How It Works

1. **Theme files** (`themes/_light.scss`, `themes/_dark.scss`) define the actual color values:

   ```scss
   // Light theme
   :root {
     --tutor-surface-base: #fafafa;
     --tutor-text-primary: #0c111d;
   }

   // Dark theme
   [data-theme='dark'] {
     --tutor-surface-base: #161b26;
     --tutor-text-primary: #ffffff;
   }
   ```

2. **Semantic token files** reference these CSS variables:

   ```scss
   $tutor-surface-base: var(--tutor-surface-base);
   $tutor-text-primary: var(--tutor-text-primary);
   ```

3. **Components** use the semantic tokens:
   ```scss
   .my-component {
     background-color: $tutor-surface-base;
     color: $tutor-text-primary;
   }
   ```

## Token Categories

### **Surfaces** (`_surfaces.scss`)

Background colors for containers, cards, and layouts.

- `surface-base`, `surface-l1`, `surface-l2`
- `surface-brand-primary`, `surface-brand-secondary`

### **Text Colors** (`_text-colors.scss`)

All text content colors.

- `text-primary`, `text-secondary`, `text-subdued`
- `text-brand`, `text-success`, `text-warning`, `text-critical`

### **Buttons** (`_buttons.scss`)

Interactive button element colors.

- `button-primary`, `button-secondary`, `button-destructive`
- Includes hover, focus, and disabled states

### **Borders** (`_borders.scss`)

Border colors and radius values.

- `border-idle`, `border-hover`, `border-focus`
- `border-brand`, `border-success`, `border-error`

### **Icons** (`_icons.scss`)

Icon-specific colors.

- `icon-idle`, `icon-hover`, `icon-secondary`
- `icon-brand`, `icon-success`, `icon-warning`

### **Actions** (`_actions.scss`)

State and feedback colors.

- `actions-success-*`, `actions-warning-*`
- `actions-brand-*`, `actions-critical-*`

### **Tabs** (`_tabs.scss`)

Navigation and tab component colors.

- `tab-sidebar-*`, `tab-l3-*`
- Includes hover and active states

## Migration from Old Approach

### Before (with theme overrides):

```scss
// Light theme defaults
$tutor-surface-primary: $tutor-gray-1;

// Dark theme overrides
$tutor-surface-primary-dark: $tutor-gray-950;

// CSS variable reference
$tutor-surface-primary-var: var(--tutor-surface-primary);
```

### After (simplified):

```scss
// Direct CSS variable reference (theme-aware)
$tutor-surface-primary: var(--tutor-surface-primary);
```

The CSS variables automatically handle the theme switching, making the code much cleaner and easier to maintain!
