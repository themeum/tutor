import { type AlpineComponentMeta } from '@Core/types';

export interface StaticsProps {
  value?: number;
  type?: 'progress' | 'complete' | 'locked';
  size?: 'medium' | 'large';
  showLabel?: boolean;
  label?: string;
  animated?: boolean;
  duration?: number;
}

export const statics = (config: StaticsProps) => ({
  value: 0,
  targetValue: config.value || 0,
  type: config.type || 'progress',
  sizeValue: config.size === 'large' ? 144 : 44,
  strokeWidth: config.size === 'large' ? 10.8 : 3.3,
  showLabel: config.showLabel !== false,
  label: config.label || '',
  animated: config.animated !== false,
  duration: config.duration || 1000,

  init() {
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
      const elapsed = Date.now() - startTime;
      const progress = Math.min(elapsed / this.duration, 1);

      // Easing function (ease-out)
      const easeOut = 1 - Math.pow(1 - progress, 3);

      this.value = startValue + (endValue - startValue) * easeOut;

      if (progress < 1) {
        requestAnimationFrame(animate);
      } else {
        this.value = endValue;
      }
    };

    requestAnimationFrame(animate);
  },

  get radius() {
    return (this.sizeValue - this.strokeWidth) / 2;
  },

  get circumference() {
    return 2 * Math.PI * this.radius;
  },

  get strokeDashoffset() {
    const progress = Math.min(Math.max(this.value, 0), 100);
    return this.circumference - (progress / 100) * this.circumference;
  },

  get viewBox() {
    return `0 0 ${this.sizeValue} ${this.sizeValue}`;
  },

  get center() {
    return this.sizeValue / 2;
  },

  get displayValue() {
    return Math.round(this.value);
  },

  get displayLabel() {
    return this.label || `${this.displayValue}%`;
  },

  renderProgressCircle() {
    const labelText = this.showLabel ? (this.displayValue === 0 ? '0' : `${this.displayValue}%`) : '';
    const labelClass =
      'tutor-statics-progress-label ' + (config.size === 'large' ? 'tutor-statics-progress-label-large' : '');

    return `
      <svg class="tutor-statics-progress" viewBox="${this.viewBox}" width="${this.sizeValue}" height="${this.sizeValue}">
        <circle 
          cx="${this.center}" 
          cy="${this.center}" 
          r="${this.radius}"
          fill="none"
          stroke="var(--tutor-actions-brand-secondary)"
          stroke-width="${this.strokeWidth}"
        ></circle>
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
      </svg>
      ${this.showLabel ? `<div class="${labelClass}">${labelText}</div>` : ''}
    `;
  },

  renderCompleteCircle() {
    const iconSize = config.size === 'large' ? 80 : 24;

    return `
      <div class="tutor-statics-complete" style="width: ${this.sizeValue}px; height: ${this.sizeValue}px;">
        <div x-data="tutorIcon({ name: 'checkStroke', width: ${iconSize}, height: ${iconSize} })"></div>
      </div>
    `;
  },

  renderLockIcon() {
    const iconSize = config.size === 'large' ? 104 : 32;

    return `
      <div class="tutor-statics-locked" style="width: ${this.sizeValue}px; height: ${this.sizeValue}px;">
        <div x-data="tutorIcon({ name: 'circumLock', width: ${iconSize}, height: ${iconSize} })"></div>
      </div>
    `;
  },

  render() {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const $el = (this as any).$el as HTMLElement;
    $el.classList.add('tutor-statics');

    if (this.type === 'progress') {
      return this.renderProgressCircle();
    } else if (this.type === 'complete') {
      return this.renderCompleteCircle();
    } else if (this.type === 'locked') {
      return this.renderLockIcon();
    }
    return '';
  },

  setValue(newValue: number) {
    this.targetValue = newValue;
    if (this.animated) {
      this.animateProgress();
    } else {
      this.value = newValue;
    }
  },

  setType(newType: 'progress' | 'complete' | 'locked') {
    this.type = newType;
  },
});

export const staticsMeta: AlpineComponentMeta<StaticsProps> = {
  name: 'statics',
  component: statics,
};
