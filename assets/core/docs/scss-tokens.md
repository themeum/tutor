# SCSS & Design Tokens

> Token system, theme architecture, mixins, and utility classes.

---

## Build Pipeline

```
scss/main.scss
  ├── @use 'tokens'       → All design token variables & maps
  ├── @use 'themes'       → Light + Dark theme CSS custom properties
  ├── @use 'mixins'       → Reusable SCSS mixins (RTL, buttons, layout…)
  ├── @use 'components'   → 32 component stylesheets
  └── @use 'utilities'    → Utility classes generated from token maps
```

The compiled output is a **single CSS file** that includes all themes, all components, and all utilities with automatic RTL support.

---

## Token Files

The `scss/tokens/` directory contains **19 token files**:

| File                  | Description                                                                       | Variable Type                     |
| --------------------- | --------------------------------------------------------------------------------- | --------------------------------- |
| `_colors.scss`        | Primitive color palette (Brand, Gray, Success, Warning, Error, Yellow, Exception) | SASS variables                    |
| `_text-colors.scss`   | Semantic text colors                                                              | CSS custom properties via `var()` |
| `_surfaces.scss`      | Surface/background colors                                                         | CSS custom properties via `var()` |
| `_borders.scss`       | Border colors + radius scale                                                      | Mixed                             |
| `_actions.scss`       | Interactive/action colors                                                         | CSS custom properties via `var()` |
| `_typography.scss`    | Font families, weights, sizes, line heights                                       | SASS variables                    |
| `_spacing.scss`       | 22-step spacing scale                                                             | SASS variables                    |
| `_shadows.scss`       | Box shadows + ring shadows                                                        | SASS variables                    |
| `_breakpoints.scss`   | 7 responsive breakpoints                                                          | SASS variables                    |
| `_buttons.scss`       | Button-specific tokens                                                            | Mixed                             |
| `_inputs.scss`        | Input-specific tokens                                                             | Mixed                             |
| `_tabs.scss`          | Tab-specific tokens                                                               | Mixed                             |
| `_icons.scss`         | Icon tokens                                                                       | Mixed                             |
| `_zIndex.scss`        | Z-index scale                                                                     | Mixed                             |
| `_modal.scss`         | Modal tokens                                                                      | Mixed                             |
| `_popover.scss`       | Popover tokens                                                                    | Mixed                             |
| `_progress.scss`      | Progress bar tokens                                                               | Mixed                             |
| `_file-uploader.scss` | File uploader tokens                                                              | Mixed                             |

### Design Decision: SASS vs CSS Custom Properties

| Use                                      | Type                      | Reason                                     |
| ---------------------------------------- | ------------------------- | ------------------------------------------ |
| Typography, Spacing, Radius, Shadows     | **SASS variables**        | Static — don't change between themes       |
| Colors, Surfaces, Borders, Actions, Text | **CSS custom properties** | Dynamic — change between light/dark themes |

```scss
// ✅ Correct: static value → SASS variable
padding: $tutor-spacing-6;
font-size: $tutor-font-size-p1;
border-radius: $tutor-radius-lg;

// ✅ Correct: theme-aware value → CSS custom property
color: $tutor-text-primary; // resolves to var(--tutor-text-primary)
background: $tutor-surface-l1; // resolves to var(--tutor-surface-l1)
border-color: $tutor-border-idle; // resolves to var(--tutor-border-idle)
```

---

## Color Tokens

### Primitive Colors (SASS variables)

| Scale     | Variable Pattern        | Steps                                                                |
| --------- | ----------------------- | -------------------------------------------------------------------- |
| Brand     | `$tutor-brand-{step}`   | 100, 200, 300, 400, 500, 600, 700, 800, 900, 950                     |
| Gray      | `$tutor-gray-{step}`    | 1, 10, 25, 50, 100, 200, 300, 400, 500, 600, 700, 750, 800, 900, 950 |
| Success   | `$tutor-success-{step}` | 25–950 (12 steps)                                                    |
| Warning   | `$tutor-warning-{step}` | 25–950 (12 steps)                                                    |
| Error     | `$tutor-error-{step}`   | 25–950 (12 steps)                                                    |
| Yellow    | `$tutor-yellow-{step}`  | 25–950 (12 steps)                                                    |
| Exception | `$tutor-exception-{n}`  | 1, 2, 2-secondary, 2-tertiary, 3, 5, 6                               |

### Semantic Text Colors (CSS custom properties)

| Token                         | Usage                    |
| ----------------------------- | ------------------------ |
| `$tutor-text-primary`         | Main body text           |
| `$tutor-text-primary-inverse` | Text on dark backgrounds |
| `$tutor-text-secondary`       | Secondary/muted text     |
| `$tutor-text-subdued`         | Subdued text             |
| `$tutor-text-brand`           | Brand-colored text       |
| `$tutor-text-success`         | Success messages         |
| `$tutor-text-critical`        | Error/critical messages  |
| `$tutor-text-warning`         | Warning messages         |
| `$tutor-text-disabled`        | Disabled elements        |

### Action Colors (CSS custom properties)

| Token                             | Usage                          |
| --------------------------------- | ------------------------------ |
| `$tutor-actions-brand-primary`    | Primary buttons, active states |
| `$tutor-actions-brand-secondary`  | Secondary brand interactions   |
| `$tutor-actions-brand-tertiary`   | Tertiary brand interactions    |
| `$tutor-actions-success-primary`  | Success buttons                |
| `$tutor-actions-critical-primary` | Delete/destructive buttons     |
| `$tutor-actions-gray-secondary`   | Neutral/gray buttons           |
| `$tutor-actions-inverse`          | Inverse theme interactions     |

### Surface Colors (CSS custom properties)

| Token                          | Usage                     |
| ------------------------------ | ------------------------- |
| `$tutor-surface-base`          | Page background           |
| `$tutor-surface-l1`            | Card/panel backgrounds    |
| `$tutor-surface-l2`            | Elevated surfaces         |
| `$tutor-surface-brand-primary` | Brand-colored surfaces    |
| `$tutor-surface-dark`          | Dark mode surfaces        |
| `$tutor-surface-success`       | Success state backgrounds |
| `$tutor-surface-critical`      | Error state backgrounds   |
| `$tutor-surface-warning`       | Warning state backgrounds |

---

## Typography Tokens

| Token                 | Size            | Line Height     |
| --------------------- | --------------- | --------------- |
| `$tutor-font-size-d1` | 4rem (64px)     | 4.5rem (72px)   |
| `$tutor-font-size-h1` | 2.5rem (40px)   | 3rem (48px)     |
| `$tutor-font-size-h2` | 2rem (32px)     | 2.5rem (40px)   |
| `$tutor-font-size-h3` | 1.5rem (24px)   | 2rem (32px)     |
| `$tutor-font-size-h4` | 1.25rem (20px)  | 1.75rem (28px)  |
| `$tutor-font-size-h5` | 1.125rem (18px) | 1.625rem (26px) |
| `$tutor-font-size-p1` | 1rem (16px)     | 1.375rem (22px) |
| `$tutor-font-size-p2` | 0.875rem (14px) | 1.125rem (18px) |
| `$tutor-font-size-p3` | 0.75rem (12px)  | 1.125rem (18px) |

**Font Weights:**

| Token                         | Value |
| ----------------------------- | ----- |
| `$tutor-font-weight-regular`  | 400   |
| `$tutor-font-weight-medium`   | 500   |
| `$tutor-font-weight-semibold` | 600   |
| `$tutor-font-weight-bold`     | 700   |

**Font Families:** Both heading and body use `'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif`

> All font sizes use `rem` units to support the font scaling feature via `PreferenceService.applyFontScale()`.

---

## Spacing Tokens

| Token                 | Value | Token               | Value |
| --------------------- | ----- | ------------------- | ----- |
| `$tutor-spacing-none` | 0px   | `$tutor-spacing-11` | 48px  |
| `$tutor-spacing-1`    | 2px   | `$tutor-spacing-12` | 56px  |
| `$tutor-spacing-2`    | 4px   | `$tutor-spacing-13` | 64px  |
| `$tutor-spacing-3`    | 6px   | `$tutor-spacing-14` | 72px  |
| `$tutor-spacing-4`    | 8px   | `$tutor-spacing-15` | 80px  |
| `$tutor-spacing-5`    | 12px  | `$tutor-spacing-16` | 88px  |
| `$tutor-spacing-6`    | 16px  | `$tutor-spacing-17` | 96px  |
| `$tutor-spacing-7`    | 20px  | `$tutor-spacing-18` | 104px |
| `$tutor-spacing-8`    | 24px  | `$tutor-spacing-19` | 112px |
| `$tutor-spacing-9`    | 32px  | `$tutor-spacing-20` | 120px |
| `$tutor-spacing-10`   | 40px  | `$tutor-spacing-21` | 200px |

---

## Border Radius Tokens

| Token                | Value  |
| -------------------- | ------ |
| `$tutor-radius-none` | 0px    |
| `$tutor-radius-xs`   | 2px    |
| `$tutor-radius-sm`   | 4px    |
| `$tutor-radius-md`   | 6px    |
| `$tutor-radius-lg`   | 8px    |
| `$tutor-radius-2xl`  | 12px   |
| `$tutor-radius-3xl`  | 16px   |
| `$tutor-radius-4xl`  | 20px   |
| `$tutor-radius-5xl`  | 24px   |
| `$tutor-radius-6xl`  | 32px   |
| `$tutor-radius-full` | 1000px |

---

## Shadow Tokens

| Token                 | Description          |
| --------------------- | -------------------- |
| `$tutor-shadow-xs`    | Subtle shadow (1px)  |
| `$tutor-shadow-sm`    | Small shadow         |
| `$tutor-shadow-md`    | Medium shadow        |
| `$tutor-shadow-lg`    | Large shadow         |
| `$tutor-shadow-xl`    | Extra large shadow   |
| `$tutor-shadow-2xl`   | 2XL shadow           |
| `$tutor-shadow-3xl`   | 3XL shadow           |
| `$tutor-shadow-modal` | Modal overlay shadow |

**Ring Shadows** (focus indicators):

| Token                         | Usage                   |
| ----------------------------- | ----------------------- |
| `$tutor-ring-brand-shadow-xs` | Brand input focus ring  |
| `$tutor-ring-brand-shadow-sm` | Brand button focus ring |
| `$tutor-ring-gray-shadow-md`  | Gray element focus ring |
| `$tutor-ring-gray-shadow-sm`  | Small gray focus ring   |
| `$tutor-error-shadow-xs`      | Error state focus ring  |

---

## Breakpoint Tokens

| Token                   | Value  | Usage          |
| ----------------------- | ------ | -------------- |
| `$tutor-breakpoint-xs`  | 480px  | Small phones   |
| `$tutor-breakpoint-sm`  | 576px  | Large phones   |
| `$tutor-breakpoint-md`  | 768px  | Tablets        |
| `$tutor-breakpoint-lg`  | 992px  | Small desktops |
| `$tutor-breakpoint-xl`  | 1200px | Large desktops |
| `$tutor-breakpoint-2xl` | 1400px | Wide screens   |
| `$tutor-breakpoint-3xl` | 1920px | Ultra-wide     |

---

## Theme System

**Files:** `scss/themes/_light.scss`, `scss/themes/_dark.scss`

Themes are implemented by setting CSS custom properties on `[data-tutor-theme="light"]` and `[data-tutor-theme="dark"]` selectors. All semantic tokens (text colors, surfaces, borders, actions) resolve to different primitive values per theme.

```scss
// themes/_light.scss
[data-tutor-theme='light'] {
  --tutor-text-primary: #{$tutor-gray-950};
  --tutor-surface-base: #{$tutor-gray-50};
  --tutor-surface-l1: #{$tutor-gray-1};
  --tutor-border-idle: #{$tutor-gray-200};
  --tutor-actions-brand-primary: #{$tutor-brand-600};
  // ...
}

// themes/_dark.scss
[data-tutor-theme='dark'] {
  --tutor-text-primary: #{$tutor-gray-50};
  --tutor-surface-base: #{$tutor-gray-950};
  --tutor-surface-l1: #{$tutor-gray-900};
  --tutor-border-idle: #{$tutor-gray-700};
  --tutor-actions-brand-primary: #{$tutor-brand-500};
  // ...
}
```

---

## Mixins

**Directory:** `scss/mixins/` — 11 mixin files

### RTL Mixins (`_rtl.scss`)

All directional properties should use RTL mixins:

```scss
@include margin-start($tutor-spacing-4); // margin-left in LTR, margin-right in RTL
@include margin-end($tutor-spacing-4); // margin-right in LTR, margin-left in RTL
@include padding-start($tutor-spacing-4); // padding-left in LTR, padding-right in RTL
@include padding-end($tutor-spacing-4); // padding-right in LTR, padding-left in RTL
@include border-start(1px solid red); // border-left in LTR, border-right in RTL
@include text-align-start; // text-align: left in LTR, right in RTL
@include inset-inline-start(0); // left: 0 in LTR, right: 0 in RTL
```

### Other Mixin Files

| File                | Key Mixins                                                                                |
| ------------------- | ----------------------------------------------------------------------------------------- |
| `_buttons.scss`     | `tutor-button-base`, `tutor-button-variant($type)`, `tutor-button-size($size)`            |
| `_cards.scss`       | `tutor-card-base`, `tutor-card-elevation($level)`, `tutor-card-radius($size)`             |
| `_inputs.scss`      | `tutor-input-base`, `tutor-input-size($size)`, `tutor-input-validation($state)`           |
| `_layout.scss`      | `tutor-flex`, `tutor-flex-center`, `tutor-grid`, `tutor-grid-columns($n)`                 |
| `_typography.scss`  | Typography component mixins                                                               |
| `_utilities.scss`   | `tutor-visually-hidden`, `tutor-truncate`, `tutor-focus-ring`, `tutor-transition($props)` |
| `_avatars.scss`     | Avatar size mixins                                                                        |
| `_badges.scss`      | Badge variant mixins                                                                      |
| `_paginations.scss` | Pagination style mixins                                                                   |

---

## Component Stylesheets

**Directory:** `scss/components/` — 32 files

| File                      | CSS Prefix                   | Description              |
| ------------------------- | ---------------------------- | ------------------------ |
| `_accordion.scss`         | `.tutor-accordion-*`         | Accordion panels         |
| `_alert.scss`             | `.tutor-alert-*`             | Alert banners            |
| `_attachment-card.scss`   | `.tutor-attachment-*`        | File attachment cards    |
| `_avatar.scss`            | `.tutor-avatar-*`            | User avatars             |
| `_badge.scss`             | `.tutor-badge-*`             | Status badges            |
| `_button.scss`            | `.tutor-btn-*`               | Buttons                  |
| `_calendar.scss`          | `.tutor-calendar-*`          | Date picker calendar     |
| `_card.scss`              | `.tutor-card-*`              | Content cards            |
| `_file-uploader.scss`     | `.tutor-file-uploader-*`     | File upload zones        |
| `_input.scss`             | `.tutor-input-*`             | Form inputs              |
| `_loading-spinner.scss`   | `.tutor-loading-*`           | Loading spinners         |
| `_modal.scss`             | `.tutor-modal-*`             | Modal dialogs            |
| `_nav.scss`               | `.tutor-nav-*`               | Navigation               |
| `_pagination.scss`        | `.tutor-pagination-*`        | Pagination controls      |
| `_popover.scss`           | `.tutor-popover-*`           | Popovers                 |
| `_preview-trigger.scss`   | `.tutor-preview-*`           | Preview triggers         |
| `_progress.scss`          | `.tutor-progress-*`          | Progress bars            |
| `_result-badge.scss`      | `.tutor-result-badge-*`      | Result/score badges      |
| `_section-separator.scss` | `.tutor-section-separator-*` | Section dividers         |
| `_select.scss`            | `.tutor-select-*`            | Select dropdowns         |
| `_select-dropdown.scss`   | `.tutor-select-dropdown-*`   | Select dropdown panels   |
| `_skeleton.scss`          | `.tutor-skeleton-*`          | Loading skeletons        |
| `_star-rating.scss`       | `.tutor-star-rating-*`       | Star ratings             |
| `_statics.scss`           | `.tutor-statics-*`           | Static info displays     |
| `_status-select.scss`     | `.tutor-status-select-*`     | Status selectors         |
| `_stepper-dropdown.scss`  | `.tutor-stepper-dropdown-*`  | Number stepper dropdowns |
| `_table.scss`             | `.tutor-table-*`             | Data tables              |
| `_tabs.scss`              | `.tutor-tabs-*`              | Tab navigation           |
| `_time-input.scss`        | `.tutor-time-input-*`        | Time picker inputs       |
| `_toast.scss`             | `.tutor-toast-*`             | Toast notifications      |
| `_tooltip.scss`           | `.tutor-tooltip-*`           | Tooltips                 |

---

## Utility Classes

**Directory:** `scss/utilities/` — 10 files generating utility classes from token maps.

### Spacing Utilities (`_spacing.scss`)

Pattern: `.tutor-{property}-{size}`

| Prefix      | Property                         |
| ----------- | -------------------------------- |
| `tutor-m-`  | margin (all sides)               |
| `tutor-mt-` | margin-top                       |
| `tutor-mb-` | margin-bottom                    |
| `tutor-ms-` | margin-inline-start (RTL-aware)  |
| `tutor-me-` | margin-inline-end (RTL-aware)    |
| `tutor-mx-` | margin horizontal                |
| `tutor-my-` | margin vertical                  |
| `tutor-p-`  | padding (all sides)              |
| `tutor-pt-` | padding-top                      |
| `tutor-pb-` | padding-bottom                   |
| `tutor-ps-` | padding-inline-start (RTL-aware) |
| `tutor-pe-` | padding-inline-end (RTL-aware)   |
| `tutor-px-` | padding horizontal               |
| `tutor-py-` | padding vertical                 |

Sizes: `none`, `1`–`21` (mapping to the spacing scale above)

### Color Utilities (`_colors.scss`)

**Text colors:** `.tutor-color-{name}` — e.g. `.tutor-color-primary`, `.tutor-color-brand`

**Background colors:** `.tutor-bg-{category}-{step}` — e.g. `.tutor-bg-brand-600`, `.tutor-bg-gray-100`

**Surface backgrounds:** `.tutor-bg-{surface}` — e.g. `.tutor-bg-base`, `.tutor-bg-l1`

**Action backgrounds:** `.tutor-bg-action-{name}` — e.g. `.tutor-bg-action-brand-primary`

### Typography Utilities (`_typography.scss`)

**Font size:** `.tutor-fs-{name}` — e.g. `.tutor-fs-h1`, `.tutor-fs-p2`

**Font weight:** `.tutor-fw-{name}` — e.g. `.tutor-fw-medium`, `.tutor-fw-bold`

**Text alignment:** `.tutor-text-{align}` — e.g. `.tutor-text-center`, `.tutor-text-start`

### Layout Utilities (`_layout.scss`)

**Display:** `.tutor-d-{value}` — e.g. `.tutor-d-flex`, `.tutor-d-none`, `.tutor-d-grid`

**Flexbox:** `.tutor-justify-{value}`, `.tutor-align-{value}`, `.tutor-flex-{value}`

**Gap:** `.tutor-gap-{size}` — using spacing scale

### Border Utilities (`_borders.scss`)

**Border color:** `.tutor-border-{name}` — e.g. `.tutor-border-idle`, `.tutor-border-brand`

**Border radius:** `.tutor-radius-{size}` — e.g. `.tutor-radius-lg`, `.tutor-radius-full`

### Transform & Transition Utilities

**Transform:** `.tutor-rotate-{deg}`, `.tutor-scale-{value}`

**Transition:** `.tutor-transition`, `.tutor-transition-fast`, `.tutor-transition-slow`
