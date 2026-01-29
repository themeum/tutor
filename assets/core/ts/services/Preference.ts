import { type ServiceMeta } from '@Core/ts/types';

export class PreferenceService {
  defaultTheme: string = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  private mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
  private systemThemeListener?: (e: MediaQueryListEvent) => void;

  applyTheme(theme: string): void {
    const wrapper = document.querySelector('[data-theme]') || document.body;

    if (!theme) return;

    if (this.systemThemeListener) {
      this.mediaQuery.removeEventListener('change', this.systemThemeListener);
      this.systemThemeListener = undefined;
    }

    const updateTheme = () => {
      if (theme === 'system') {
        wrapper.setAttribute('data-theme', this.mediaQuery.matches ? 'dark' : 'light');
      } else {
        wrapper.setAttribute('data-theme', theme);
      }
    };

    updateTheme();

    if (theme === 'system') {
      this.systemThemeListener = () => updateTheme();
      this.mediaQuery.addEventListener('change', this.systemThemeListener);
    }

    this.defaultTheme = theme;
  }
  applyFontScale(fontScale: string): void {
    const head = document.head || document.getElementsByTagName('head')[0];
    const styleId = 'tutor-font-scale';
    let styleEl = document.getElementById(styleId) as HTMLStyleElement | null;

    if (!fontScale) {
      return;
    }

    const updateFontScale = () => {
      const scaleNum = typeof fontScale === 'string' ? parseInt(fontScale, 10) : Number(fontScale);
      if (Number.isNaN(scaleNum) || scaleNum <= 0) {
        return;
      }
      const base = 16;
      const px = (base * scaleNum) / 100;
      if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = styleId;
        head.appendChild(styleEl);
      }
      styleEl.textContent = `:root { font-size: ${px}px; }`;
    };
    updateFontScale();
  }
}

export const preferenceServiceMeta: ServiceMeta<PreferenceService> = {
  name: 'preference',
  instance: new PreferenceService(),
};
