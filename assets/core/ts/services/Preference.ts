import { type ServiceMeta } from '@Core/ts/types';

export class PreferenceService {
  theme: string = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  fontScale: string = '100';
  applyTheme(theme: string): void {
    const wrapper = document.querySelector('[data-theme]') || document.body;
    const currentTheme = wrapper.getAttribute('data-theme');
    if (!theme) {
      return;
    }

    const updateTheme = () => {
      if (theme === 'system') {
        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        wrapper.setAttribute('data-theme', isDark ? 'dark' : 'light');
      } else {
        wrapper.setAttribute('data-theme', theme);
      }
    };

    updateTheme();

    if (currentTheme === 'system') {
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateTheme);
    }
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
