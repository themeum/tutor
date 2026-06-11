import { __ } from '@wordpress/i18n';

import {
  type ToastType,
  type TutorToastConfig,
  type TutorToastOptions,
  type TutorToastType,
} from '@Core/ts/types/toast';

interface TutorToastEntry {
  id: string;
  element: HTMLElement;
  card: HTMLElement;
  timerId: ReturnType<typeof setTimeout> | null;
  type: TutorToastType;
  endsAt: number;
  remainingMs: number;
  paused: boolean;
  exiting: boolean;
  swiping: boolean;
  height: number;
}

export interface TutorToastApi {
  (message: string, options?: TutorToastOptions): string;
  success: (message: string, options?: TutorToastOptions) => string;
  error: (message: string, options?: TutorToastOptions) => string;
  warning: (message: string, options?: TutorToastOptions) => string;
  info: (message: string, options?: TutorToastOptions) => string;

  dismiss: (id?: string) => void;
  configure: (options: TutorToastConfig) => void;
}

interface NormalizedTutorToastOptions {
  type: TutorToastType;
  title: string;
  description?: string;
  icon: string | null;
  duration: number;
  closeButton: boolean;
  dir: 'ltr' | 'rtl' | 'auto';
  richColors: boolean;
  position: TutorToastOptions['position'];
}

const DEFAULT_CONFIG: Required<TutorToastConfig> = {
  position: 'bottom-right',
  duration: 5000,
  closeButton: true,
  maxVisible: 5,
  dir: 'auto',
  offset: {
    x: 20,
    y: 20,
    mobile: { y: 12 },
    lg: {},
  },
  expandMode: 'hover',
  richColors: false,
  theme: 'auto',
};

const DEFAULT_STACK_DEPTH = {
  gap: 10,
  peek: 10,
  scaleStep: 0.034,
  scaleFloor: 0.883,
  opacity1: 0.78,
  opacity2: 0.52,
  opacity3: 0,
} as const;

const TOAST_DOM_ID = {
  liveRegion: 'tutor-toast-aria-live',
} as const;

const TOAST_CLASS = {
  srOnly: 'tutor-toast-sr-only',
  container: 'tutor-toast-container',
  stack: 'tutor-toast-stack',
  card: 'tutor-toast-card',
  icon: 'tutor-toast-icon',
  content: 'tutor-toast-content',
  title: 'tutor-toast-title',
  description: 'tutor-toast-description',
  closeButton: 'tutor-toast-close',
  item: 'tutor-toast-item',
} as const;

const TOAST_SELECTOR = {
  card: `.${TOAST_CLASS.card}`,
  icon: `.${TOAST_CLASS.icon}`,
  content: `.${TOAST_CLASS.content}`,
  title: `.${TOAST_CLASS.title}`,
  description: `.${TOAST_CLASS.description}`,
} as const;

const TOAST_ATTR = {
  ariaLive: 'aria-live',
  ariaAtomic: 'aria-atomic',
  ariaLabel: 'aria-label',
  ariaLabelledBy: 'aria-labelledby',
  dataPositionX: 'data-position-x',
  dataPositionY: 'data-position-y',
  dataTutorTheme: 'data-tutor-theme',
  dataRichColors: 'data-rich-colors',
  dataType: 'data-type',
  dataFront: 'data-front',
  dataExpanded: 'data-expanded',
  dataEntering: 'data-entering',
  dataExiting: 'data-exiting',
  dataSwiping: 'data-swiping',
  dataSwipeOut: 'data-swipe-out',

  dir: 'dir',
  role: 'role',
  tabIndex: 'tabindex',
} as const;

const TOAST_ATTR_VALUE = {
  polite: 'polite',
  assertive: 'assertive',
  region: 'region',
  list: 'list',
  listItem: 'listitem',
  alert: 'alert',
  status: 'status',
  true: 'true',
  frontZIndex: '10',
} as const;

const TOAST_POSITION = {
  left: 'left',
  right: 'right',
  center: 'center',
  top: 'top',
  bottom: 'bottom',
} as const;

const TOAST_CSS_VAR = {
  offsetX: '--tutor-toast-offset-x',
  offsetY: '--tutor-toast-offset-y',
  y: '--tutor-toast-y',
  scale: '--tutor-toast-scale',
  opacity: '--tutor-toast-opacity',
  frontHeight: '--tutor-toast-front-height',
} as const;

const TOAST_TITLE_ID_PREFIX = 'tutor-toast-title-';

const DEFAULT_LABELS: Record<ToastType, string> = {
  success: __('Success', 'tutor'),
  error: __('Error', 'tutor'),
  warning: __('Warning', 'tutor'),
  info: __('Info', 'tutor'),
};

const TOAST_ICON_MARKUP: Record<TutorToastType | 'default', string> = {
  success:
    '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>',
  error:
    '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>',
  warning:
    '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
  info: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="8"/><path d="M12 11v6"/></svg>',
  default:
    '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="8"/><path d="M12 11v6"/></svg>',
};

const createDefaultConfig = (): Required<TutorToastConfig> => ({
  ...DEFAULT_CONFIG,
  offset: {
    ...DEFAULT_CONFIG.offset,
    mobile: {
      ...DEFAULT_CONFIG.offset.mobile,
    },
    lg: {
      ...DEFAULT_CONFIG.offset.lg,
    },
  },
});

export class TutorToastManager {
  private config: Required<TutorToastConfig> = createDefaultConfig();

  private readonly entries = new Map<string, TutorToastEntry>();

  private idCounter = 0;

  private container: HTMLOListElement | null = null;

  private stack: HTMLLIElement | null = null;

  private expanded = false;

  private hovered = false;

  constructor() {
    this.initFullscreenListener();
  }

  private initFullscreenListener(): void {
    document.addEventListener('fullscreenchange', () => {
      if (!this.container) {
        return;
      }

      const target = document.fullscreenElement || document.body;
      if (this.container.parentElement !== target) {
        target.appendChild(this.container);
      }
    });
  }

  private isBottom(position = this.config.position): boolean {
    return position.startsWith('bottom');
  }

  private xPosition(position = this.config.position): 'left' | 'right' | 'center' {
    if (position.endsWith('left')) {
      return TOAST_POSITION.left;
    }

    if (position.endsWith('right')) {
      return TOAST_POSITION.right;
    }

    return TOAST_POSITION.center;
  }

  private yPosition(position = this.config.position): 'top' | 'bottom' {
    return this.isBottom(position) ? TOAST_POSITION.bottom : TOAST_POSITION.top;
  }

  private ensureLiveRegion(): void {
    if (document.getElementById(TOAST_DOM_ID.liveRegion)) {
      return;
    }

    const liveRegion = document.createElement('div');
    liveRegion.id = TOAST_DOM_ID.liveRegion;
    liveRegion.className = TOAST_CLASS.srOnly;
    liveRegion.setAttribute(TOAST_ATTR.ariaLive, TOAST_ATTR_VALUE.polite);
    liveRegion.setAttribute(TOAST_ATTR.ariaAtomic, 'false');
    document.body.appendChild(liveRegion);
  }

  private announce(title: string, description: string | undefined, type: TutorToastType): void {
    this.ensureLiveRegion();
    const liveRegion = document.getElementById(TOAST_DOM_ID.liveRegion);

    if (!liveRegion) {
      return;
    }

    liveRegion.setAttribute(
      TOAST_ATTR.ariaLive,
      type === 'error' ? TOAST_ATTR_VALUE.assertive : TOAST_ATTR_VALUE.polite,
    );
    liveRegion.textContent = '';
    requestAnimationFrame(() => {
      liveRegion.textContent = description ? `${title}. ${description}` : title;
    });
  }

  private applyOffset(): void {
    if (!this.container) {
      return;
    }

    const offset = this.config.offset;
    const isMobile = window.matchMedia('(max-width: 639px)').matches;
    const isLarge = window.matchMedia('(min-width: 1280px)').matches;

    let offsetX = offset.x ?? 16;
    let offsetY = offset.y ?? 16;

    if (isMobile && offset.mobile) {
      if (offset.mobile.x != null) {
        offsetX = offset.mobile.x;
      }

      if (offset.mobile.y != null) {
        offsetY = offset.mobile.y;
      }
    }

    if (isLarge && offset.lg) {
      if (offset.lg.x != null) {
        offsetX = offset.lg.x;
      }

      if (offset.lg.y != null) {
        offsetY = offset.lg.y;
      }
    }

    this.container.style.setProperty(TOAST_CSS_VAR.offsetX, `${offsetX}px`);
    this.container.style.setProperty(TOAST_CSS_VAR.offsetY, `${offsetY}px`);
  }

  private syncContainerAttributes(position = this.config.position): void {
    if (!this.container) {
      return;
    }

    this.container.setAttribute(TOAST_ATTR.dataPositionX, this.xPosition(position));
    this.container.setAttribute(TOAST_ATTR.dataPositionY, this.yPosition(position));

    const theme = this.config.theme;
    if (theme === 'auto') {
      this.container.removeAttribute(TOAST_ATTR.dataTutorTheme);
    } else {
      this.container.setAttribute(TOAST_ATTR.dataTutorTheme, theme);
    }

    if (this.config.dir === 'auto') {
      this.container.removeAttribute(TOAST_ATTR.dir);
      return;
    }

    this.container.setAttribute(TOAST_ATTR.dir, this.config.dir);
  }

  private boot(position = this.config.position): void {
    if (this.container && this.stack) {
      this.syncContainerAttributes(position);
      this.applyOffset();
      return;
    }

    this.container = document.createElement('ol');
    this.container.className = TOAST_CLASS.container;
    if (this.config.theme !== 'auto') {
      this.container.setAttribute(TOAST_ATTR.dataTutorTheme, this.config.theme);
    }
    this.container.setAttribute(TOAST_ATTR.role, TOAST_ATTR_VALUE.region);
    this.container.setAttribute(TOAST_ATTR.ariaLabel, __('Notifications', 'tutor'));
    this.container.setAttribute(TOAST_ATTR.tabIndex, '-1');

    this.stack = document.createElement('li');
    this.stack.className = TOAST_CLASS.stack;
    this.stack.setAttribute(TOAST_ATTR.role, TOAST_ATTR_VALUE.list);

    this.stack.addEventListener('mouseenter', () => {
      this.hovered = true;
      if (this.config.expandMode === 'hover') {
        this.setExpanded(true);
      }
      this.pauseAll();
    });

    this.stack.addEventListener('mouseleave', (event: MouseEvent) => {
      if (!this.stack) {
        return;
      }

      const bounds = this.stack.getBoundingClientRect();
      const inside =
        event.clientX >= bounds.left &&
        event.clientX <= bounds.right &&
        event.clientY >= bounds.top &&
        event.clientY <= bounds.bottom;

      if (inside) {
        return;
      }

      this.hovered = false;
      if (this.config.expandMode === 'hover') {
        this.setExpanded(false);
      }
      this.resumeAll();
    });

    this.stack.addEventListener('focusin', () => {
      this.hovered = true;
      this.pauseAll();
      this.setExpanded(true);
    });

    this.stack.addEventListener('focusout', (event: FocusEvent) => {
      if (this.stack?.contains(event.relatedTarget as Node)) {
        return;
      }

      this.hovered = false;
      this.resumeAll();
      this.setExpanded(false);
    });

    this.container.appendChild(this.stack);
    const target = document.fullscreenElement || document.body;
    target.appendChild(this.container);

    this.syncContainerAttributes(position);
    this.applyOffset();

    if (this.config.expandMode === 'always') {
      this.expanded = true;
    }

    window.addEventListener('resize', () => this.applyOffset());
  }

  private setExpanded(isExpanded: boolean): void {
    if (this.config.expandMode === 'always') {
      this.expanded = true;
    } else if (this.config.expandMode === 'never') {
      this.expanded = false;
    } else {
      this.expanded = isExpanded;
    }

    this.restack();
  }

  private renderIcon(wrapper: HTMLElement, type: TutorToastType, override?: string | null): void {
    wrapper.innerHTML = '';

    const iconMarkup = override ?? TOAST_ICON_MARKUP[type] ?? TOAST_ICON_MARKUP.default;

    if (iconMarkup?.trimStart().startsWith('<')) {
      wrapper.innerHTML = iconMarkup;
      return;
    }

    wrapper.textContent = String(iconMarkup);
  }

  private buildCard(id: string, title: string, options: NormalizedTutorToastOptions): HTMLElement {
    const card = document.createElement('div');
    card.className = TOAST_CLASS.card;
    card.setAttribute(TOAST_ATTR.dataType, options.type);
    card.setAttribute(TOAST_ATTR.role, options.type === 'error' ? TOAST_ATTR_VALUE.alert : TOAST_ATTR_VALUE.status);
    card.setAttribute(TOAST_ATTR.ariaAtomic, 'false');

    if (options.dir) {
      card.setAttribute(TOAST_ATTR.dir, options.dir);
    }

    if (options.richColors) {
      card.setAttribute(TOAST_ATTR.dataRichColors, TOAST_ATTR_VALUE.true);
    }

    const icon = document.createElement('div');
    icon.className = TOAST_CLASS.icon;
    this.renderIcon(icon, options.type, options.icon);
    card.appendChild(icon);

    const content = document.createElement('div');
    content.className = TOAST_CLASS.content;

    const titleElement = document.createElement('p');
    titleElement.className = TOAST_CLASS.title;
    titleElement.id = `${TOAST_TITLE_ID_PREFIX}${id}`;
    titleElement.textContent = title;
    content.appendChild(titleElement);

    if (options.description) {
      const description = document.createElement('p');
      description.className = TOAST_CLASS.description;
      description.textContent = options.description;
      content.appendChild(description);
    }

    card.appendChild(content);

    if (options.closeButton) {
      const closeButton = document.createElement('button');
      closeButton.className = TOAST_CLASS.closeButton;
      closeButton.type = 'button';
      closeButton.setAttribute(TOAST_ATTR.ariaLabel, __('Close notification', 'tutor'));
      closeButton.innerHTML =
        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 16 16"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m12.402 3.6-8.8 8.8m0-8.8 8.8 8.8"/></svg>';
      closeButton.addEventListener('click', (event) => {
        event.stopPropagation();
        this.dismiss(id);
      });
      card.appendChild(closeButton);
    }

    return card;
  }

  private restack(): void {
    if (!this.stack) {
      return;
    }

    const visibleEntries = Array.from(this.entries.values())
      .filter((entry) => !entry.exiting && !entry.swiping)
      .reverse();

    const gap = DEFAULT_STACK_DEPTH.gap;
    const peek = DEFAULT_STACK_DEPTH.peek;
    const scaleStep = DEFAULT_STACK_DEPTH.scaleStep;
    const direction = this.isBottom() ? -1 : 1;

    visibleEntries.forEach((entry) => {
      const height = entry.element.offsetHeight;
      if (height > 0) {
        entry.height = height;
      }
    });

    visibleEntries.forEach((entry, index) => {
      const isFront = index === 0;

      entry.element.setAttribute(TOAST_ATTR.dataFront, String(isFront));
      if (this.expanded) {
        entry.element.setAttribute(TOAST_ATTR.dataExpanded, TOAST_ATTR_VALUE.true);
      } else {
        entry.element.removeAttribute(TOAST_ATTR.dataExpanded);
      }

      entry.element.style.pointerEvents = isFront || this.expanded ? 'all' : 'none';

      if (!this.expanded) {
        if (isFront) {
          entry.element.style.setProperty(TOAST_CSS_VAR.y, '0px');
          entry.element.style.setProperty(TOAST_CSS_VAR.scale, '1');
          entry.element.style.setProperty(TOAST_CSS_VAR.opacity, '1');
        } else {
          const offset = index * peek;
          const scale = Math.max(DEFAULT_STACK_DEPTH.scaleFloor, 1 - index * scaleStep);
          const opacity =
            index === 1
              ? DEFAULT_STACK_DEPTH.opacity1
              : index === 2
                ? DEFAULT_STACK_DEPTH.opacity2
                : DEFAULT_STACK_DEPTH.opacity3;

          entry.element.style.setProperty(TOAST_CSS_VAR.y, `${direction * offset}px`);
          entry.element.style.setProperty(TOAST_CSS_VAR.scale, String(scale));
          entry.element.style.setProperty(TOAST_CSS_VAR.opacity, String(opacity));
        }
      } else {
        let offset = 0;
        for (let cursor = 0; cursor < index; cursor += 1) {
          offset += (visibleEntries[cursor].height || 72) + gap;
        }

        entry.element.style.setProperty(TOAST_CSS_VAR.y, `${direction * offset}px`);
        entry.element.style.setProperty(TOAST_CSS_VAR.scale, '1');
        entry.element.style.setProperty(TOAST_CSS_VAR.opacity, '1');
      }

      entry.element.style.zIndex = String(Number(TOAST_ATTR_VALUE.frontZIndex) - index);
      if (!entry.element.hasAttribute(TOAST_ATTR.dataEntering)) {
        entry.element.style.transform = `translateY(var(${TOAST_CSS_VAR.y}, 0px)) scale(var(${TOAST_CSS_VAR.scale}, 1))`;
        entry.element.style.opacity = `var(${TOAST_CSS_VAR.opacity}, 1)`;
      }
    });

    const frontHeight = visibleEntries[0]?.height || 0;
    this.stack.style.setProperty(TOAST_CSS_VAR.frontHeight, `${frontHeight}px`);

    if (this.expanded && visibleEntries.length > 0) {
      const totalHeight =
        visibleEntries.reduce((sum, entry) => sum + (entry.height || 72), 0) +
        Math.max(0, visibleEntries.length - 1) * gap;
      this.stack.style.height = `${totalHeight}px`;
    } else {
      this.stack.style.height = `${frontHeight}px`;
    }
  }

  private clearTimer(id: string): void {
    const entry = this.entries.get(id);
    if (entry?.timerId) {
      clearTimeout(entry.timerId);
      entry.timerId = null;
    }
  }

  private pauseEntry(entry: TutorToastEntry): void {
    if (entry.paused || entry.exiting) {
      return;
    }

    this.clearTimer(entry.id);
    entry.remainingMs = Math.max(0, entry.endsAt - Date.now());
    entry.paused = true;
  }

  private resumeEntry(entry: TutorToastEntry): void {
    if (!entry.paused || entry.exiting) {
      return;
    }

    entry.paused = false;

    if (entry.remainingMs > 0) {
      entry.endsAt = Date.now() + entry.remainingMs;
      entry.timerId = setTimeout(() => this.dismiss(entry.id), entry.remainingMs);
    } else {
      this.dismiss(entry.id);
    }
  }

  private pauseAll(): void {
    this.entries.forEach((entry) => this.pauseEntry(entry));
  }

  private resumeAll(): void {
    this.entries.forEach((entry) => this.resumeEntry(entry));
  }

  private collapseAndRemove(element: HTMLElement, id: string): void {
    this.entries.delete(id);

    const height = element.offsetHeight;
    element.style.pointerEvents = 'none';
    element.style.overflow = 'hidden';
    element.style.height = `${height}px`;
    void element.offsetHeight;
    element.style.transition = 'height 200ms ease';
    element.style.height = '0px';

    setTimeout(() => {
      element.remove();
      if (this.hovered) {
        this.setExpanded(true);
      } else {
        this.restack();
      }
    }, 210);
  }

  private evict(id: string): void {
    const entry = this.entries.get(id);
    if (!entry) {
      return;
    }

    this.clearTimer(id);
    this.entries.delete(id);
    entry.element.remove();
  }

  private enforceLimits(): void {
    const ids = Array.from(this.entries.keys());
    if (ids.length <= this.config.maxVisible) {
      return;
    }

    ids.slice(0, ids.length - this.config.maxVisible).forEach((id) => this.evict(id));
    this.restack();
  }

  private attachSwipe(element: HTMLElement, id: string): void {
    const card = element.querySelector<HTMLElement>(TOAST_SELECTOR.card);
    if (!card) {
      return;
    }

    let startX = 0;
    let distanceX = 0;
    let active = false;

    element.addEventListener('mouseenter', () => {
      const entry = this.entries.get(id);
      if (entry) {
        this.pauseEntry(entry);
      }
    });

    element.addEventListener('mouseleave', () => {
      if (this.expanded) {
        return;
      }

      const entry = this.entries.get(id);
      if (entry) {
        this.resumeEntry(entry);
      }
    });

    const handleStart = (event: MouseEvent | TouchEvent) => {
      startX = 'touches' in event ? event.touches[0].clientX : event.clientX;
      active = true;
      card.style.transition = 'none';

      const entry = this.entries.get(id);
      if (entry) {
        entry.swiping = true;
        element.setAttribute(TOAST_ATTR.dataSwiping, TOAST_ATTR_VALUE.true);
      }
    };

    const handleMove = (event: MouseEvent | TouchEvent) => {
      if (!active) {
        return;
      }

      distanceX = ('touches' in event ? event.touches[0].clientX : event.clientX) - startX;
      card.style.transform = `translateX(${distanceX}px)`;
      card.style.opacity = String(Math.max(0, 1 - Math.abs(distanceX) / 180));
    };

    const handleEnd = () => {
      if (!active) {
        return;
      }

      active = false;
      card.style.transition = '';
      card.style.transform = '';
      card.style.opacity = '';

      const entry = this.entries.get(id);
      if (entry) {
        entry.swiping = false;
        element.removeAttribute(TOAST_ATTR.dataSwiping);
      }

      if (Math.abs(distanceX) >= 60) {
        const direction = distanceX > 0 ? 'right' : 'left';
        this.clearTimer(id);

        if (entry) {
          entry.exiting = true;
        }

        element.setAttribute(TOAST_ATTR.dataSwipeOut, direction);
        setTimeout(() => this.collapseAndRemove(element, id), 230);
      }

      distanceX = 0;
    };

    element.addEventListener('touchstart', handleStart as EventListener, { passive: true });
    element.addEventListener('touchmove', handleMove as EventListener, { passive: true });
    element.addEventListener('touchend', handleEnd);
    element.addEventListener('mousedown', handleStart as EventListener);
    window.addEventListener('mousemove', handleMove as EventListener);
    window.addEventListener('mouseup', handleEnd);
  }

  private dismissOne(id: string): void {
    const entry = this.entries.get(id);
    if (!entry || entry.exiting) {
      return;
    }

    entry.exiting = true;
    this.clearTimer(id);
    entry.element.removeAttribute(TOAST_ATTR.dataEntering);
    entry.element.setAttribute(TOAST_ATTR.dataExiting, TOAST_ATTR_VALUE.true);
    entry.element.setAttribute(TOAST_ATTR.dataPositionY, this.yPosition());

    setTimeout(() => this.collapseAndRemove(entry.element, id), 280);
  }

  public dismiss(id?: string): void {
    if (id) {
      this.dismissOne(id);
      return;
    }

    Array.from(this.entries.keys()).forEach((entryId) => this.dismissOne(entryId));
  }

  public show(message: string, options: TutorToastOptions = {}): string {
    const position = options.position ?? this.config.position;
    const theme = options.theme ?? this.config.theme;

    if (theme !== this.config.theme) {
      this.config = { ...this.config, theme };
    }

    this.boot(position);

    const id = String(++this.idCounter);
    const type = options.type ?? 'info';
    const duration = options.duration ?? this.config.duration;
    const title = options.title ?? (type === 'default' ? message : DEFAULT_LABELS[type as ToastType]);
    const description = options.description ?? (options.title ? message : type === 'default' ? undefined : message);

    const normalizedOptions: NormalizedTutorToastOptions = {
      type,
      title,
      description,
      icon: options.icon ?? null,
      duration,
      closeButton: options.closeButton ?? this.config.closeButton,
      dir: options.dir ?? (this.config.dir !== 'auto' ? this.config.dir : 'ltr'),
      richColors: options.richColors ?? this.config.richColors,
      position,
    };

    const item = document.createElement('li');
    item.className = TOAST_CLASS.item;
    item.setAttribute(TOAST_ATTR.role, TOAST_ATTR_VALUE.listItem);
    item.setAttribute(TOAST_ATTR.ariaLabelledBy, `${TOAST_TITLE_ID_PREFIX}${id}`);
    item.setAttribute(TOAST_ATTR.tabIndex, '0');
    item.setAttribute(TOAST_ATTR.dataEntering, TOAST_ATTR_VALUE.true);
    item.setAttribute(TOAST_ATTR.dataPositionY, this.yPosition(position));

    const card = this.buildCard(id, title, normalizedOptions);
    item.appendChild(card);

    this.attachSwipe(item, id);
    item.addEventListener('keydown', (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        this.dismiss(id);
      }
    });

    if (this.stack?.firstChild) {
      this.stack.insertBefore(item, this.stack.firstChild);
    } else {
      this.stack?.appendChild(item);
    }

    setTimeout(() => {
      item.removeAttribute(TOAST_ATTR.dataEntering);
      this.restack();
    }, 420);

    this.announce(title, description, type);

    let timerId: ReturnType<typeof setTimeout> | null = null;
    let endsAt = 0;
    if (duration > 0 && !this.hovered) {
      endsAt = Date.now() + duration;
      timerId = setTimeout(() => this.dismiss(id), duration);
    } else if (duration > 0) {
      endsAt = Date.now() + duration;
    }

    this.entries.set(id, {
      id,
      element: item,
      card,
      timerId,
      type,
      endsAt,
      remainingMs: duration,
      paused: this.hovered,
      exiting: false,
      swiping: false,
      height: 0,
    });

    this.restack();
    this.enforceLimits();

    return id;
  }

  public configure(options: TutorToastConfig): void {
    const { offset, ...rest } = options;

    if (offset) {
      this.config.offset = {
        ...this.config.offset,
        ...offset,
        mobile: {
          ...this.config.offset.mobile,
          ...offset.mobile,
        },
        lg: {
          ...this.config.offset.lg,
          ...offset.lg,
        },
      };
    }

    this.config = {
      ...this.config,
      ...rest,
      offset: this.config.offset,
    };

    if (this.config.expandMode === 'always') {
      this.expanded = true;
    } else if (this.config.expandMode !== 'hover') {
      this.expanded = false;
    }

    if (this.container) {
      this.syncContainerAttributes();
      this.applyOffset();
      this.restack();
    }
  }

  public clear(): void {
    this.dismiss();
  }

  public success(message: string, duration?: number): string {
    return this.show(message, { type: 'success', ...(duration !== undefined ? { duration } : {}) });
  }

  public error(message: string, duration?: number): string {
    return this.show(message, { type: 'error', ...(duration !== undefined ? { duration } : {}) });
  }

  public warning(message: string, duration?: number): string {
    return this.show(message, { type: 'warning', ...(duration !== undefined ? { duration } : {}) });
  }

  public info(message: string, duration?: number): string {
    return this.show(message, { type: 'info', ...(duration !== undefined ? { duration } : {}) });
  }

  public createContextBound(contextConfig: TutorToastConfig): TutorToastApi {
    const contextOverrides: Pick<TutorToastOptions, 'dir' | 'theme'> = {
      ...(contextConfig.dir != null && { dir: contextConfig.dir }),
      ...(contextConfig.theme != null && { theme: contextConfig.theme }),
    };

    const show = (message: string, options?: TutorToastOptions) =>
      this.show(message, { ...contextOverrides, ...options });

    return createTutorToastApi(show, this, contextOverrides);
  }
}

type ToastContextOptions = Pick<TutorToastOptions, 'dir' | 'theme'>;

export function createTutorToastApi(
  showHandler: (message: string, options?: TutorToastOptions) => string,
  manager: TutorToastManager,
  contextOptions?: ToastContextOptions,
): TutorToastApi {
  const merge = (overrides: Partial<TutorToastOptions>): TutorToastOptions => ({
    ...contextOptions,
    ...overrides,
  });

  const api = ((message: string, options?: TutorToastOptions) => showHandler(message, options)) as TutorToastApi;

  api.success = (message, options) => showHandler(message, merge({ ...options, type: 'success' }));
  api.error = (message, options) => showHandler(message, merge({ ...options, type: 'error' }));
  api.warning = (message, options) => showHandler(message, merge({ ...options, type: 'warning' }));
  api.info = (message, options) => showHandler(message, merge({ ...options, type: 'info' }));

  api.dismiss = (id) => manager.dismiss(id);
  api.configure = (options) => manager.configure(options);

  return api;
}

const manager = new TutorToastManager();

export const tutorToastManager = manager;

export const tutorToastDefaults: TutorToastConfig = DEFAULT_CONFIG;

export const toast = createTutorToastApi((message, options) => manager.show(message, options), manager);
