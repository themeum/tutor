import { type ServiceMeta } from '@Core/ts/types';
type Theme = 'dark' | 'light' | 'system';
type Vision = 'normal' | 'protanopia' | 'deuteranopia' | 'deuteranomaly';
type Contrast = '' | 'high';
type Motion = '' | 'auto' | 'reduce' | 'standard';

class PreferenceService {
  private readonly THEME = { DARK: 'dark', LIGHT: 'light', SYSTEM: 'system' } as const;
  activeTheme: Theme;
  private mediaQuery: MediaQueryList;
  private systemThemeListener?: (e: MediaQueryListEvent) => void;
  private readonly BASE_FONT_SIZE = 16;
  private readonly SCALE_PERCENTAGE_BASE = 100;
  private readonly STYLE_ID = 'tutor-font-scale';
  private readonly DATA_THEME_ATTR = 'data-tutor-theme';
  private readonly DATA_VISION_ATTR = 'data-tutor-vision';
  private readonly DATA_CONTRAST_ATTR = 'data-tutor-contrast';
  private readonly DATA_MOTION_ATTR = 'data-tutor-motion';

  constructor() {
    this.mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    this.activeTheme = this.mediaQuery.matches ? this.THEME.DARK : this.THEME.LIGHT;
    this.initialize();
  }

  private getWrapper(): HTMLElement {
    return (document.querySelector(`[${this.DATA_THEME_ATTR}]`) as HTMLElement | null) || document.documentElement;
  }

  private inferBase(themeAttr: string | null): 'dark' | 'light' {
    return themeAttr?.startsWith('dark') ? 'dark' : 'light';
  }

  initialize(): void {
    const wrapper = this.getWrapper();
    const attrTheme = wrapper.getAttribute(this.DATA_THEME_ATTR) as Theme | null;

    // If the saved preference is "system", re-apply to attach listener and compute correct attr.
    if (attrTheme === this.THEME.SYSTEM) this.applyTheme(this.THEME.SYSTEM, false);

    const contrast = (wrapper.getAttribute(this.DATA_CONTRAST_ATTR) as Contrast | null) ?? '';
    if (contrast) {
      this.applyContrast(contrast);
    }

    const motion = (wrapper.getAttribute(this.DATA_MOTION_ATTR) as Motion | null) ?? '';
    this.applyMotionEffects(motion as Motion);
  }

  applyTheme(theme: Theme, withTransition: boolean = true): void {
    if (!theme) return;
    const wrapper = this.getWrapper();

    // Resolve what the new effective theme would be.
    const incomingEffectiveTheme =
      theme === this.THEME.SYSTEM ? (this.mediaQuery.matches ? this.THEME.DARK : this.THEME.LIGHT) : theme;

    // Skip transition if the effective theme hasn't changed.
    const currentAttr = wrapper.getAttribute(this.DATA_THEME_ATTR);
    const effectiveCurrent = this.inferBase(currentAttr);
    if (incomingEffectiveTheme === effectiveCurrent && theme !== this.THEME.SYSTEM) {
      return;
    }

    if (this.systemThemeListener) {
      this.mediaQuery.removeEventListener('change', this.systemThemeListener);
      this.systemThemeListener = undefined;
    }

    const updateTheme = () => {
      if (theme === this.THEME.SYSTEM) {
        const base = this.mediaQuery.matches ? this.THEME.DARK : this.THEME.LIGHT;
        wrapper.setAttribute(this.DATA_THEME_ATTR, base);
      } else {
        wrapper.setAttribute(this.DATA_THEME_ATTR, theme);
      }
    };

    const applyLogic = () => {
      updateTheme();

      if (theme === this.THEME.SYSTEM) {
        this.systemThemeListener = () => updateTheme();
        this.mediaQuery.addEventListener('change', this.systemThemeListener);
      }

      const applied = wrapper.getAttribute(this.DATA_THEME_ATTR);
      this.activeTheme = this.inferBase(applied);
    };

    if (withTransition && document.startViewTransition) {
      document.startViewTransition(() => {
        applyLogic();
      });
    } else {
      applyLogic();
    }
  }

  applyContrast(contrast: Contrast): void {
    const wrapper = this.getWrapper();
    if (contrast === 'high') {
      wrapper.setAttribute(this.DATA_CONTRAST_ATTR, 'high');
    } else {
      wrapper.removeAttribute(this.DATA_CONTRAST_ATTR);
    }
  }

  applyVision(vision: Vision): void {
    const wrapper = this.getWrapper();
    const safeVision: Vision =
      vision === 'protanopia' || vision === 'deuteranopia' || vision === 'deuteranomaly' ? vision : 'normal';

    if (safeVision === 'normal') {
      wrapper.removeAttribute(this.DATA_VISION_ATTR);
    } else {
      wrapper.setAttribute(this.DATA_VISION_ATTR, safeVision);
    }
  }

  applyMotionEffects(motion: Motion): void {
    const wrapper = this.getWrapper();
    if (motion === 'reduce') {
      wrapper.setAttribute(this.DATA_MOTION_ATTR, 'reduce');
    } else if (motion === 'auto') {
      wrapper.setAttribute(this.DATA_MOTION_ATTR, 'auto');
    } else {
      wrapper.removeAttribute(this.DATA_MOTION_ATTR);
    }
  }

  applyFontScale(fontScale: string | number): void {
    if (!fontScale) return;

    const head = document.head;
    let styleEl = document.getElementById(this.STYLE_ID) as HTMLStyleElement | null;
    const scaleNum = typeof fontScale === 'string' ? parseInt(fontScale, 10) : Number(fontScale);
    if (Number.isNaN(scaleNum) || scaleNum <= 0) return;

    const scaledFontSize = (this.BASE_FONT_SIZE * scaleNum) / this.SCALE_PERCENTAGE_BASE;
    if (!styleEl) {
      styleEl = document.createElement('style');
      styleEl.id = this.STYLE_ID;
      head.appendChild(styleEl);
    }
    styleEl.textContent = `:root { font-size: ${scaledFontSize}px; }`;
  }
}

export const preferenceServiceMeta: ServiceMeta<PreferenceService> = {
  name: 'preference',
  instance: new PreferenceService(),
};
