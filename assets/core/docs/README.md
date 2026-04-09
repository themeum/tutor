# Tutor Core Library — Documentation

> Complete API reference and developer guide for the Tutor Design System core library.

## Table of Contents

| Document                                 | Description                                                                                           |
| ---------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| [Architecture](./architecture.md)        | System architecture, registry pattern, and initialization flow                                        |
| [Components](./components.md)            | All Alpine.js UI components — Modal, Tabs, Accordion, Select, Tooltip, Popover, Toast, Form, and more |
| [Services](./services.md)                | Global services — Query, Toast, Modal, Form, WPMedia, Preference                                      |
| [Utilities](./utilities.md)              | Security helpers, nonce utilities, and constants                                                      |
| [SCSS & Design Tokens](./scss-tokens.md) | SCSS token system, theme structure, and component styles                                              |

---

## Quick Overview

The Tutor Core Library is a standalone design system providing:

- **23 Alpine.js components** registered via a centralized `ComponentRegistry`
- **7 global services** exposed on `window.TutorCore`
- **Comprehensive SCSS token system** with 19 token files and 32 component stylesheets
- **Full TypeScript support** with strict typing (no `any` in public APIs)
- **RTL-first design** — all components and utilities adapt to `dir="rtl"` automatically

### Tech Stack

| Layer        | Technology                                                                                    |
| ------------ | --------------------------------------------------------------------------------------------- |
| JS Framework | [Alpine.js](https://alpinejs.dev/) v3 with `@alpinejs/collapse` and `@alpinejs/focus` plugins |
| Language     | TypeScript (strict mode)                                                                      |
| Styling      | SCSS with CSS custom properties for theming                                                   |
| Build        | Webpack / Rollup (consumed by Tutor LMS build pipeline)                                       |

### Global Namespace

After initialization, the library exposes:

```
window.Alpine          — Alpine.js instance
window.TutorCore       — Services + globally-exposed components
window.TutorComponentRegistry — The registry singleton
```
