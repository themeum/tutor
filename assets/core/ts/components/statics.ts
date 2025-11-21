import { type AlpineComponentMeta } from '@Core/ts/types';

type StaticsType = 'progress' | 'complete' | 'locked';
type StaticsSize = 'tiny' | 'small' | 'medium' | 'large';

export interface StaticsProps {
  value?: number;
  type?: StaticsType;
  size?: StaticsSize;
  background?: string;
  strokeColor?: string;
  showLabel?: boolean;
  label?: string;
  animated?: boolean;
  duration?: number;
}

const SIZE_CONFIG = {
  large: { dimension: 144, strokeWidth: 10.8, iconSizes: { check: 80, lock: 104 } },
  medium: { dimension: 56, strokeWidth: 4.3, iconSizes: { check: 24, lock: 32 } },
  small: { dimension: 44, strokeWidth: 3.3, iconSizes: { check: 24, lock: 32 } },
  tiny: { dimension: 16, strokeWidth: 2, iconSizes: { check: 8, lock: 12 } },
} as const;

const DEFAULT_CONFIG = {
  value: 0,
  type: 'progress' as StaticsType,
  size: 'small' as StaticsSize,
  background: 'none',
  strokeColor: 'var(--tutor-actions-brand-secondary)',
  showLabel: true,
  label: '',
  animated: false,
  duration: 1000,
} as const;

const ANIMATION_EASING_POWER = 3;
const MAX_PROGRESS = 100;
const MIN_PROGRESS = 0;

export const statics = (config: StaticsProps) => ({
  value: 0,
  targetValue: config.value ?? DEFAULT_CONFIG.value,
  type: config.type ?? DEFAULT_CONFIG.type,
  background: config.background ?? DEFAULT_CONFIG.background,
  strokeColor: config.strokeColor ?? DEFAULT_CONFIG.strokeColor,
  showLabel: config.showLabel ?? DEFAULT_CONFIG.showLabel,
  label: config.label ?? DEFAULT_CONFIG.label,
  animated: config.animated ?? DEFAULT_CONFIG.animated,
  duration: config.duration ?? DEFAULT_CONFIG.duration,

  init() {
    this.initializeValue();
  },

  initializeValue() {
    if (this.animated && this.type === 'progress') {
      this.animateProgress();
    } else {
      this.value = this.targetValue;
    }
  },

  animateProgress() {
    const startTime = Date.now();
    const startValue = this.value;
    const endValue = this.targetValue;

    const animate = () => {
      const elapsedTime = Date.now() - startTime;
      const progress = Math.min(elapsedTime / this.duration, 1);
      const easedProgress = this.easeOut(progress);

      this.value = startValue + (endValue - startValue) * easedProgress;

      if (progress < 1) {
        requestAnimationFrame(animate);
      } else {
        this.value = endValue;
      }
    };

    requestAnimationFrame(animate);
  },

  easeOut(progress: number): number {
    return 1 - Math.pow(1 - progress, ANIMATION_EASING_POWER);
  },

  get sizeConfig() {
    const size = config.size ?? DEFAULT_CONFIG.size;
    return SIZE_CONFIG[size];
  },

  get sizeValue(): number {
    return this.sizeConfig.dimension;
  },

  get strokeWidth(): number {
    return this.sizeConfig.strokeWidth;
  },

  get radius(): number {
    return (this.sizeValue - this.strokeWidth) / 2;
  },

  get center(): number {
    return this.sizeValue / 2;
  },

  get viewBox(): string {
    return `0 0 ${this.sizeValue} ${this.sizeValue}`;
  },

  get circumference(): number {
    return 2 * Math.PI * this.radius;
  },

  get strokeDashoffset(): number {
    const clampedProgress = Math.min(Math.max(this.value, MIN_PROGRESS), MAX_PROGRESS);
    return this.circumference - (clampedProgress / MAX_PROGRESS) * this.circumference;
  },

  get displayValue(): number {
    return Math.round(this.value);
  },

  get displayLabel(): string {
    return this.label || `${this.displayValue}%`;
  },

  get labelText(): string {
    if (!this.showLabel) return '';
    return this.displayValue === 0 ? '0' : `${this.displayValue}%`;
  },

  get labelClass(): string {
    const baseClass = 'tutor-statics-progress-label';
    const sizeClass = config.size === 'large' ? 'tutor-statics-progress-label-large' : '';
    return `${baseClass} ${sizeClass}`.trim();
  },

  renderProgressCircle(): string {
    return `
      <svg class="tutor-statics-progress" viewBox="${this.viewBox}" width="${this.sizeValue}" height="${this.sizeValue}">
        ${this.renderBackgroundCircle()}
        ${this.renderProgressArc()}
      </svg>
      ${this.renderLabel()}
    `;
  },

  renderBackgroundCircle(): string {
    return `
      <circle 
        cx="${this.center}" 
        cy="${this.center}" 
        r="${this.radius}"
        fill="${this.background}"
        stroke="${this.strokeColor}"
        stroke-width="${this.strokeWidth}"
      ></circle>
    `;
  },

  renderProgressArc(): string {
    return `
      <circle 
        cx="${this.center}" 
        cy="${this.center}" 
        r="${this.radius}"
        fill="none"
        stroke="var(--tutor-actions-brand-primary)"
        stroke-width="${this.strokeWidth}"
        stroke-linecap="round"
        stroke-dasharray="${this.circumference}"
        stroke-dashoffset="${this.strokeDashoffset}"
        style="transition: stroke-dashoffset 0.6s ease;"
      ></circle>
    `;
  },

  renderLabel(): string {
    if (!this.showLabel) return '';
    return `<div class="${this.labelClass}">${this.labelText}</div>`;
  },

  renderCompleteCircle(): string {
    const iconSize = this.sizeConfig.iconSizes.check;
    return this.renderIconContainer('tutor-statics-complete', 'checkStroke', iconSize);
  },

  renderLockIcon(): string {
    const iconSize = this.sizeConfig.iconSizes.lock;
    return this.renderIconContainer('tutor-statics-locked', 'circumLock', iconSize);
  },

  renderIconContainer(className: string, iconName: string, iconSize: number): string {
    return `
      <div class="${className}" style="width: ${this.sizeValue}px; height: ${this.sizeValue}px;">
        <div x-data="tutorIcon({ name: '${iconName}', width: ${iconSize}, height: ${iconSize} })"></div>
      </div>
    `;
  },

  render(): string {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const $el = (this as any).$el as HTMLElement;
    $el.classList.add('tutor-statics');

    switch (this.type) {
      case 'progress':
        return this.renderProgressCircle();
      case 'complete':
        return this.renderCompleteCircle();
      case 'locked':
        return this.renderLockIcon();
      default:
        return '';
    }
  },
});

export const staticsMeta: AlpineComponentMeta<StaticsProps> = {
  name: 'statics',
  component: statics,
};
