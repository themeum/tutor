import { __ } from '@wordpress/i18n';

import { type ServiceMeta } from '@Core/ts/types';
import {
  type ToastConfig,
  type ToastType,
  type TutorToastConfig,
  type TutorToastOptions,
  type TutorToastPromiseMessages,
  type TutorToastType,
  type TutorToastUpdateOptions,
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
  loading: (message: string, options?: TutorToastOptions) => string;
  promise: <T>(promise: Promise<T>, messages: TutorToastPromiseMessages<T>, options?: TutorToastOptions) => string;
  update: (id: string, options: TutorToastUpdateOptions) => void;
  dismiss: (id?: string) => void;
  configure: (options: TutorToastConfig) => void;
}

interface NormalizedTutorToastOptions {
  type: TutorToastType;
  title: string;
  description?: string;
  icon: string | null;
  action: TutorToastOptions['action'] | null;
  duration: number;
  progressBar: boolean;
  closeButton: boolean;
  dir: 'ltr' | 'rtl';
  richColors: boolean;
  position: TutorToastOptions['position'];
}

const DEFAULT_CONFIG: Required<TutorToastConfig> = {
  position: 'bottom-right',
  duration: 5000,
  closeButton: true,
  progressBar: false,
  maxVisible: 5,
  dir: 'auto',
  offset: {
    x: 16,
    y: 16,
    mobile: { y: 12 },
    lg: {},
  },
  expandMode: 'hover',
  richColors: false,
};

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
  loading: '',
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
      return 'left';
    }

    if (position.endsWith('right')) {
      return 'right';
    }

    return 'center';
  }

  private yPosition(position = this.config.position): 'top' | 'bottom' {
    return this.isBottom(position) ? 'bottom' : 'top';
  }

  private ensureLiveRegion(): void {
    if (document.getElementById('tutor-toast-aria-live')) {
      return;
    }

    const liveRegion = document.createElement('div');
    liveRegion.id = 'tutor-toast-aria-live';
    liveRegion.className = 'tutor-toast-sr-only';
    liveRegion.setAttribute('aria-live', 'polite');
    liveRegion.setAttribute('aria-atomic', 'false');
    document.body.appendChild(liveRegion);
  }

  private announce(title: string, description: string | undefined, type: TutorToastType): void {
    this.ensureLiveRegion();
    const liveRegion = document.getElementById('tutor-toast-aria-live');

    if (!liveRegion) {
      return;
    }

    liveRegion.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');
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

    this.container.style.setProperty('--tutor-toast-offset-x', `${offsetX}px`);
    this.container.style.setProperty('--tutor-toast-offset-y', `${offsetY}px`);
  }

  private syncContainerAttributes(position = this.config.position): void {
    if (!this.container) {
      return;
    }

    this.container.setAttribute('data-position-x', this.xPosition(position));
    this.container.setAttribute('data-position-y', this.yPosition(position));

    if (this.config.dir === 'auto') {
      this.container.removeAttribute('dir');
      return;
    }

    this.container.setAttribute('dir', this.config.dir);
  }

  private boot(position = this.config.position): void {
    if (this.container && this.stack) {
      this.syncContainerAttributes(position);
      this.applyOffset();
      return;
    }

    this.container = document.createElement('ol');
    this.container.className = 'tutor-toast-container';
    this.container.setAttribute('role', 'region');
    this.container.setAttribute('aria-label', __('Notifications', 'tutor'));
    this.container.setAttribute('tabindex', '-1');

    this.stack = document.createElement('li');
    this.stack.className = 'tutor-toast-stack';
    this.stack.setAttribute('role', 'list');

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

    if (type === 'loading') {
      const spinner = document.createElement('div');
      spinner.className = 'tutor-toast-spinner';
      spinner.setAttribute('aria-label', __('Loading', 'tutor'));
      wrapper.appendChild(spinner);
      return;
    }

    const iconMarkup = override ?? TOAST_ICON_MARKUP[type] ?? TOAST_ICON_MARKUP.default;

    if (iconMarkup?.trimStart().startsWith('<')) {
      wrapper.innerHTML = iconMarkup;
      return;
    }

    wrapper.textContent = String(iconMarkup);
  }

  private buildCard(id: string, title: string, options: NormalizedTutorToastOptions): HTMLElement {
    const card = document.createElement('div');
    card.className = 'tutor-toast-card';
    card.setAttribute('data-type', options.type);
    card.setAttribute('role', options.type === 'error' ? 'alert' : 'status');
    card.setAttribute('aria-atomic', 'false');

    if (options.dir) {
      card.setAttribute('dir', options.dir);
    }

    if (options.richColors) {
      card.setAttribute('data-rich-colors', 'true');
    }

    const icon = document.createElement('div');
    icon.className = 'tutor-toast-icon';
    this.renderIcon(icon, options.type, options.icon);
    card.appendChild(icon);

    const content = document.createElement('div');
    content.className = 'tutor-toast-content';

    const titleElement = document.createElement('p');
    titleElement.className = 'tutor-toast-title';
    titleElement.id = `tutor-toast-title-${id}`;
    titleElement.textContent = title;
    content.appendChild(titleElement);

    if (options.description) {
      const description = document.createElement('p');
      description.className = 'tutor-toast-description';
      description.textContent = options.description;
      content.appendChild(description);
    }

    card.appendChild(content);

    if (options.action) {
      const actions = document.createElement('div');
      actions.className = 'tutor-toast-actions';

      const actionButton = document.createElement('button');
      actionButton.className = 'tutor-toast-action-button';
      actionButton.type = 'button';
      actionButton.textContent = options.action.label;
      actionButton.addEventListener('click', (event) => {
        event.stopPropagation();
        options.action?.onClick();
        if (options.action?.dismissOnClick !== false) {
          this.dismiss(id);
        }
      });

      actions.appendChild(actionButton);
      card.appendChild(actions);
    }

    if (options.closeButton) {
      const closeButton = document.createElement('button');
      closeButton.className = 'tutor-toast-close';
      closeButton.type = 'button';
      closeButton.setAttribute('aria-label', __('Close notification', 'tutor'));
      closeButton.innerHTML =
        '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M1 1l10 10M11 1L1 11"/></svg>';
      closeButton.addEventListener('click', (event) => {
        event.stopPropagation();
        this.dismiss(id);
      });
      card.appendChild(closeButton);
    }

    if (options.progressBar && options.type !== 'loading' && options.duration > 0) {
      const progress = document.createElement('div');
      progress.className = 'tutor-toast-progress';

      const progressBar = document.createElement('div');
      progressBar.className = 'tutor-toast-progress-bar';
      progressBar.style.animation = `tutor-toast-progress-shrink ${options.duration}ms linear forwards`;

      progress.appendChild(progressBar);
      card.appendChild(progress);
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

    const gap = 10;
    const peek = 7;
    const scaleStep = 0.034;
    const direction = this.isBottom() ? -1 : 1;

    visibleEntries.forEach((entry) => {
      const height = entry.element.offsetHeight;
      if (height > 0) {
        entry.height = height;
      }
    });

    visibleEntries.forEach((entry, index) => {
      const isFront = index === 0;

      entry.element.setAttribute('data-front', String(isFront));
      if (this.expanded) {
        entry.element.setAttribute('data-expanded', 'true');
      } else {
        entry.element.removeAttribute('data-expanded');
      }

      entry.element.style.pointerEvents = isFront || this.expanded ? 'all' : 'none';

      if (!this.expanded) {
        if (isFront) {
          entry.element.style.setProperty('--tutor-toast-y', '0px');
          entry.element.style.setProperty('--tutor-toast-scale', '1');
          entry.element.style.setProperty('--tutor-toast-opacity', '1');
        } else {
          const offset = index * peek;
          const scale = Math.max(0.9, 1 - index * scaleStep);
          const opacity = index === 1 ? 0.78 : index === 2 ? 0.52 : 0;

          entry.element.style.setProperty('--tutor-toast-y', `${direction * offset}px`);
          entry.element.style.setProperty('--tutor-toast-scale', String(scale));
          entry.element.style.setProperty('--tutor-toast-opacity', String(opacity));
        }
      } else {
        let offset = 0;
        for (let cursor = 0; cursor < index; cursor += 1) {
          offset += (visibleEntries[cursor].height || 72) + gap;
        }

        entry.element.style.setProperty('--tutor-toast-y', `${direction * offset}px`);
        entry.element.style.setProperty('--tutor-toast-scale', '1');
        entry.element.style.setProperty('--tutor-toast-opacity', '1');
      }

      entry.element.style.zIndex = String(10 - index);
      if (!entry.element.hasAttribute('data-entering')) {
        entry.element.style.transform = 'translateY(var(--tutor-toast-y, 0px)) scale(var(--tutor-toast-scale, 1))';
        entry.element.style.opacity = 'var(--tutor-toast-opacity, 1)';
      }
    });

    const frontHeight = visibleEntries[0]?.height || 0;
    this.stack.style.setProperty('--tutor-toast-front-height', `${frontHeight}px`);

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
    if (entry.paused || entry.exiting || entry.type === 'loading') {
      return;
    }

    this.clearTimer(entry.id);
    entry.remainingMs = Math.max(0, entry.endsAt - Date.now());
    entry.paused = true;

    const progressBar = entry.card.querySelector<HTMLElement>('.tutor-toast-progress-bar');
    if (progressBar) {
      progressBar.style.animationPlayState = 'paused';
    }
  }

  private resumeEntry(entry: TutorToastEntry): void {
    if (!entry.paused || entry.exiting) {
      return;
    }

    entry.paused = false;

    if (entry.remainingMs > 0) {
      entry.endsAt = Date.now() + entry.remainingMs;
      entry.timerId = setTimeout(() => this.dismiss(entry.id), entry.remainingMs);

      const progressBar = entry.card.querySelector<HTMLElement>('.tutor-toast-progress-bar');
      if (progressBar) {
        progressBar.style.animationPlayState = 'running';
      }
    } else if (entry.type !== 'loading') {
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
    const card = element.querySelector<HTMLElement>('.tutor-toast-card');
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
        element.setAttribute('data-swiping', 'true');
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
        element.removeAttribute('data-swiping');
      }

      if (Math.abs(distanceX) >= 60) {
        const direction = distanceX > 0 ? 'right' : 'left';
        this.clearTimer(id);

        if (entry) {
          entry.exiting = true;
        }

        element.setAttribute('data-swipe-out', direction);
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
    entry.element.removeAttribute('data-entering');
    entry.element.setAttribute('data-exiting', 'true');
    entry.element.setAttribute('data-position-y', this.yPosition());

    setTimeout(() => this.collapseAndRemove(entry.element, id), 280);
  }

  public dismiss(id?: string): void {
    if (id) {
      this.dismissOne(id);
      return;
    }

    Array.from(this.entries.keys()).forEach((entryId) => this.dismissOne(entryId));
  }

  public update(id: string, options: TutorToastUpdateOptions): void {
    const entry = this.entries.get(id);
    if (!entry) {
      return;
    }

    const { card } = entry;
    const nextType = options.type ?? entry.type;

    if (options.type) {
      card.setAttribute('data-type', options.type);
      card.setAttribute('role', options.type === 'error' ? 'alert' : 'status');
      entry.type = options.type;
      const icon = card.querySelector<HTMLElement>('.tutor-toast-icon');
      if (icon) {
        this.renderIcon(icon, options.type, options.icon ?? null);
      }
    }

    if (options.title != null) {
      const titleElement = card.querySelector<HTMLElement>('.tutor-toast-title');
      if (titleElement) {
        titleElement.textContent = options.title;
      }
    }

    if (options.description != null) {
      let descriptionElement = card.querySelector<HTMLElement>('.tutor-toast-description');
      if (!descriptionElement) {
        descriptionElement = document.createElement('p');
        descriptionElement.className = 'tutor-toast-description';
        card.querySelector('.tutor-toast-content')?.appendChild(descriptionElement);
      }
      descriptionElement.textContent = options.description;
    }

    card.setAttribute('data-updating', 'true');
    setTimeout(() => card.removeAttribute('data-updating'), 250);

    this.clearTimer(id);
    const nextDuration = options.duration ?? this.config.duration;

    if (nextType !== 'loading' && nextDuration > 0) {
      entry.paused = false;
      entry.endsAt = Date.now() + nextDuration;
      entry.remainingMs = nextDuration;
      entry.timerId = setTimeout(() => this.dismiss(id), nextDuration);

      const progressBar = card.querySelector<HTMLElement>('.tutor-toast-progress-bar');
      if (progressBar) {
        progressBar.style.animation = 'none';
        requestAnimationFrame(() => {
          progressBar.style.animation = `tutor-toast-progress-shrink ${nextDuration}ms linear forwards`;
        });
      } else if (options.progressBar ?? this.config.progressBar) {
        const progress = document.createElement('div');
        progress.className = 'tutor-toast-progress';

        const bar = document.createElement('div');
        bar.className = 'tutor-toast-progress-bar';
        bar.style.animation = `tutor-toast-progress-shrink ${nextDuration}ms linear forwards`;

        progress.appendChild(bar);
        card.appendChild(progress);
      }
    }

    const titleElement = card.querySelector<HTMLElement>('.tutor-toast-title');
    const descriptionElement = card.querySelector<HTMLElement>('.tutor-toast-description');
    this.announce(titleElement?.textContent || '', descriptionElement?.textContent, nextType);
  }

  public show(message: string, options: TutorToastOptions = {}): string {
    const position = options.position ?? this.config.position;
    this.boot(position);

    const id = String(++this.idCounter);
    const type = options.type ?? 'info';
    const duration = options.duration ?? this.config.duration;
    const title =
      options.title ?? (type === 'default' || type === 'loading' ? message : DEFAULT_LABELS[type as ToastType]);
    const description = options.description ?? (options.title ? message : type === 'default' ? undefined : message);

    const normalizedOptions: NormalizedTutorToastOptions = {
      type,
      title,
      description,
      icon: options.icon ?? null,
      action: options.action ?? null,
      duration,
      progressBar: options.progressBar ?? this.config.progressBar,
      closeButton: options.closeButton ?? this.config.closeButton,
      dir: options.dir ?? (this.config.dir !== 'auto' ? this.config.dir : 'ltr'),
      richColors: options.richColors ?? this.config.richColors,
      position,
    };

    const item = document.createElement('li');
    item.className = 'tutor-toast-item';
    item.setAttribute('role', 'listitem');
    item.setAttribute('aria-labelledby', `tutor-toast-title-${id}`);
    item.setAttribute('tabindex', '0');
    item.setAttribute('data-entering', 'true');
    item.setAttribute('data-position-y', this.yPosition(position));

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
      item.removeAttribute('data-entering');
      this.restack();
    }, 420);

    this.announce(title, description, type);

    let timerId: ReturnType<typeof setTimeout> | null = null;
    let endsAt = 0;
    if (type !== 'loading' && duration > 0 && !this.hovered) {
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

  public promise<T>(promise: Promise<T>, messages: TutorToastPromiseMessages<T>, options?: TutorToastOptions): string {
    const id = this.show(
      typeof messages.loading === 'function' ? messages.loading() : messages.loading || __('Loading', 'tutor'),
      {
        ...options,
        type: 'loading',
        duration: 0,
      },
    );

    Promise.resolve(promise)
      .then((result) => {
        const title = typeof messages.success === 'function' ? messages.success(result) : messages.success;
        this.update(id, {
          type: 'success',
          title,
          duration: options?.duration ?? this.config.duration,
        });
      })
      .catch((error: unknown) => {
        const title = typeof messages.error === 'function' ? messages.error(error) : messages.error;
        this.update(id, {
          type: 'error',
          title,
          duration: options?.duration ?? this.config.duration,
        });
      });

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

  public loading(message: string, options?: TutorToastOptions): string {
    return this.show(message, { ...options, type: 'loading', duration: 0 });
  }

  public createContextBound(contextConfig: TutorToastConfig): TutorToastApi {
    const show = (message: string, options?: TutorToastOptions) =>
      this.show(message, {
        ...contextConfig,
        ...options,
      });

    return createTutorToastApi(show, this);
  }
}

export function createTutorToastApi(
  showHandler: (message: string, options?: TutorToastOptions) => string,
  manager: TutorToastManager,
): TutorToastApi {
  const api = ((message: string, options?: TutorToastOptions) => showHandler(message, options)) as TutorToastApi;

  api.success = (message, options) => showHandler(message, { ...options, type: 'success' });
  api.error = (message, options) => showHandler(message, { ...options, type: 'error' });
  api.warning = (message, options) => showHandler(message, { ...options, type: 'warning' });
  api.info = (message, options) => showHandler(message, { ...options, type: 'info' });
  api.loading = (message, options) => showHandler(message, { ...options, type: 'loading', duration: 0 });
  api.promise = (promise, messages, options) => manager.promise(promise, messages, options);
  api.update = (id, options) => manager.update(id, options);
  api.dismiss = (id) => manager.dismiss(id);
  api.configure = (options) => manager.configure(options);

  return api;
}

export const tutorToastManager = new TutorToastManager();

export const tutorToastDefaults: TutorToastConfig = DEFAULT_CONFIG;

export class ToastService {
  constructor() {
    tutorToastManager.configure(DEFAULT_CONFIG);
  }

  show(message: string, config: ToastConfig = {}): string {
    return tutorToastManager.show(message, config);
  }

  success(message: string, duration?: number): string {
    return tutorToastManager.success(message, duration);
  }

  error(message: string, duration?: number): string {
    return tutorToastManager.error(message, duration);
  }

  warning(message: string, duration?: number): string {
    return tutorToastManager.warning(message, duration);
  }

  info(message: string, duration?: number): string {
    return tutorToastManager.info(message, duration);
  }

  loading(message: string, options?: TutorToastOptions): string {
    return tutorToastManager.loading(message, options);
  }

  update(id: string, options: TutorToastUpdateOptions): void {
    tutorToastManager.update(id, options);
  }

  dismiss(id?: string): void {
    tutorToastManager.dismiss(id);
  }

  clear(): void {
    tutorToastManager.clear();
  }

  promise<T>(promise: Promise<T>, messages: TutorToastPromiseMessages<T>, options?: TutorToastOptions): string {
    return tutorToastManager.promise(promise, messages, options);
  }

  configure(options: TutorToastConfig): void {
    tutorToastManager.configure(options);
  }
}

export const toastServiceMeta: ServiceMeta<ToastService> = {
  name: 'toast',
  instance: new ToastService(),
};
