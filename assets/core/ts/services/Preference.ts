import { type ServiceMeta } from '@Core/ts/types';
type Theme = 'dark' | 'light' | 'system';

class PreferenceService {
  private readonly THEME = { DARK: 'dark', LIGHT: 'light', SYSTEM: 'system' } as const;
  activeTheme: Theme;
  private mediaQuery: MediaQueryList;
  private systemThemeListener?: (e: MediaQueryListEvent) => void;
  private readonly BASE_FONT_SIZE = 16;
  private readonly SCALE_PERCENTAGE_BASE = 100;
  private readonly STYLE_ID = 'tutor-font-scale';
  private readonly DATA_THEME_ATTR = 'data-theme';

  constructor() {
    this.mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    this.activeTheme = this.mediaQuery.matches ? this.THEME.DARK : this.THEME.LIGHT;
    this.initialize();
  }

  initialize(): void {
    const wrapper = document.querySelector(`[${this.DATA_THEME_ATTR}]`) || document.body;
    const attrTheme = wrapper.getAttribute(this.DATA_THEME_ATTR);
    if (attrTheme === this.THEME.SYSTEM) {
      this.applyTheme(this.THEME.SYSTEM);
    }
  }

  applyTheme(theme: Theme): void {
    if (!theme) return;
    const wrapper = document.querySelector(`[${this.DATA_THEME_ATTR}]`) || document.body;

    if (this.systemThemeListener) {
      this.mediaQuery.removeEventListener('change', this.systemThemeListener);
      this.systemThemeListener = undefined;
    }

    const updateTheme = () => {
      if (theme === this.THEME.SYSTEM) {
        wrapper.setAttribute(this.DATA_THEME_ATTR, this.mediaQuery.matches ? this.THEME.DARK : this.THEME.LIGHT);
      } else {
        wrapper.setAttribute(this.DATA_THEME_ATTR, theme);
      }
    };

    updateTheme();

    if (theme === this.THEME.SYSTEM) {
      this.systemThemeListener = () => updateTheme();
      this.mediaQuery.addEventListener('change', this.systemThemeListener);
    }

    const applied = wrapper.getAttribute(this.DATA_THEME_ATTR);
    if (applied === this.THEME.DARK || applied === this.THEME.LIGHT) {
      this.activeTheme = applied;
    }
  }
  applyFontScale(fontScale: string | number): void {
    const head = document.head || document.getElementsByTagName('head')[0];
    let styleEl = document.getElementById(this.STYLE_ID) as HTMLStyleElement | null;

    if (!fontScale) {
      return;
    }

    const updateFontScale = () => {
      const scaleNum = typeof fontScale === 'string' ? parseInt(fontScale, 10) : Number(fontScale);
      if (Number.isNaN(scaleNum) || scaleNum <= 0) {
        return;
      }
      const scaledFontSize = (this.BASE_FONT_SIZE * scaleNum) / this.SCALE_PERCENTAGE_BASE;
      if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = this.STYLE_ID;
        head.appendChild(styleEl);
      }
      styleEl.textContent = `:root { font-size: ${scaledFontSize}px; }`;
    };
    updateFontScale();
  }
}

export const preferenceServiceMeta: ServiceMeta<PreferenceService> = {
  name: 'preference',
  instance: new PreferenceService(),
};
