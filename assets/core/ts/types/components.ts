// Component Type Definitions
// TypeScript interfaces for Alpine.js components

export interface DropdownConfig {
  placement?: 'bottom-start' | 'bottom-end' | 'top-start' | 'top-end';
  offset?: number;
  closeOnClickOutside?: boolean;
  trigger?: 'click' | 'hover';
  delay?: number;
}

export interface ModalConfig {
  closable?: boolean;
  backdrop?: boolean;
  keyboard?: boolean;
  size?: 'small' | 'medium' | 'large' | 'fullscreen';
  animation?: 'fade' | 'slide' | 'zoom';
}

export interface ToastConfig {
  type?: 'success' | 'error' | 'warning' | 'info';
  duration?: number;
  position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left' | 'top-center' | 'bottom-center';
  closable?: boolean;
  icon?: boolean;
}

export interface PopoverConfig {
  placement?: 'top' | 'bottom' | 'left' | 'right' | 'top-start' | 'top-end' | 'bottom-start' | 'bottom-end';
  trigger?: 'click' | 'hover' | 'focus' | 'manual';
  offset?: number;
  delay?: { show?: number; hide?: number };
  arrow?: boolean;
  interactive?: boolean;
}

export interface TabsConfig {
  defaultTab?: number;
  orientation?: 'horizontal' | 'vertical';
  keyboard?: boolean;
  lazy?: boolean;
}

export interface AccordionConfig {
  multiple?: boolean;
  collapsible?: boolean;
  defaultOpen?: number[];
  animation?: boolean;
}

export interface SidebarConfig {
  breakpoint?: number;
  overlay?: boolean;
  position?: 'left' | 'right';
  backdrop?: boolean;
  persistent?: boolean;
}

export interface TooltipConfig {
  placement?: 'top' | 'bottom' | 'left' | 'right';
  trigger?: 'hover' | 'focus' | 'click';
  delay?: { show?: number; hide?: number };
  arrow?: boolean;
  interactive?: boolean;
}

export interface FormValidationConfig {
  rules?: Record<string, ValidationRule[]>;
  messages?: Record<string, string>;
  validateOnBlur?: boolean;
  validateOnInput?: boolean;
  showErrors?: boolean;
}

export interface ValidationRule {
  type: 'required' | 'email' | 'min' | 'max' | 'pattern' | 'minLength' | 'maxLength' | 'number' | 'url';
  value?: string | number;
  message?: string;
}

export interface ToastItem {
  id: number;
  message: string;
  type: 'success' | 'error' | 'warning' | 'info';
  duration: number;
}

// Alpine.js component return types
export interface AlpineDropdownData {
  open: boolean;
  placement: string;
  $el?: HTMLElement;
  $nextTick?: (callback: () => void) => void;
  init(): void;
  toggle(): void;
  close(): void;
  handleClickOutside(): void;
  handleKeydown(event: KeyboardEvent): void;
  setupRTL(): void;
}

export interface AlpineModalData {
  open: boolean;
  previousFocus: HTMLElement | null;
  $el?: HTMLElement;
  $nextTick?: (callback: () => void) => void;
  show(): void;
  hide(): void;
  handleKeydown(event: KeyboardEvent): void;
  handleBackdropClick(): void;
  trapFocus(): void;
  releaseFocus(): void;
  handleTabKey(event: KeyboardEvent): void;
}

export interface AlpineToastData {
  toasts: ToastItem[];
  $el?: HTMLElement;
  show(message: string, config?: ToastConfig): void;
  remove(id: number): void;
  clear(): void;
  success(message: string, duration?: number): void;
  error(message: string, duration?: number): void;
  warning(message: string, duration?: number): void;
  info(message: string, duration?: number): void;
}

export interface AlpineTabsData {
  activeTab: number;
  $el?: HTMLElement;
  init(): void;
  setTab(index: number): void;
  isActive(index: number): boolean;
  handleKeydown(event: KeyboardEvent, index: number): void;
  setupAccessibility(): void;
  updateAccessibility(): void;
}

export interface AlpineAccordionData {
  openItems: number[];
  multiple: boolean;
  $el?: HTMLElement;
  toggle(index: number): void;
  isOpen(index: number): boolean;
  handleKeydown(event: KeyboardEvent, index: number): void;
  focusNext(currentIndex: number): void;
  focusPrevious(currentIndex: number): void;
  focusFirst(): void;
  focusLast(): void;
}

export interface AlpinePopoverData {
  open: boolean;
  placement: string;
  trigger: string;
  $el?: HTMLElement;
  init(): void;
  show(): void;
  hide(): void;
  toggle(): void;
  handleClickOutside(): void;
  handleMouseEnter(): void;
  handleMouseLeave(): void;
  handleFocus(): void;
  handleBlur(): void;
  handleKeydown(event: KeyboardEvent): void;
  setupRTL(): void;
  setupTriggers(): void;
  updatePosition(): void;
}

export interface AlpineTooltipData {
  visible: boolean;
  placement: string;
  trigger: string;
  $el?: HTMLElement;
  init(): void;
  show(): void;
  hide(): void;
  handleMouseEnter(): void;
  handleMouseLeave(): void;
  handleFocus(): void;
  handleBlur(): void;
  handleClick(): void;
  handleKeydown(event: KeyboardEvent): void;
  setupRTL(): void;
  setupTriggers(): void;
  setupAccessibility(): void;
  updatePosition(): void;
}

export interface AlpineSidebarData {
  open: boolean;
  overlay: boolean;
  position: string;
  breakpoint: number;
  previousFocus: HTMLElement | null;
  $el?: HTMLElement;
  $nextTick?: (callback: () => void) => void;
  init(): void;
  toggle(): void;
  close(): void;
  handleResize(): void;
  handleKeydown(event: KeyboardEvent): void;
  handleBackdropClick(): void;
  setupRTL(): void;
  setupResponsive(): void;
  setupKeyboardHandling(): void;
  updateBodyClass(): void;
  manageFocus(): void;
  restoreFocus(): void;
  trapFocus(event: KeyboardEvent): void;
}

export interface AlpineFormValidationData {
  errors: Record<string, string>;
  touched: Record<string, boolean>;
  $el?: HTMLElement;
  init(): void;
  validate(field: string, value: unknown): boolean;
  validateRule(field: string, value: unknown, rule: ValidationRule): boolean;
  validateAll(): boolean;
  hasError(field: string): boolean;
  getError(field: string): string;
  clearError(field: string): void;
  clearAllErrors(): void;
  touch(field: string): void;
  isTouched(field: string): boolean;
  shouldShowError(field: string): boolean;
  handleInput(field: string, value: unknown): void;
  handleBlur(field: string, value: unknown): void;
  handleSubmit(event: Event): boolean;
  focusFirstError(): void;
  setupValidation(): void;
  getFieldStatus(field: string): 'valid' | 'invalid' | 'untouched';
}
