# Contributing to Tutor Design System

Thank you for your interest in contributing to the Tutor Design System! This document provides guidelines and information for contributors.

## Development Setup

### Prerequisites

- Node.js 16+ and npm
- SASS compiler
- TypeScript compiler

### Getting Started

1. **Clone and setup:**

   ```bash
   cd assets/core
   npm install
   ```

2. **Development mode:**

   ```bash
   npm run dev
   # or
   make dev
   ```

3. **Build for production:**
   ```bash
   npm run build
   # or
   make build
   ```

## Project Structure

```
assets/core/
├── scss/                    # SASS source files
│   ├── tokens/             # Design tokens (colors, typography, spacing)
│   ├── themes/             # Theme variants (light, dark)
│   ├── mixins/             # Reusable SASS mixins
│   ├── components/         # Component styles
│   └── main.scss           # Main entry point
├── ts/                     # TypeScript source files
│   ├── types/              # Type definitions
│   ├── components/         # Component logic
│   └── index.ts            # Main entry point
├── examples/               # Usage examples
├── dist/                   # Compiled output files
└── README.md               # Documentation
```

## Design Tokens

The design system is built on a foundation of design tokens defined in the `scss/tokens/` directory:

### Colors (`_colors.scss`)

- Brand colors (100-950 scale)
- Semantic colors (success, warning, error)
- Gray scale (1-950)

### Typography (`_typography.scss`)

- Font sizes (H1-H5, P1-P3)
- Line heights
- Font weights

### Spacing (`_spacing.scss`)

- Spacing scale (0-21: 0px to 200px)

### Border Radius (`_radius.scss`)

- Radius scale (none to full)

### Breakpoints (`_breakpoints.scss`)

- Responsive breakpoints

## Component Development

### SASS Components

When creating new components:

1. **Create the component file:**

   ```scss
   // scss/components/_my-component.scss
   .tutor-my-component {
     @include tutor-component-base;
     // Component styles here
   }
   ```

2. **Use design tokens:**

   ```scss
   .tutor-my-component {
     padding: $tutor-spacing-4;
     background-color: var(--tutor-surface-l1);
     border-radius: $tutor-radius-md;
   }
   ```

3. **Create mixins for reusability:**

   ```scss
   // scss/mixins/_my-component.scss
   @mixin tutor-my-component-variant($variant: default) {
     @if $variant == primary {
       background-color: var(--tutor-button-primary);
     }
   }
   ```

4. **Import in main.scss:**
   ```scss
   @import 'components/my-component';
   ```

### TypeScript Components

When creating Alpine.js components:

1. **Create the component file:**

   ```typescript
   // ts/components/my-component.ts
   import { MyComponentConfig } from '../types/components';

   export function createMyComponent(config: MyComponentConfig = {}) {
     return {
       // Component data and methods
       init() {
         // Initialization logic
       },
     };
   }
   ```

2. **Add type definitions:**

   ```typescript
   // ts/types/components.ts
   export interface MyComponentConfig {
     option1?: boolean;
     option2?: string;
   }
   ```

3. **Register in index.ts:**

   ```typescript
   // ts/index.ts
   import { createMyComponent } from './components/my-component';

   export class TutorCore {
     static myComponent(config?: MyComponentConfig) {
       return createMyComponent(config);
     }
   }
   ```

## Theme Development

### Creating Theme Variants

1. **Create theme file:**

   ```scss
   // scss/themes/_my-theme.scss
   [data-theme='my-theme'] {
     --tutor-surface-base: #custom-color;
     --tutor-text-primary: #custom-text;
     // Other theme variables
   }
   ```

2. **Import in main.scss:**
   ```scss
   @import 'themes/my-theme';
   ```

### Theme Variables

Always use CSS custom properties for theme-aware values:

```scss
// ✅ Good - uses theme-aware variable
.my-component {
  background-color: var(--tutor-surface-l1);
  color: var(--tutor-text-primary);
}

// ❌ Bad - uses fixed color
.my-component {
  background-color: #ffffff;
  color: #000000;
}
```

## RTL Support

### RTL-Aware Styles

Use logical properties and RTL-aware mixins:

```scss
// ✅ Good - RTL-aware
.my-component {
  margin-inline-start: $tutor-spacing-4;
  padding-inline-end: $tutor-spacing-2;
  text-align: start;
}

// ❌ Bad - not RTL-aware
.my-component {
  margin-left: $tutor-spacing-4;
  padding-right: $tutor-spacing-2;
  text-align: left;
}
```

### RTL Component Logic

Handle RTL in TypeScript components:

```typescript
export function createMyComponent(config = {}) {
  return {
    init() {
      if (TutorCore.utils.isRTL()) {
        // RTL-specific logic
      }
    },
  };
}
```

## Testing

### Manual Testing

1. **Test all themes:**
   - Light theme
   - Dark theme
   - Any custom themes

2. **Test RTL support:**
   - Set `dir="rtl"` on html element
   - Verify component positioning
   - Check text alignment

3. **Test responsive behavior:**
   - Mobile (< 768px)
   - Tablet (768px - 1024px)
   - Desktop (> 1024px)

### Browser Testing

Test in supported browsers:

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Code Style

### SASS Guidelines

1. **Use BEM naming convention:**

   ```scss
   .tutor-component {
   }
   .tutor-component__element {
   }
   .tutor-component--modifier {
   }
   ```

2. **Use design tokens:**

   ```scss
   // ✅ Good
   padding: $tutor-spacing-4;

   // ❌ Bad
   padding: 16px;
   ```

3. **Group related properties:**
   ```scss
   .tutor-component {
     // Layout
     display: flex;
     align-items: center;

     // Spacing
     padding: $tutor-spacing-4;
     margin: $tutor-spacing-2;

     // Appearance
     background-color: var(--tutor-surface-l1);
     border-radius: $tutor-radius-md;
   }
   ```

### TypeScript Guidelines

1. **Use proper typing:**

   ```typescript
   // ✅ Good
   function createComponent(config: ComponentConfig): AlpineComponent {
     return {
       // ...
     };
   }

   // ❌ Bad
   function createComponent(config: any): any {
     return {
       // ...
     };
   }
   ```

2. **Use descriptive names:**

   ```typescript
   // ✅ Good
   const isDropdownOpen = true;

   // ❌ Bad
   const open = true;
   ```

## Documentation

### Component Documentation

When adding new components, include:

1. **Usage examples in README.md**
2. **HTML examples in examples/ directory**
3. **TypeScript interface documentation**
4. **SASS mixin documentation**

### Example Format

````markdown
### My Component

Description of what the component does.

#### Basic Usage

```html
<div class="tutor-my-component">Content here</div>
```
````

#### With Alpine.js

```html
<div x-data="TutorCore.myComponent({ option: true })">
  <!-- Component markup -->
</div>
```

#### Configuration Options

- `option1` (boolean): Description of option1
- `option2` (string): Description of option2

````

## Submitting Changes

### Before Submitting

1. **Test your changes:**
   ```bash
   npm run build
   npm run lint
   npm run type-check
````

2. **Update documentation:**
   - Update README.md if needed
   - Add examples for new components
   - Update CHANGELOG.md

3. **Test examples:**
   - Open example HTML files in browser
   - Test with different themes
   - Test RTL support

### Pull Request Guidelines

1. **Clear description:** Explain what your changes do and why
2. **Small, focused changes:** One feature or fix per PR
3. **Test coverage:** Include examples and test cases
4. **Documentation:** Update docs for any new features

## Release Process

### Version Numbering

We follow semantic versioning (semver):

- **Major (1.0.0):** Breaking changes
- **Minor (0.1.0):** New features, backward compatible
- **Patch (0.0.1):** Bug fixes, backward compatible

### Release Checklist

1. Update version in package.json
2. Update CHANGELOG.md
3. Build and test all examples
4. Create git tag
5. Update documentation

## Getting Help

- **Issues:** Report bugs and request features via GitHub issues
- **Discussions:** Ask questions in GitHub discussions
- **Documentation:** Check README.md and examples/

## License

By contributing to Tutor Design System, you agree that your contributions will be licensed under the MIT License.
