// RTL Detection Utility
// Automatic RTL detection and CSS class application system

export interface RTLConfig {
  autoDetect?: boolean;
  rtlLanguages?: string[];
  fallbackDirection?: 'ltr' | 'rtl';
  classPrefix?: string;
  debugMode?: boolean;
}

export class RTLDetector {
  private config: Required<RTLConfig>;
  private currentDirection: 'ltr' | 'rtl' = 'ltr';

  // Common RTL languages
  private static readonly DEFAULT_RTL_LANGUAGES = ['ar', 'he', 'fa', 'ur', 'ps', 'sd', 'ku', 'dv', 'ug', 'yi'];

  constructor(config: RTLConfig = {}) {
    this.config = {
      autoDetect: config.autoDetect ?? true,
      rtlLanguages: config.rtlLanguages ?? RTLDetector.DEFAULT_RTL_LANGUAGES,
      fallbackDirection: config.fallbackDirection ?? 'ltr',
      classPrefix: config.classPrefix ?? 'tutor',
      debugMode: config.debugMode ?? false,
    };

    if (this.config.autoDetect) {
      this.initialize();
    }
  }

  /**
   * Initialize RTL detection
   */
  private initialize(): void {
    // Detect initial direction
    this.detectAndApplyDirection();

    // Watch for language changes
    this.observeLanguageChanges();

    // Add debug indicators if enabled
    if (this.config.debugMode) {
      this.addDebugIndicators();
    }
  }

  /**
   * Detect current text direction based on various factors
   */
  public detectDirection(): 'ltr' | 'rtl' {
    // 1. Check explicit dir attribute on html element
    const htmlDir = document.documentElement.getAttribute('dir');
    if (htmlDir === 'rtl' || htmlDir === 'ltr') {
      return htmlDir;
    }

    // 2. Check lang attribute and determine if it's RTL
    const lang = this.getCurrentLanguage();
    if (this.isRTLLanguage(lang)) {
      return 'rtl';
    }

    // 3. Check CSS direction property
    const computedStyle = window.getComputedStyle(document.documentElement);
    const cssDirection = computedStyle.direction;
    if (cssDirection === 'rtl') {
      return 'rtl';
    }

    // 4. Check browser language
    const browserLang = navigator.language.split('-')[0];
    if (this.isRTLLanguage(browserLang)) {
      return 'rtl';
    }

    // 5. Fallback to configured default
    return this.config.fallbackDirection;
  }

  /**
   * Get current language from various sources
   */
  private getCurrentLanguage(): string {
    // Check html lang attribute
    const htmlLang = document.documentElement.getAttribute('lang');
    if (htmlLang) {
      return htmlLang.split('-')[0].toLowerCase();
    }

    // Check meta tag
    const metaLang = document.querySelector('meta[http-equiv="content-language"]');
    if (metaLang) {
      const content = metaLang.getAttribute('content');
      if (content) {
        return content.split('-')[0].toLowerCase();
      }
    }

    // Fallback to browser language
    return navigator.language.split('-')[0].toLowerCase();
  }

  /**
   * Check if a language code is RTL
   */
  private isRTLLanguage(lang: string): boolean {
    return this.config.rtlLanguages.includes(lang.toLowerCase());
  }

  /**
   * Apply direction classes and attributes
   */
  private detectAndApplyDirection(): void {
    const direction = this.detectDirection();
    this.applyDirection(direction);
  }

  /**
   * Apply direction to document
   */
  public applyDirection(direction: 'ltr' | 'rtl'): void {
    const html = document.documentElement;
    const body = document.body;

    // Update current direction
    this.currentDirection = direction;

    // Set dir attribute
    html.setAttribute('dir', direction);

    // Add/remove CSS classes
    const ltrClass = `${this.config.classPrefix}-ltr`;
    const rtlClass = `${this.config.classPrefix}-rtl`;

    if (direction === 'rtl') {
      html.classList.add(rtlClass);
      html.classList.remove(ltrClass);
      body.classList.add(rtlClass);
      body.classList.remove(ltrClass);
    } else {
      html.classList.add(ltrClass);
      html.classList.remove(rtlClass);
      body.classList.add(ltrClass);
      body.classList.remove(rtlClass);
    }

    // Dispatch custom event
    const event = new CustomEvent('directionchange', {
      detail: { direction, previousDirection: this.currentDirection },
    });
    document.dispatchEvent(event);

    if (this.config.debugMode) {
      // Debug logging for direction changes
      // eslint-disable-next-line no-console
      console.log(`[RTL Detector] Direction changed to: ${direction}`);
    }
  }

  /**
   * Toggle between LTR and RTL
   */
  public toggleDirection(): void {
    const newDirection = this.currentDirection === 'ltr' ? 'rtl' : 'ltr';
    this.applyDirection(newDirection);
  }

  /**
   * Get current direction
   */
  public getCurrentDirection(): 'ltr' | 'rtl' {
    return this.currentDirection;
  }

  /**
   * Watch for language changes
   */
  private observeLanguageChanges(): void {
    // Watch for lang attribute changes
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' && (mutation.attributeName === 'lang' || mutation.attributeName === 'dir')) {
          this.detectAndApplyDirection();
        }
      });
    });

    observer.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ['lang', 'dir'],
    });
  }

  /**
   * Add debug indicators
   */
  private addDebugIndicators(): void {
    // Create direction indicator
    const indicator = document.createElement('div');
    indicator.className = `${this.config.classPrefix}-direction-indicator`;
    indicator.style.cssText = `
      position: fixed;
      top: 10px;
      right: 10px;
      background: #333;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-family: monospace;
      z-index: 10000;
      pointer-events: none;
    `;

    // Update indicator text
    const updateIndicator = () => {
      indicator.textContent = `DIR: ${this.currentDirection.toUpperCase()}`;
      indicator.style.background = this.currentDirection === 'rtl' ? '#e53e3e' : '#3182ce';
    };

    updateIndicator();
    document.body.appendChild(indicator);

    // Listen for direction changes
    document.addEventListener('directionchange', updateIndicator);

    // Create toggle button
    const toggleButton = document.createElement('button');
    toggleButton.textContent = 'Toggle Direction';
    toggleButton.className = `${this.config.classPrefix}-direction-toggle`;
    toggleButton.style.cssText = `
      position: fixed;
      top: 50px;
      right: 10px;
      background: #3182ce;
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      font-size: 12px;
      cursor: pointer;
      z-index: 10000;
    `;

    toggleButton.addEventListener('click', () => {
      this.toggleDirection();
    });

    document.body.appendChild(toggleButton);
  }

  /**
   * Add RTL testing utilities to page
   */
  public addTestingUtilities(): void {
    // Add testing CSS class to body
    document.body.classList.add(`${this.config.classPrefix}-rtl-testing-mode`);

    // Create test component wrapper
    const createTestComponent = (name: string, content: string): HTMLElement => {
      const wrapper = document.createElement('div');
      wrapper.className = `${this.config.classPrefix}-test-component`;
      wrapper.setAttribute('data-component', name);
      wrapper.innerHTML = content;
      return wrapper;
    };

    // Add to global scope for easy access
    (window as Window & { createTestComponent: typeof createTestComponent }).createTestComponent = createTestComponent;

    if (this.config.debugMode) {
      // Debug logging for testing utilities
      // eslint-disable-next-line no-console
      console.log('[RTL Detector] Testing utilities added');
    }
  }

  /**
   * Remove all RTL classes and reset to LTR
   */
  public reset(): void {
    const html = document.documentElement;
    const body = document.body;

    html.removeAttribute('dir');
    html.classList.remove(`${this.config.classPrefix}-ltr`, `${this.config.classPrefix}-rtl`);
    body.classList.remove(`${this.config.classPrefix}-ltr`, `${this.config.classPrefix}-rtl`);

    this.currentDirection = 'ltr';
  }

  /**
   * Static method to create and initialize RTL detector
   */
  static create(config?: RTLConfig): RTLDetector {
    return new RTLDetector(config);
  }
}

// Auto-initialize if in browser environment
if (typeof window !== 'undefined' && typeof document !== 'undefined') {
  // Wait for DOM to be ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      // Auto-initialize with default config
      const detector = RTLDetector.create();

      // Make available globally for debugging
      (window as Window & { tutorRTLDetector: RTLDetector }).tutorRTLDetector = detector;
    });
  } else {
    // DOM is already ready
    const detector = RTLDetector.create();
    (window as Window & { tutorRTLDetector: RTLDetector }).tutorRTLDetector = detector;
  }
}

export default RTLDetector;
