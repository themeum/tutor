# Components

> All Alpine.js UI components registered via `TutorComponentRegistry`.

Each component is used via Alpine's `x-data` directive with the naming convention `tutor` + PascalCase(name).

---

## Modal

**File:** [`ts/components/modal.ts`](../ts/components/modal.ts) &nbsp;|&nbsp; **Directive:** `x-data="tutorModal({ ... })"`

A dialog overlay with focus trapping, ESC-to-close, backdrop transitions, and programmatic open/close via custom events.

### Config

```typescript
interface ModalConfig {
  id: string; // Unique modal identifier (default: 'tutor-modal')
  isCloseable?: boolean; // Allow close via ESC/click-outside (default: true)
  initialOpen?: boolean; // Open on init (default: false)
}
```

### Reactive State

| Property      | Type      | Description                              |
| ------------- | --------- | ---------------------------------------- |
| `open`        | `boolean` | Current visibility state                 |
| `payload`     | `any`     | Data passed when opened programmatically |
| `isCloseable` | `boolean` | Whether close interactions are allowed   |
| `id`          | `string`  | Modal identifier                         |

### Methods

| Method                      | Description                                                |
| --------------------------- | ---------------------------------------------------------- |
| `show()`                    | Opens the modal                                            |
| `close()`                   | Closes the modal and dispatches `tutor-modal-closed` event |
| `getBackdropBindings()`     | Returns Alpine attribute bindings for the backdrop element |
| `getModalBindings()`        | Returns Alpine attribute bindings for the modal wrapper    |
| `getModalContentBindings()` | Returns bindings with focus trap (`x-trap.noscroll.inert`) |
| `getCloseButtonBindings()`  | Returns bindings for the close button                      |

### Usage

```html
<div x-data="tutorModal({ id: 'confirm-dialog', isCloseable: true })">
  <!-- Backdrop -->
  <div x-bind="getBackdropBindings()"></div>

  <!-- Modal wrapper -->
  <div x-bind="getModalBindings()">
    <!-- Content with focus trap -->
    <div x-bind="getModalContentBindings()">
      <h2>Confirm Action</h2>
      <p x-text="payload?.message"></p>
      <button @click="close()">Cancel</button>
    </div>
  </div>
</div>
```

### Programmatic Control

```javascript
// Open from anywhere
TutorCore.modal.showModal('confirm-dialog', { message: 'Are you sure?' });

// Close
TutorCore.modal.closeModal('confirm-dialog');
```

---

## Tabs

**File:** [`ts/components/tabs.ts`](../ts/components/tabs.ts) &nbsp;|&nbsp; **Directive:** `x-data="tutorTabs({ ... })"`

Tab navigation with URL parameter sync and keyboard support.

### Config

```typescript
interface TabsConfig {
  tabs: TabItem[]; // Required: array of tab definitions
  defaultTab?: string; // Initial active tab ID
  orientation?: 'horizontal' | 'vertical';
  size?: 'sm' | 'md' | 'lg'; // Default: 'lg'
  fullWidth?: boolean;
  onChange?: (tabId: string) => void; // Callback on tab change
  urlParams?: {
    enabled?: boolean; // Sync with URL params (default: true)
    paramName?: string; // URL param name (default: 'page_tab')
  };
}

interface TabItem {
  id: string;
  label: string;
  icon?: string;
  disabled?: boolean;
  href?: string; // If set, navigates instead of switching
}
```

### Methods

| Method             | Description                                   |
| ------------------ | --------------------------------------------- |
| `selectTab(tabId)` | Switches to the specified tab                 |
| `isActive(tabId)`  | Returns `true` if the tab is currently active |
| `getTabClass(tab)` | Returns the CSS class string for a tab button |

### Usage

```html
<div
  x-data="tutorTabs({
  tabs: [
    { id: 'overview', label: 'Overview' },
    { id: 'details', label: 'Details' },
    { id: 'reviews', label: 'Reviews', disabled: true }
  ],
  defaultTab: 'overview'
})"
>
  <!-- Tab buttons -->
  <template x-for="tab in tabs" :key="tab.id">
    <button :class="getTabClass(tab)" :disabled="tab.disabled" @click="selectTab(tab.id)" x-text="tab.label"></button>
  </template>

  <!-- Tab panels -->
  <div x-show="isActive('overview')">Overview content</div>
  <div x-show="isActive('details')">Details content</div>
</div>
```

---

## Accordion

**File:** [`ts/components/accordion.ts`](../ts/components/accordion.ts) &nbsp;|&nbsp; **Directive:** `x-data="tutorAccordion({ ... })"`

Expandable panels with single or multi-expand support and full keyboard navigation.

### Config

```typescript
interface AccordionConfig {
  multiple?: boolean; // Allow multiple open panels (default: true)
  defaultOpen?: number[]; // Indices to open on init (default: [])
}
```

### Methods

| Method                        | Description                                            |
| ----------------------------- | ------------------------------------------------------ |
| `toggle(index)`               | Toggle panel open/closed                               |
| `isOpen(index)`               | Check if panel is open                                 |
| `handleKeydown(event, index)` | Keyboard handler (Enter, Space, Arrow keys, Home, End) |
| `focusNext(index)`            | Focus next accordion trigger                           |
| `focusPrevious(index)`        | Focus previous accordion trigger                       |
| `focusFirst()`                | Focus the first trigger                                |
| `focusLast()`                 | Focus the last trigger                                 |

### Usage

```html
<div x-data="tutorAccordion({ multiple: false, defaultOpen: [0] })">
  <div class="tutor-accordion-item">
    <button class="tutor-accordion-trigger" @click="toggle(0)" @keydown="handleKeydown($event, 0)">Panel 1</button>
    <div x-show="isOpen(0)" x-collapse>Panel 1 content...</div>
  </div>
</div>
```

---

## Select

**File:** [`ts/components/select.ts`](../ts/components/select.ts) &nbsp;|&nbsp; **Directive:** `x-data="tutorSelect({ ... })"`

A fully-featured select/dropdown with search, multi-select, grouping, keyboard navigation, and form integration.

### Config

```typescript
interface SelectProps {
  // Data
  options?: SelectOption[];
  groups?: SelectGroup[];
  value?: string | number | (string | number)[];
  defaultValue?: string | number | (string | number)[];

  // Multi-select
  multiple?: boolean;
  maxSelections?: number;

  // Behavior
  searchable?: boolean;
  clearable?: boolean;
  disabled?: boolean;
  loading?: boolean;
  closeOnSelect?: boolean; // Defaults to !multiple

  // Display
  placeholder?: string;
  searchPlaceholder?: string;
  emptyMessage?: string;
  maxHeight?: number; // Default: 280

  // Form integration
  name?: string; // Registers with parent tutorForm
  required?: boolean | string;

  // Callbacks
  onChange?: (value) => void;
  onSearch?: (query) => void | Promise<SelectOption[]>;
  onOpen?: () => void;
  onClose?: () => void;
}

interface SelectOption {
  label: string;
  value: string | number;
  disabled?: boolean;
  icon?: string;
  description?: string;
  group?: string;
}

interface SelectGroup {
  label: string;
  options: SelectOption[];
}
```

### Key Computed Properties

| Property                 | Type             | Description                                       |
| ------------------------ | ---------------- | ------------------------------------------------- |
| `filteredOptions`        | `SelectOption[]` | Options filtered by search query                  |
| `filteredGroups`         | `SelectGroup[]`  | Groups filtered by search query                   |
| `selectedOptions`        | `SelectOption[]` | Currently selected option objects                 |
| `displayValue`           | `string`         | Text shown in the trigger (label or "N selected") |
| `canClear`               | `boolean`        | Whether the clear button should be visible        |
| `isMaxSelectionsReached` | `boolean`        | Multi-select limit reached                        |

### Key Methods

| Method                            | Description                                           |
| --------------------------------- | ----------------------------------------------------- |
| `open()` / `close()` / `toggle()` | Control dropdown visibility                           |
| `selectOption(option)`            | Select or toggle an option                            |
| `deselectOption(option, event?)`  | Remove a selected option (multi-select)               |
| `clear(event?)`                   | Clear all selections                                  |
| `handleSearch(query)`             | Filter options; triggers async `onSearch` if provided |
| `isSelected(option)`              | Check if option is selected                           |
| `isHighlighted(index)`            | Check if option is highlighted for keyboard nav       |

### Form Integration

When a `name` prop is provided and the component is inside a `tutorForm`, it automatically:

1. Registers with the form's validation system
2. Syncs value bidirectionally
3. Manages hidden `<input>` elements for native form submission

---

## Tooltip

**File:** [`ts/components/tooltip.ts`](../ts/components/tooltip.ts) &nbsp;|&nbsp; **Directive:** `x-data="tutorTooltip({ ... })"`

Smart-positioned tooltip with auto-flip, RTL support, and multiple trigger modes.

### Config

```typescript
interface TooltipProps {
  placement?: 'top' | 'bottom' | 'start' | 'end'; // Default: 'top'
  trigger?: 'hover' | 'focus' | 'click'; // Default: 'hover'
  size?: 'small' | 'large'; // Default: 'small'
  arrow?: 'start' | 'center' | 'end'; // Default: 'start'
  offset?: number; // Default: 8
  delay?: { show?: number; hide?: number }; // Default: { show: 0, hide: 0 }
}
```

> **Note:** Placements use `start`/`end` instead of `left`/`right` for automatic RTL adaptation.

### Methods

| Method     | Description                            |
| ---------- | -------------------------------------- |
| `show()`   | Show tooltip with position calculation |
| `hide()`   | Hide tooltip                           |
| `toggle()` | Toggle visibility                      |

### Usage

```html
<div x-data="tutorTooltip({ placement: 'top', trigger: 'hover' })">
  <button x-ref="trigger">Hover me</button>
  <div x-ref="content" x-show="open" x-cloak>Tooltip text here</div>
</div>
```

---

## Popover

**File:** [`ts/components/popover.ts`](../ts/components/popover.ts) &nbsp;|&nbsp; **Directive:** `x-data="tutorPopover({ ... })"`

A rich-content floating panel with automatic position flipping and RTL adaptation.

### Config

```typescript
interface PopoverProps {
  placement?: 'top' | 'top-start' | 'top-end' | 'bottom' | 'bottom-start' | 'bottom-end' | 'left' | 'right';
  offset?: number; // Default: 4
  onShow?: () => void;
  onHide?: () => void;
}
```

### Methods

| Method                 | Description                              |
| ---------------------- | ---------------------------------------- |
| `show()`               | Open popover with position calculation   |
| `hide()`               | Close popover                            |
| `toggle()`             | Toggle visibility                        |
| `handleClickOutside()` | Close if open (bind to `@click.outside`) |

### Usage

```html
<div x-data="tutorPopover({ placement: 'bottom-start' })">
  <button x-ref="trigger" @click="toggle()">Menu</button>
  <div x-ref="content" x-show="open" @click.outside="handleClickOutside()" x-cloak>
    <p>Popover content</p>
  </div>
</div>
```

---

## Toast (Component)

**File:** [`ts/components/toast.ts`](../ts/components/toast.ts) &nbsp;|&nbsp; **Directive:** `x-data="tutorToast()"`

The DOM-side component that renders toast notifications. Listens for `tutor-toast-show` and `tutor-toast-clear` events.

> **Note:** You rarely create this manually. The `ToastService` auto-injects the container into the DOM on first use.

### Toast Item Shape

```typescript
interface ToastItem {
  id: number;
  message: string;
  type: 'success' | 'error' | 'warning' | 'info';
  duration: number; // ms, 0 = persistent
  title: string;
}
```

### Methods

| Method                        | Description                 |
| ----------------------------- | --------------------------- |
| `show(message, config?)`      | Add a toast notification    |
| `remove(id)`                  | Remove a specific toast     |
| `clear()`                     | Remove all toasts           |
| `success(message, duration?)` | Shorthand for success toast |
| `error(message, duration?)`   | Shorthand for error toast   |
| `warning(message, duration?)` | Shorthand for warning toast |
| `info(message, duration?)`    | Shorthand for info toast    |

---

## Form

**File:** [`ts/components/form.ts`](../ts/components/form.ts) &nbsp;|&nbsp; **Directive:** `x-data="tutorForm({ ... })"`

A comprehensive form management system inspired by React Hook Form. Provides field registration, validation, dirty/touched tracking, and submit handling.

### Config

```typescript
interface FormControlConfig {
  id?: string; // Form ID for external access via FormService
  mode?: 'onChange' | 'onBlur' | 'onSubmit'; // Validation timing (default: 'onBlur')
  shouldFocusError?: boolean; // Auto-focus first error field (default: true)
  shouldScrollToError?: boolean; // Auto-scroll to first error (default: true)
  defaultValues?: Record<string, unknown>;
}
```

### Validation Rules

```typescript
interface ValidationRules {
  required?: boolean | string;
  minLength?: number | { value: number; message: string };
  maxLength?: number | { value: number; message: string };
  min?: number | { value: number; message: string };
  max?: number | { value: number; message: string };
  pattern?: RegExp | { value: RegExp; message: string };
  validTime?: boolean | string | { message: string };
  numberOnly?: boolean | { allowNegative?: boolean; whole?: boolean };
  validate?: (value: unknown) => boolean | string | Promise<boolean | string>;
}
```

### Reactive State

| Property        | Type                         | Description                    |
| --------------- | ---------------------------- | ------------------------------ |
| `values`        | `Record<string, unknown>`    | All field values (reactive)    |
| `errors`        | `Record<string, FieldError>` | Current validation errors      |
| `touchedFields` | `Record<string, boolean>`    | Fields that have been blurred  |
| `dirtyFields`   | `Record<string, boolean>`    | Fields differing from defaults |
| `isValid`       | `boolean`                    | Whether all validations pass   |
| `isDirty`       | `boolean` (computed)         | Whether any field is dirty     |
| `isSubmitting`  | `boolean`                    | Submission in progress         |
| `isValidating`  | `boolean`                    | Validation in progress         |

### Key Methods

| Method         | Signature                         | Description                                         |
| -------------- | --------------------------------- | --------------------------------------------------- |
| `register`     | `(name, rules?) → bindings`       | Register a field; returns Alpine attribute bindings |
| `watch`        | `(name?) → value`                 | Watch a field or all values                         |
| `setValue`     | `(name, value, options?)`         | Programmatically set a field value                  |
| `getValue`     | `(name) → value`                  | Get a field's current value                         |
| `setFocus`     | `(name, options?)`                | Focus a field                                       |
| `trigger`      | `(name?) → Promise<boolean>`      | Trigger validation for field(s)                     |
| `clearErrors`  | `(name?)`                         | Clear errors for field(s)                           |
| `setError`     | `(name, error)`                   | Set a custom error                                  |
| `reset`        | `(values?)`                       | Reset form to defaults or provided values           |
| `handleSubmit` | `(onValid, onInvalid?) → handler` | Returns an event handler for form submission        |

### Usage

```html
<form
  x-data="tutorForm({
  id: 'course-form',
  mode: 'onBlur',
  defaultValues: { title: '', price: 0 }
})"
  @submit="handleSubmit(submitCourse, handleErrors)($event)"
>
  <input x-bind="register('title', { required: 'Title is required', minLength: 3 })" />
  <span x-show="errors.title" x-text="errors.title?.message" class="tutor-input-error"></span>

  <input x-bind="register('price', { required: true, min: 0, numberOnly: true })" />

  <button type="submit" :disabled="isSubmitting || !isValid">Save</button>
</form>
```

### What `register()` Returns

For a standard text input, `register('email', rules)` returns:

```javascript
{
  name: 'email',
  'x-ref': 'email',
  'x-model': 'values["email"]',
  ':aria-invalid': '!!errors["email"]',
  ':class': '{ "tutor-input-error": errors["email"], ... }',
  '@input': 'handleFieldInput("email", $event.target.value, $event.target)',
  '@blur': 'handleFieldBlur("email", $event.target.value)'
}
```

### Utility: `FormDataUtils`

Helper for converting form values to `FormData` or serialized URL params:

```javascript
FormDataUtils.convertToFormData(values, 'POST'); // Returns FormData
FormDataUtils.serializeParams(params); // Returns Record<string, unknown>
```
