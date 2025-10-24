// Theme Type Definitions
// TypeScript interfaces for theme management

export type ThemeMode = 'light' | 'dark';

export interface ThemeConfig {
  theme: ThemeMode;
  autoDetect?: boolean;
  persistence?: boolean;
}

export interface ThemePreference {
  mode: ThemeMode;
  autoSwitch: boolean;
  systemPreference: boolean;
}

export interface ThemeChangeEvent {
  oldTheme: ThemeMode;
  newTheme: ThemeMode;
  source: 'user' | 'system' | 'auto';
}
