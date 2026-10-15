# Tutor CSS Utility Classes Documentation

A comprehensive guide to all utility classes available in the Tutor design system. These utilities provide a consistent, scalable approach to styling with built-in RTL support and responsive design.

## Table of Contents

1. [Layout Utilities](#layout-utilities)
2. [Spacing Utilities](#spacing-utilities)
3. [Typography Utilities](#typography-utilities)
4. [Color Utilities](#color-utilities)
5. [Sizing Utilities](#sizing-utilities)
6. [Border Utilities](#border-utilities)
7. [RTL Utilities](#rtl-utilities)
8. [Z-Index Utilities](#z-index-utilities)
9. [Responsive Design](#responsive-design)
10. [Design Tokens](#design-tokens)

---

## Layout Utilities

### Display

Control the display behavior of elements.

```css
.tutor-block          /* display: block */
.tutor-inline-block   /* display: inline-block */
.tutor-inline         /* display: inline */
.tutor-flex           /* display: flex */
.tutor-inline-flex    /* display: inline-flex */
.tutor-grid           /* display: grid */
.tutor-inline-grid    /* display: inline-grid */
.tutor-hidden         /* display: none */
```

### Flexbox

#### Direction (RTL-aware)

```css
.tutor-flex-row       /* flex-direction: row (RTL: row-reverse) */
.tutor-flex-row-reverse /* flex-direction: row-reverse (RTL: row) */
.tutor-flex-col       /* flex-direction: column */
.tutor-flex-col-reverse /* flex-direction: column-reverse */
```

#### Wrap

```css
.tutor-flex-wrap      /* flex-wrap: wrap */
.tutor-flex-wrap-reverse /* flex-wrap: wrap-reverse */
.tutor-flex-nowrap    /* flex-wrap: nowrap */
```

#### Flex Properties

```css
.tutor-flex-1         /* flex: 1 1 0% */
.tutor-flex-auto      /* flex: 1 1 auto */
.tutor-flex-initial   /* flex: 0 1 auto */
.tutor-flex-none      /* flex: none */
.tutor-grow           /* flex-grow: 1 */
.tutor-grow-0         /* flex-grow: 0 */
.tutor-shrink         /* flex-shrink: 1 */
.tutor-shrink-0       /* flex-shrink: 0 */
```

#### Justify Content (RTL-aware)

```css
.tutor-justify-start    /* justify-content: flex-start (RTL: flex-end) */
.tutor-justify-end      /* justify-content: flex-end (RTL: flex-start) */
.tutor-justify-center   /* justify-content: center */
.tutor-justify-between  /* justify-content: space-between */
.tutor-justify-around   /* justify-content: space-around */
.tutor-justify-evenly   /* justify-content: space-evenly */
```

#### Align Items

```css
.tutor-items-start     /* align-items: flex-start */
.tutor-items-end       /* align-items: flex-end */
.tutor-items-center    /* align-items: center */
.tutor-items-baseline  /* align-items: baseline */
.tutor-items-stretch   /* align-items: stretch */
```

#### Align Content

```css
.tutor-content-start   /* align-content: flex-start */
.tutor-content-end     /* align-content: flex-end */
.tutor-content-center  /* align-content: center */
.tutor-content-between /* align-content: space-between */
.tutor-content-around  /* align-content: space-around */
.tutor-content-evenly  /* align-content: space-evenly */
```

#### Align Self

```css
.tutor-self-auto      /* align-self: auto */
.tutor-self-start     /* align-self: flex-start */
.tutor-self-end       /* align-self: flex-end */
.tutor-self-center    /* align-self: center */
.tutor-self-stretch   /* align-self: stretch */
.tutor-self-baseline  /* align-self: baseline */
```

### Gap

Control spacing between flex and grid items.

```css
.tutor-gap-{size}     /* gap: {size} */
.tutor-gap-x-{size}   /* column-gap: {size} */
.tutor-gap-y-{size}   /* row-gap: {size} */
```

Available sizes: `none`, `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8`, `9`, `10`, `11`, `12`, `13`, `14`, `15`, `16`, `17`, `18`, `19`, `20`, `21`

### Grid

#### Grid Template Columns

```css
.tutor-grid-cols-1    /* grid-template-columns: repeat(1, minmax(0, 1fr)) */
.tutor-grid-cols-2    /* grid-template-columns: repeat(2, minmax(0, 1fr)) */
.tutor-grid-cols-3    /* grid-template-columns: repeat(3, minmax(0, 1fr)) */
.tutor-grid-cols-4    /* grid-template-columns: repeat(4, minmax(0, 1fr)) */
.tutor-grid-cols-5    /* grid-template-columns: repeat(5, minmax(0, 1fr)) */
.tutor-grid-cols-6    /* grid-template-columns: repeat(6, minmax(0, 1fr)) */
.tutor-grid-cols-12   /* grid-template-columns: repeat(12, minmax(0, 1fr)) */
.tutor-grid-cols-none /* grid-template-columns: none */
```

#### Grid Column Span

```css
.tutor-col-auto       /* grid-column: auto */
.tutor-col-span-1     /* grid-column: span 1 / span 1 */
.tutor-col-span-2     /* grid-column: span 2 / span 2 */
.tutor-col-span-3     /* grid-column: span 3 / span 3 */
.tutor-col-span-4     /* grid-column: span 4 / span 4 */
.tutor-col-span-5     /* grid-column: span 5 / span 5 */
.tutor-col-span-6     /* grid-column: span 6 / span 6 */
.tutor-col-span-full  /* grid-column: 1 / -1 */
```

### Positioning

```css
.tutor-static        /* position: static */
.tutor-fixed         /* position: fixed */
.tutor-absolute      /* position: absolute */
.tutor-relative      /* position: relative */
.tutor-sticky        /* position: sticky */
```

#### Position Values (RTL-aware)

```css
.tutor-inset-0       /* top: 0; right: 0; bottom: 0; left: 0 */
.tutor-inset-auto    /* top: auto; right: auto; bottom: auto; left: auto */
.tutor-top-0         /* top: 0 */
.tutor-right-0       /* inset-inline-end: 0 */
.tutor-bottom-0      /* bottom: 0 */
.tutor-left-0        /* inset-inline-start: 0 */
.tutor-top-auto      /* top: auto */
.tutor-right-auto    /* inset-inline-end: auto */
.tutor-bottom-auto   /* bottom: auto */
.tutor-left-auto     /* inset-inline-start: auto */
```

### Overflow

```css
.tutor-overflow-auto      /* overflow: auto */
.tutor-overflow-hidden    /* overflow: hidden */
.tutor-overflow-visible   /* overflow: visible */
.tutor-overflow-scroll    /* overflow: scroll */
.tutor-overflow-x-auto    /* overflow-x: auto */
.tutor-overflow-y-auto    /* overflow-y: auto */
.tutor-overflow-x-hidden  /* overflow-x: hidden */
.tutor-overflow-y-hidden  /* overflow-y: hidden */
.tutor-overflow-x-visible /* overflow-x: visible */
.tutor-overflow-y-visible /* overflow-y: visible */
.tutor-overflow-x-scroll  /* overflow-x: scroll */
.tutor-overflow-y-scroll  /* overflow-y: scroll */
```

---

## Spacing Utilities

### Margin (RTL-aware)

```css
.tutor-m-{size}      /* margin: {size} */
.tutor-mt-{size}     /* margin-top: {size} */
.tutor-mr-{size}     /* margin-inline-end: {size} */
.tutor-mb-{size}     /* margin-bottom: {size} */
.tutor-ml-{size}     /* margin-inline-start: {size} */
.tutor-mx-{size}     /* margin-inline: {size} */
.tutor-my-{size}     /* margin-top: {size}; margin-bottom: {size} */
```

### Padding (RTL-aware)

```css
.tutor-p-{size}      /* padding: {size} */
.tutor-pt-{size}     /* padding-top: {size} */
.tutor-pr-{size}     /* padding-inline-end: {size} */
.tutor-pb-{size}     /* padding-bottom: {size} */
.tutor-pl-{size}     /* padding-inline-start: {size} */
.tutor-px-{size}     /* padding-left: {size}; padding-right: {size} */
.tutor-py-{size}     /* padding-top: {size}; padding-bottom: {size} */
```

### Negative Margins

```css
.-tutor-m-{size}     /* margin: -{size} */
.-tutor-mt-{size}    /* margin-top: -{size} */
.-tutor-mr-{size}    /* margin-inline-end: -{size} */
.-tutor-mb-{size}    /* margin-bottom: -{size} */
.-tutor-ml-{size}    /* margin-inline-start: -{size} */
.-tutor-mx-{size}    /* margin-left: -{size}; margin-right: -{size} */
.-tutor-my-{size}    /* margin-top: -{size}; margin-bottom: -{size} */
```

### Auto Margins

```css
.tutor-m-auto        /* margin: auto */
.tutor-mt-auto       /* margin-top: auto */
.tutor-mr-auto       /* margin-inline-end: auto */
.tutor-mb-auto       /* margin-bottom: auto */
.tutor-ml-auto       /* margin-inline-start: auto */
.tutor-mx-auto       /* margin-left: auto; margin-right: auto */
.tutor-my-auto       /* margin-top: auto; margin-bottom: auto */
```

### Spacing Scale

| Class  | Value | Pixels |
| ------ | ----- | ------ |
| `none` | 0px   | 0px    |
| `1`    | 2px   | 2px    |
| `2`    | 4px   | 4px    |
| `3`    | 6px   | 6px    |
| `4`    | 8px   | 8px    |
| `5`    | 12px  | 12px   |
| `6`    | 16px  | 16px   |
| `7`    | 20px  | 20px   |
| `8`    | 24px  | 24px   |
| `9`    | 32px  | 32px   |
| `10`   | 40px  | 40px   |
| `11`   | 48px  | 48px   |
| `12`   | 56px  | 56px   |
| `13`   | 64px  | 64px   |
| `14`   | 72px  | 72px   |
| `15`   | 80px  | 80px   |
| `16`   | 88px  | 88px   |
| `17`   | 96px  | 96px   |
| `18`   | 104px | 104px  |
| `19`   | 112px | 112px  |
| `20`   | 120px | 120px  |
| `21`   | 200px | 200px  |

---

## Typography Utilities

### Semantic Typography Classes

```css
.tutor-h1            /* Heading 1: 40px/48px, Bold */
.tutor-h2            /* Heading 2: 32px/40px, Bold */
.tutor-h3            /* Heading 3: 24px/32px, Semi Bold */
.tutor-h4            /* Heading 4: 20px/28px, Semi Bold */
.tutor-h5            /* Heading 5: 18px/26px, Medium */
.tutor-p1            /* Paragraph 1: 16px/22px */
.tutor-p2            /* Paragraph 2: 14px/18px */
.tutor-p3            /* Paragraph 3: 12px/18px */
```

### Font Sizes

```css
.tutor-text-h1       /* 2.5rem (40px) */
.tutor-text-h2       /* 2rem (32px) */
.tutor-text-h3       /* 1.5rem (24px) */
.tutor-text-h4       /* 1.25rem (20px) */
.tutor-text-h5       /* 1.125rem (18px) */
.tutor-text-medium   /* 1rem (16px) */
.tutor-text-p1       /* 1rem (16px) */
.tutor-text-small    /* 0.875rem (14px) */
.tutor-text-p2       /* 0.875rem (14px) */
.tutor-text-tiny     /* 0.75rem (12px) */
.tutor-text-p3       /* 0.75rem (12px) */
```

### Font Weights

```css
.tutor-font-regular     /* font-weight: 400 */
.tutor-font-medium      /* font-weight: 500 */
.tutor-font-semi-strong /* font-weight: 600 */
.tutor-font-strong      /* font-weight: 700 */
```

### Text Alignment (RTL-aware)

```css
.tutor-text-left     /* text-align: left (RTL: right) */
.tutor-text-center   /* text-align: center */
.tutor-text-right    /* text-align: right (RTL: left) */
.tutor-text-justify  /* text-align: justify */
.tutor-text-start    /* text-align: start */
.tutor-text-end      /* text-align: end */
```

### Text Colors

#### Basic Colors

```css
.tutor-text-primary     /* Primary text color */
.tutor-text-secondary   /* Secondary text color */
.tutor-text-tertiary    /* Tertiary text color */
.tutor-text-disabled    /* Disabled text color */
.tutor-text-brand       /* Brand text color */
.tutor-text-inverse     /* Inverse text color */
.tutor-text-success     /* Success text color */
.tutor-text-warning     /* Warning text color */
.tutor-text-error       /* Error text color */
```

#### Brand Colors

```css
.tutor-text-brand-100   /* Lightest brand color */
.tutor-text-brand-200
.tutor-text-brand-300
.tutor-text-brand-400
.tutor-text-brand-500   /* Default brand color */
.tutor-text-brand-600
.tutor-text-brand-700
.tutor-text-brand-800
.tutor-text-brand-900
.tutor-text-brand-950   /* Darkest brand color */
```

#### Gray Colors

```css
.tutor-text-gray-1      /* White */
.tutor-text-gray-10
.tutor-text-gray-25
.tutor-text-gray-50
.tutor-text-gray-100
.tutor-text-gray-200
.tutor-text-gray-300
.tutor-text-gray-400
.tutor-text-gray-500
.tutor-text-gray-600
.tutor-text-gray-700
.tutor-text-gray-750
.tutor-text-gray-800
.tutor-text-gray-900
.tutor-text-gray-950    /* Darkest gray */
```

### Text Decoration

```css
.tutor-underline        /* text-decoration: underline */
.tutor-line-through     /* text-decoration: line-through */
.tutor-no-underline     /* text-decoration: none */
```

### Text Transform

```css
.tutor-uppercase        /* text-transform: uppercase */
.tutor-lowercase        /* text-transform: lowercase */
.tutor-capitalize       /* text-transform: capitalize */
.tutor-normal-case      /* text-transform: none */
```

### Text Overflow

```css
.tutor-truncate         /* Truncate with ellipsis */
.tutor-text-ellipsis    /* text-overflow: ellipsis */
.tutor-text-clip        /* text-overflow: clip */
```

### Line Clamp

```css
.tutor-line-clamp-1     /* Clamp to 1 line */
.tutor-line-clamp-2     /* Clamp to 2 lines */
.tutor-line-clamp-3     /* Clamp to 3 lines */
.tutor-line-clamp-4     /* Clamp to 4 lines */
.tutor-line-clamp-5     /* Clamp to 5 lines */
.tutor-line-clamp-6     /* Clamp to 6 lines */
```

### White Space

```css
.tutor-whitespace-normal   /* white-space: normal */
.tutor-whitespace-nowrap   /* white-space: nowrap */
.tutor-whitespace-pre      /* white-space: pre */
.tutor-whitespace-pre-line /* white-space: pre-line */
.tutor-whitespace-pre-wrap /* white-space: pre-wrap */
```

### Word Break

```css
.tutor-break-normal     /* overflow-wrap: normal; word-break: normal */
.tutor-break-words      /* overflow-wrap: break-word */
.tutor-break-all        /* word-break: break-all */
```

### Letter Spacing

```css
.tutor-tracking-tighter /* letter-spacing: -0.05em */
.tutor-tracking-tight   /* letter-spacing: -0.025em */
.tutor-tracking-normal  /* letter-spacing: 0em */
.tutor-tracking-wide    /* letter-spacing: 0.025em */
.tutor-tracking-wider   /* letter-spacing: 0.05em */
.tutor-tracking-widest  /* letter-spacing: 0.1em */
```

### Line Height

```css
.tutor-leading-none     /* line-height: 1 */
.tutor-leading-tight    /* line-height: 1.25 */
.tutor-leading-snug     /* line-height: 1.375 */
.tutor-leading-normal   /* line-height: 1.5 */
.tutor-leading-relaxed  /* line-height: 1.625 */
.tutor-leading-loose    /* line-height: 2 */
```

### List Styles

```css
.tutor-list-none        /* list-style-type: none */
.tutor-list-disc        /* list-style-type: disc */
.tutor-list-decimal     /* list-style-type: decimal */
.tutor-list-inside      /* list-style-position: inside */
.tutor-list-outside     /* list-style-position: outside */
```

---

## Color Utilities

### Background Colors

#### Basic Backgrounds

```css
.tutor-bg-transparent      /* background-color: transparent */
.tutor-bg-current          /* background-color: currentColor */
.tutor-bg-surface-base     /* Base surface color */
.tutor-bg-surface-l1       /* Level 1 surface color */
.tutor-bg-surface-l2       /* Level 2 surface color */
.tutor-bg-surface-elevated /* Elevated surface color */
.tutor-bg-primary          /* Primary button color */
.tutor-bg-secondary        /* Secondary button color */
.tutor-bg-success          /* Success color */
.tutor-bg-success-light    /* Light success color */
.tutor-bg-warning          /* Warning color */
.tutor-bg-warning-light    /* Light warning color */
.tutor-bg-error            /* Error color */
.tutor-bg-error-light      /* Light error color */
```

#### Brand Backgrounds

```css
.tutor-bg-brand-100        /* Lightest brand background */
.tutor-bg-brand-200
.tutor-bg-brand-300
.tutor-bg-brand-400
.tutor-bg-brand-500        /* Default brand background */
.tutor-bg-brand-600
.tutor-bg-brand-700
.tutor-bg-brand-800
.tutor-bg-brand-900
.tutor-bg-brand-950        /* Darkest brand background */
```

#### Gray Backgrounds

```css
.tutor-bg-gray-1           /* White background */
.tutor-bg-gray-10
.tutor-bg-gray-25
.tutor-bg-gray-50
.tutor-bg-gray-100
.tutor-bg-gray-200
.tutor-bg-gray-300
.tutor-bg-gray-400
.tutor-bg-gray-500
.tutor-bg-gray-600
.tutor-bg-gray-700
.tutor-bg-gray-750
.tutor-bg-gray-800
.tutor-bg-gray-900
.tutor-bg-gray-950         /* Darkest gray background */
```

### Border Colors

```css
.tutor-border-transparent  /* border-color: transparent */
.tutor-border-current      /* border-color: currentColor */
.tutor-border-idle         /* Default border color */
.tutor-border-hover        /* Hover border color */
.tutor-border-focus        /* Focus border color */
.tutor-border-brand        /* Brand border color */
.tutor-border-success      /* Success border color */
.tutor-border-warning      /* Warning border color */
.tutor-border-error        /* Error border color */
```

#### Brand Border Colors

```css
.tutor-border-brand-100    /* Lightest brand border */
.tutor-border-brand-200
.tutor-border-brand-300
.tutor-border-brand-400
.tutor-border-brand-500    /* Default brand border */
.tutor-border-brand-600
.tutor-border-brand-700
.tutor-border-brand-800
.tutor-border-brand-900
.tutor-border-brand-950    /* Darkest brand border */
```

### Shadows

```css
.tutor-shadow-none         /* box-shadow: none */
.tutor-shadow-sm           /* Small shadow */
.tutor-shadow              /* Default shadow */
.tutor-shadow-md           /* Medium shadow */
.tutor-shadow-lg           /* Large shadow */
.tutor-shadow-xl           /* Extra large shadow */
```

### Opacity

```css
.tutor-opacity-0           /* opacity: 0 */
.tutor-opacity-25          /* opacity: 0.25 */
.tutor-opacity-50          /* opacity: 0.5 */
.tutor-opacity-75          /* opacity: 0.75 */
.tutor-opacity-100         /* opacity: 1 */
```

### Hover States

```css
.tutor-hover-bg-primary:hover     /* Hover primary background */
.tutor-hover-bg-secondary:hover   /* Hover secondary background */
.tutor-hover-bg-surface-l2:hover  /* Hover surface background */
.tutor-hover-text-primary:hover   /* Hover primary text */
.tutor-hover-text-brand:hover     /* Hover brand text */
.tutor-hover-border-brand:hover   /* Hover brand border */
.tutor-hover-shadow-md:hover      /* Hover medium shadow */
.tutor-hover-shadow-lg:hover      /* Hover large shadow */
```

### Focus States

```css
.tutor-focus-border-brand:focus   /* Focus brand border */
.tutor-focus-ring:focus           /* Focus ring */
.tutor-focus-outline-none:focus   /* Remove focus outline */
```

---

## Sizing Utilities

### Width

#### Basic Widths

```css
.tutor-w-0             /* width: 0 */
.tutor-w-auto          /* width: auto */
.tutor-w-full          /* width: 100% */
.tutor-w-screen        /* width: 100vw */
.tutor-w-min           /* width: min-content */
.tutor-w-max           /* width: max-content */
.tutor-w-fit           /* width: fit-content */
```

#### Fractional Widths

```css
.tutor-w-1\/2          /* width: 50% */
.tutor-w-1\/3          /* width: 33.333333% */
.tutor-w-2\/3          /* width: 66.666667% */
.tutor-w-1\/4          /* width: 25% */
.tutor-w-2\/4          /* width: 50% */
.tutor-w-3\/4          /* width: 75% */
.tutor-w-1\/5          /* width: 20% */
.tutor-w-2\/5          /* width: 40% */
.tutor-w-3\/5          /* width: 60% */
.tutor-w-4\/5          /* width: 80% */
.tutor-w-1\/6          /* width: 16.666667% */
.tutor-w-2\/6          /* width: 33.333333% */
.tutor-w-3\/6          /* width: 50% */
.tutor-w-4\/6          /* width: 66.666667% */
.tutor-w-5\/6          /* width: 83.333333% */
```

#### Fixed Widths (using spacing scale)

```css
.tutor-w-{size}        /* width: {size} */
```

#### Additional Fixed Widths

```css
.tutor-w-16            /* width: 64px */
.tutor-w-20            /* width: 80px */
.tutor-w-24            /* width: 96px */
.tutor-w-32            /* width: 128px */
.tutor-w-40            /* width: 160px */
.tutor-w-48            /* width: 192px */
.tutor-w-56            /* width: 224px */
.tutor-w-64            /* width: 256px */
.tutor-w-72            /* width: 288px */
.tutor-w-80            /* width: 320px */
.tutor-w-96            /* width: 384px */
```

### Height

#### Basic Heights

```css
.tutor-h-0             /* height: 0 */
.tutor-h-auto          /* height: auto */
.tutor-h-full          /* height: 100% */
.tutor-h-screen        /* height: 100vh */
.tutor-h-min           /* height: min-content */
.tutor-h-max           /* height: max-content */
.tutor-h-fit           /* height: fit-content */
```

#### Fixed Heights (using spacing scale)

```css
.tutor-h-{size}        /* height: {size} */
```

### Min/Max Width

```css
.tutor-min-w-0         /* min-width: 0 */
.tutor-min-w-full      /* min-width: 100% */
.tutor-min-w-min       /* min-width: min-content */
.tutor-min-w-max       /* min-width: max-content */
.tutor-min-w-fit       /* min-width: fit-content */

.tutor-max-w-0         /* max-width: 0 */
.tutor-max-w-none      /* max-width: none */
.tutor-max-w-xs        /* max-width: 320px */
.tutor-max-w-sm        /* max-width: 384px */
.tutor-max-w-md        /* max-width: 448px */
.tutor-max-w-lg        /* max-width: 512px */
.tutor-max-w-xl        /* max-width: 576px */
.tutor-max-w-2xl       /* max-width: 672px */
.tutor-max-w-3xl       /* max-width: 768px */
.tutor-max-w-4xl       /* max-width: 896px */
.tutor-max-w-5xl       /* max-width: 1024px */
.tutor-max-w-6xl       /* max-width: 1152px */
.tutor-max-w-7xl       /* max-width: 1280px */
.tutor-max-w-full      /* max-width: 100% */
.tutor-max-w-prose     /* max-width: 65ch */
```

### Min/Max Height

```css
.tutor-min-h-0         /* min-height: 0 */
.tutor-min-h-full      /* min-height: 100% */
.tutor-min-h-screen    /* min-height: 100vh */
.tutor-min-h-min       /* min-height: min-content */
.tutor-min-h-max       /* min-height: max-content */
.tutor-min-h-fit       /* min-height: fit-content */

.tutor-max-h-0         /* max-height: 0 */
.tutor-max-h-full      /* max-height: 100% */
.tutor-max-h-screen    /* max-height: 100vh */
.tutor-max-h-min       /* max-height: min-content */
.tutor-max-h-max       /* max-height: max-content */
.tutor-max-h-fit       /* max-height: fit-content */
```

---

## Border Utilities

### Border Width & Style

#### Basic Borders (with smart defaults)

```css
.tutor-border          /* border: 1px solid var(--tutor-border-idle) */
.tutor-border-0        /* border: none */
.tutor-border-2        /* border: 2px solid var(--tutor-border-idle) */
.tutor-border-4        /* border: 4px solid var(--tutor-border-idle) */
.tutor-border-8        /* border: 8px solid var(--tutor-border-idle) */
```

#### Directional Borders (RTL-aware)

```css
.tutor-border-t        /* border-top: 1px solid var(--tutor-border-idle) */
.tutor-border-r        /* border-inline-end: 1px solid var(--tutor-border-idle) */
.tutor-border-b        /* border-bottom: 1px solid var(--tutor-border-idle) */
.tutor-border-l        /* border-inline-start: 1px solid var(--tutor-border-idle) */

.tutor-border-t-0      /* border-top: none */
.tutor-border-r-0      /* border-inline-end: none */
.tutor-border-b-0      /* border-bottom: none */
.tutor-border-l-0      /* border-inline-start: none */

.tutor-border-t-2      /* border-top: 2px solid var(--tutor-border-idle) */
.tutor-border-r-2      /* border-inline-end: 2px solid var(--tutor-border-idle) */
.tutor-border-b-2      /* border-bottom: 2px solid var(--tutor-border-idle) */
.tutor-border-l-2      /* border-inline-start: 2px solid var(--tutor-border-idle) */
```

#### Border Styles

```css
.tutor-border-solid    /* border-style: solid */
.tutor-border-dashed   /* border-style: dashed */
.tutor-border-dotted   /* border-style: dotted */
.tutor-border-double   /* border-style: double */
.tutor-border-none     /* border-style: none */
```

### Border Radius (RTL-aware)

#### All Corners

```css
.tutor-rounded-{size}  /* border-radius: {size} */
```

#### Directional Radius

```css
.tutor-rounded-t-{size}  /* border-start-start-radius & border-start-end-radius */
.tutor-rounded-b-{size}  /* border-end-end-radius & border-end-start-radius */
.tutor-rounded-r-{size}  /* border-start-end-radius & border-end-end-radius */
.tutor-rounded-l-{size}  /* border-start-start-radius & border-end-start-radius */
```

#### Individual Corners

```css
.tutor-rounded-tl-{size} /* border-start-start-radius */
.tutor-rounded-tr-{size} /* border-start-end-radius */
.tutor-rounded-br-{size} /* border-end-end-radius */
.tutor-rounded-bl-{size} /* border-end-start-radius */
```

### Semantic Border Utilities

```css
.tutor-border-card      /* Card border with radius */
.tutor-border-input     /* Input border with focus state */
.tutor-border-divider   /* Bottom border divider */
.tutor-border-accent    /* Accent border (inline-start) */
```

---

## RTL Utilities

### Logical Direction Utilities

```css
.tutor-text-start      /* text-align: start */
.tutor-text-end        /* text-align: end */
.tutor-float-start     /* float: inline-start */
.tutor-float-end       /* float: inline-end */
```

### Logical Spacing

#### Margin

```css
.tutor-ms-{size}       /* margin-inline-start: {size} */
.tutor-me-{size}       /* margin-inline-end: {size} */
```

#### Padding

```css
.tutor-ps-{size}       /* padding-inline-start: {size} */
.tutor-pe-{size}       /* padding-inline-end: {size} */
```

### Logical Positioning

```css
.tutor-start-0         /* inset-inline-start: 0 */
.tutor-end-0           /* inset-inline-end: 0 */
```

### Logical Borders

```css
.tutor-border-start    /* border-inline-start: 1px solid var(--tutor-border-idle) */
.tutor-border-end      /* border-inline-end: 1px solid var(--tutor-border-idle) */
```

### Component Utilities

```css
.tutor-icon-start      /* Icon at start position */
.tutor-icon-end        /* Icon at end position */
.tutor-dropdown-start  /* Dropdown positioned at start */
.tutor-dropdown-end    /* Dropdown positioned at end */
.tutor-sidebar-start   /* Sidebar positioned at start */
.tutor-sidebar-end     /* Sidebar positioned at end */
.tutor-toast-start     /* Toast positioned at start */
.tutor-toast-end       /* Toast positioned at end */
```

### Language-Specific Classes

```css
.tutor-lang-ar         /* Arabic: direction: rtl; text-align: right */
.tutor-lang-he         /* Hebrew: direction: rtl; text-align: right */
.tutor-lang-fa         /* Persian: direction: rtl; text-align: right */
.tutor-lang-ur         /* Urdu: direction: rtl; text-align: right */
.tutor-lang-en         /* English: direction: ltr; text-align: left */
.tutor-lang-es         /* Spanish: direction: ltr; text-align: left */
.tutor-lang-fr         /* French: direction: ltr; text-align: left */
.tutor-lang-de         /* German: direction: ltr; text-align: left */
```

---

## Z-Index Utilities

```css
.tutor-z-{level}       /* z-index: {level} */
```

Available z-index levels are defined in the design tokens.

---

## Responsive Design

All utility classes support responsive variants using breakpoint prefixes:

### Breakpoint Prefixes

- `tutor-sm-*` - Small screens and up
- `tutor-md-*` - Medium screens and up
- `tutor-lg-*` - Large screens and up
- `tutor-xl-*` - Extra large screens and up
- `tutor-2xl-*` - 2X large screens and up

### Examples

```css
.tutor-md-flex         /* display: flex on medium screens and up */
.tutor-lg-grid-cols-3  /* 3 columns on large screens and up */
.tutor-sm-text-center  /* Center text on small screens and up */
.tutor-xl-p-8          /* Padding 8 on extra large screens and up */
```

---

## Design Tokens

### Spacing Scale

The spacing system uses a consistent scale from 0px to 200px:

| Token  | Value | Usage                 |
| ------ | ----- | --------------------- |
| `none` | 0px   | No spacing            |
| `1`    | 2px   | Minimal spacing       |
| `2`    | 4px   | Very small spacing    |
| `3`    | 6px   | Small spacing         |
| `4`    | 8px   | Small spacing         |
| `5`    | 12px  | Medium-small spacing  |
| `6`    | 16px  | Medium spacing (base) |
| `7`    | 20px  | Medium spacing        |
| `8`    | 24px  | Medium-large spacing  |
| `9`    | 32px  | Large spacing         |
| `10`   | 40px  | Large spacing         |
| `11`   | 48px  | Extra large spacing   |
| `12`   | 56px  | Extra large spacing   |
| `13`   | 64px  | XXL spacing           |
| `14`   | 72px  | XXL spacing           |
| `15`   | 80px  | XXXL spacing          |
| `16`   | 88px  | XXXL spacing          |
| `17`   | 96px  | XXXL spacing          |
| `18`   | 104px | XXXL spacing          |
| `19`   | 112px | XXXL spacing          |
| `20`   | 120px | XXXL spacing          |
| `21`   | 200px | Maximum spacing       |

### Typography Scale

| Token       | Size | Line Height | Usage               |
| ----------- | ---- | ----------- | ------------------- |
| `h1`        | 40px | 48px        | Main headings       |
| `h2`        | 32px | 40px        | Section headings    |
| `h3`        | 24px | 32px        | Subsection headings |
| `h4`        | 20px | 28px        | Component headings  |
| `h5`        | 18px | 26px        | Small headings      |
| `medium/p1` | 16px | 22px        | Body text           |
| `small/p2`  | 14px | 18px        | Secondary text      |
| `tiny/p3`   | 12px | 18px        | Caption text        |

### Color Palette

#### Brand Colors

- `brand-100` to `brand-950` - Primary brand colors from lightest to darkest
- `brand-500` - Default brand color

#### Gray Colors

- `gray-1` to `gray-950` - Neutral colors from white to near-black
- `gray-600` - Default text color

#### Semantic Colors

- `success-25` to `success-950` - Success states
- `warning-25` to `warning-950` - Warning states
- `error-25` to `error-950` - Error states
- `exception-1` to `exception-6` - Special accent colors

---

## Best Practices

### 1. Use Semantic Classes First

```html
<!-- Good -->
<h1 class="tutor-h1">Main Heading</h1>
<p class="tutor-p1">Body text</p>

<!-- Avoid -->
<h1 class="tutor-text-h1 tutor-font-strong tutor-mb-6">Main Heading</h1>
```

### 2. Leverage RTL-Aware Classes

```html
<!-- Good - automatically adapts to RTL -->
<div class="tutor-ml-4 tutor-text-left">Content with left margin and left-aligned text</div>

<!-- Better - use logical properties -->
<div class="tutor-ms-4 tutor-text-start">Content with start margin and start-aligned text</div>
```

### 3. Use Responsive Variants

```html
<!-- Mobile-first responsive design -->
<div class="tutor-grid tutor-grid-cols-1 tutor-md-grid-cols-2 tutor-lg-grid-cols-3">
  <div>Item 1</div>
  <div>Item 2</div>
  <div>Item 3</div>
</div>
```

### 4. Combine Utilities Effectively

```html
<!-- Card component using utilities -->
<div class="tutor-bg-surface-l1 tutor-border tutor-rounded-lg tutor-p-6 tutor-shadow-sm">
  <h3 class="tutor-h3 tutor-mb-4">Card Title</h3>
  <p class="tutor-p2 tutor-text-secondary">Card content</p>
</div>
```

### 5. Use Consistent Spacing

```html
<!-- Consistent vertical rhythm -->
<article class="tutor-space-y-6">
  <h2 class="tutor-h2">Article Title</h2>
  <p class="tutor-p1">First paragraph</p>
  <p class="tutor-p1">Second paragraph</p>
</article>
```

---

## Migration Guide

### From Custom CSS to Utilities

#### Before

```css
.my-component {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  background-color: white;
  border: 1px solid #e5e5e5;
  border-radius: 8px;
}
```

#### After

```html
<div
  class="tutor-flex tutor-items-center tutor-justify-between tutor-px-8 tutor-py-6 tutor-bg-surface-l1 tutor-border tutor-rounded-lg"
>
  <!-- Component content -->
</div>
```

### RTL Migration

#### Before (Physical Properties)

```css
.component {
  margin-left: 16px;
  text-align: left;
  border-left: 2px solid blue;
}
```

#### After (Logical Properties)

```html
<div class="tutor-ms-6 tutor-text-start tutor-border-l-2 tutor-border-brand-500">
  <!-- Content -->
</div>
```

This utility system provides a comprehensive, consistent, and maintainable approach to styling that scales with your design system and supports modern web standards including RTL languages and responsive design.
