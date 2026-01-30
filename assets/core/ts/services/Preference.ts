import { type ServiceMeta } from '@Core/ts/types';
type Theme = 'dark' | 'light' | 'system';

class PreferenceService {
  private readonly THEME = { DARK: 'dark', LIGHT: 'light' } as const;
  activeTheme: Theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? this.THEME.DARK : this.THEME.LIGHT;
  private mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
  private systemThemeListener?: (e: MediaQueryListEvent) => void;
  private readonly BASE_FONT_SIZE = 16;
  private readonly SCALE_PERCENTAGE_BASE = 100;
  private readonly STYLE_ID = 'tutor-font-scale';

  constructor() {
    this.initialize();
  }

  initialize(): void {
    const wrapper = document.querySelector('[data-theme]') || document.body;
    const attrTheme = wrapper.getAttribute('data-theme');
    if (attrTheme === 'system') {
      this.applyTheme('system');
    }
  }

  applyTheme(theme: string): void {
    if (!theme) return;
    const wrapper = document.querySelector('[data-theme]') || document.body;

    if (this.systemThemeListener) {
      this.mediaQuery.removeEventListener('change', this.systemThemeListener);
      this.systemThemeListener = undefined;
    }

    const updateTheme = () => {
      if (theme === 'system') {
        wrapper.setAttribute('data-theme', this.mediaQuery.matches ? this.THEME.DARK : this.THEME.LIGHT);
      } else {
        wrapper.setAttribute('data-theme', theme);
      }
    };

    updateTheme();

    if (theme === 'system') {
      this.systemThemeListener = () => updateTheme();
      this.mediaQuery.addEventListener('change', this.systemThemeListener);
    }

    const applied = wrapper.getAttribute('data-theme');
    if (applied === this.THEME.DARK || applied === this.THEME.LIGHT) {
      this.activeTheme = applied;
    }
  }
  applyFontScale(fontScale: string): void {
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
