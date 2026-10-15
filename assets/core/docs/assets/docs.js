// Documentation App
function docsApp() {
  return {
    // State
    activeSection: 'overview',
    searchQuery: '',
    mobileMenuOpen: false,
    theme: 'light',
    isRTL: false,
    fontScale: 100,
    isMobile: window.innerWidth <= 768,

    // Navigation structure
    navigation: [
      {
        id: 'getting-started',
        title: 'Getting Started',
        items: [
          { id: 'overview', title: 'Overview' },
          { id: 'installation', title: 'Installation' },
          { id: 'basic-usage', title: 'Basic Usage' }
        ]
      },
      {
        id: 'design-system',
        title: 'Design System',
        items: [
          { id: 'design-tokens', title: 'Design Tokens' },
          { id: 'typography', title: 'Typography' },
          { id: 'colors', title: 'Colors' },
          { id: 'spacing', title: 'Spacing' }
        ]
      },
      {
        id: 'components',
        title: 'Components',
        items: [
          { id: 'components', title: 'Overview' },
          { id: 'buttons', title: 'Buttons' },
          { id: 'cards', title: 'Cards' },
          { id: 'forms', title: 'Forms' },
          { id: 'alpine-components', title: 'Alpine.js Components' }
        ]
      },
      {
        id: 'development',
        title: 'Development',
        items: [
          { id: 'sass-development', title: 'SASS Development' },
          { id: 'custom-components', title: 'Custom Components' },
          { id: 'utility-classes', title: 'Utility Classes' }
        ]
      },
      {
        id: 'features',
        title: 'Features',
        items: [
          { id: 'font-scaling', title: 'Font Scaling' },
          { id: 'rtl-support', title: 'RTL Support' },
          { id: 'theming', title: 'Theming' }
        ]
      }
    ],

    filteredNavigation: [],

    // Initialize
    init() {
      this.filteredNavigation = this.navigation;
      this.detectTheme();
      this.detectRTL();
      this.detectFontScale();
      this.handleResize();
      
      // Listen for resize events
      window.addEventListener('resize', () => {
        this.handleResize();
      });

      // Listen for hash changes
      window.addEventListener('hashchange', () => {
        this.handleHashChange();
      });

      // Handle initial hash
      this.handleHashChange();
    },

    // Navigation methods
    setActiveSection(sectionId) {
      this.activeSection = sectionId;
      this.mobileMenuOpen = false;
      
      // Update URL hash
      window.location.hash = sectionId;
      
      // Scroll to top
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    getCurrentSection() {
      for (const section of this.navigation) {
        for (const item of section.items) {
          if (item.id === this.activeSection) {
            return item;
          }
        }
      }
      return null;
    },

    filterNavigation() {
      if (!this.searchQuery.trim()) {
        this.filteredNavigation = this.navigation;
        return;
      }

      const query = this.searchQuery.toLowerCase();
      this.filteredNavigation = this.navigation.map(section => ({
        ...section,
        items: section.items.filter(item => 
          item.title.toLowerCase().includes(query) ||
          item.id.toLowerCase().includes(query)
        )
      })).filter(section => section.items.length > 0);
    },

    handleHashChange() {
      const hash = window.location.hash.slice(1);
      if (hash && this.isValidSection(hash)) {
        this.activeSection = hash;
      }
    },

    isValidSection(sectionId) {
      for (const section of this.navigation) {
        for (const item of section.items) {
          if (item.id === sectionId) {
            return true;
          }
        }
      }
      return false;
    },

    handleResize() {
      this.isMobile = window.innerWidth <= 768;
      if (!this.isMobile) {
        this.mobileMenuOpen = false;
      }
    },

    // Theme methods
    toggleTheme() {
      this.theme = this.theme === 'light' ? 'dark' : 'light';
      document.documentElement.setAttribute('data-theme', this.theme);
      localStorage.setItem('docs-theme', this.theme);
    },

    detectTheme() {
      const savedTheme = localStorage.getItem('docs-theme');
      const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      this.theme = savedTheme || systemTheme;
      document.documentElement.setAttribute('data-theme', this.theme);
    },

    // RTL methods
    toggleRTL() {
      this.isRTL = !this.isRTL;
      document.documentElement.setAttribute('dir', this.isRTL ? 'rtl' : 'ltr');
      localStorage.setItem('docs-rtl', this.isRTL);
    },

    detectRTL() {
      const savedRTL = localStorage.getItem('docs-rtl');
      this.isRTL = savedRTL === 'true';
      document.documentElement.setAttribute('dir', this.isRTL ? 'rtl' : 'ltr');
    },

    // Font scaling methods
    setFontScale(scale) {
      this.fontScale = parseInt(scale);
      
      // Remove existing font scale classes
      document.documentElement.classList.remove(
        'tutor-font-scale-80',
        'tutor-font-scale-90',
        'tutor-font-scale-100',
        'tutor-font-scale-110',
        'tutor-font-scale-120'
      );
      
      // Add new font scale class
      if (scale !== 100) {
        document.documentElement.classList.add(`tutor-font-scale-${scale}`);
      }
      
      localStorage.setItem('docs-font-scale', scale);
    },

    detectFontScale() {
      const savedScale = localStorage.getItem('docs-font-scale');
      this.fontScale = savedScale ? parseInt(savedScale) : 100;
      this.setFontScale(this.fontScale);
    },

    // Utility methods
    copyToClipboard(event) {
      const codeBlock = event.target.closest('.docs-code-block');
      const code = codeBlock.querySelector('.docs-code-block__content code');
      
      if (code) {
        const text = code.textContent;
        
        // Modern clipboard API
        if (navigator.clipboard) {
          navigator.clipboard.writeText(text).then(() => {
            this.showCopyFeedback(event.target);
          });
        } else {
          // Fallback for older browsers
          const textarea = document.createElement('textarea');
          textarea.value = text;
          document.body.appendChild(textarea);
          textarea.select();
          document.execCommand('copy');
          document.body.removeChild(textarea);
          this.showCopyFeedback(event.target);
        }
      }
    },

    showCopyFeedback(button) {
      const originalText = button.textContent;
      button.textContent = 'Copied!';
      button.style.background = 'var(--tutor-actions-success-primary)';
      button.style.color = 'white';
      button.style.borderColor = 'var(--tutor-actions-success-primary)';
      
      setTimeout(() => {
        button.textContent = originalText;
        button.style.background = '';
        button.style.color = '';
        button.style.borderColor = '';
      }, 2000);
    }
  };
}

// Color system data
const colorScales = {
  brand: [
    { name: 'Brand 100', value: '#f6f8fe', token: '$tutor-brand-100' },
    { name: 'Brand 200', value: '#e4ebfc', token: '$tutor-brand-200' },
    { name: 'Brand 300', value: '#dbe4fa', token: '$tutor-brand-300' },
    { name: 'Brand 400', value: '#a4bcf4', token: '$tutor-brand-400' },
    { name: 'Brand 500', value: '#4979e8', token: '$tutor-brand-500' },
    { name: 'Brand 600', value: '#3e64de', token: '$tutor-brand-600' },
    { name: 'Brand 700', value: '#2b49ca', token: '$tutor-brand-700' },
    { name: 'Brand 800', value: '#293da4', token: '$tutor-brand-800' },
    { name: 'Brand 900', value: '#263782', token: '$tutor-brand-900' },
    { name: 'Brand 950', value: '#1c234f', token: '$tutor-brand-950' }
  ],
  gray: [
    { name: 'Gray 25', value: '#fcfcfd', token: '$tutor-gray-25' },
    { name: 'Gray 50', value: '#f9fafb', token: '$tutor-gray-50' },
    { name: 'Gray 100', value: '#f2f4f7', token: '$tutor-gray-100' },
    { name: 'Gray 200', value: '#eaecf0', token: '$tutor-gray-200' },
    { name: 'Gray 300', value: '#d0d5dd', token: '$tutor-gray-300' },
    { name: 'Gray 400', value: '#98a2b3', token: '$tutor-gray-400' },
    { name: 'Gray 500', value: '#667085', token: '$tutor-gray-500' },
    { name: 'Gray 600', value: '#475467', token: '$tutor-gray-600' },
    { name: 'Gray 700', value: '#344054', token: '$tutor-gray-700' },
    { name: 'Gray 800', value: '#1d2939', token: '$tutor-gray-800' },
    { name: 'Gray 900', value: '#101828', token: '$tutor-gray-900' },
    { name: 'Gray 950', value: '#0c111d', token: '$tutor-gray-950' }
  ],
  success: [
    { name: 'Success 25', value: '#fafef5', token: '$tutor-success-25' },
    { name: 'Success 50', value: '#f3fee7', token: '$tutor-success-50' },
    { name: 'Success 100', value: '#e3fbcc', token: '$tutor-success-100' },
    { name: 'Success 200', value: '#d0f8ab', token: '$tutor-success-200' },
    { name: 'Success 300', value: '#a6ef67', token: '$tutor-success-300' },
    { name: 'Success 400', value: '#85e13a', token: '$tutor-success-400' },
    { name: 'Success 500', value: '#66c61c', token: '$tutor-success-500' },
    { name: 'Success 600', value: '#4ca30d', token: '$tutor-success-600' },
    { name: 'Success 700', value: '#3b7c0f', token: '$tutor-success-700' },
    { name: 'Success 800', value: '#326212', token: '$tutor-success-800' },
    { name: 'Success 900', value: '#2b5314', token: '$tutor-success-900' },
    { name: 'Success 950', value: '#15290a', token: '$tutor-success-950' }
  ],
  warning: [
    { name: 'Warning 25', value: '#fffcf5', token: '$tutor-warning-25' },
    { name: 'Warning 50', value: '#fffaeb', token: '$tutor-warning-50' },
    { name: 'Warning 100', value: '#fef0c7', token: '$tutor-warning-100' },
    { name: 'Warning 200', value: '#fedf89', token: '$tutor-warning-200' },
    { name: 'Warning 300', value: '#fec84b', token: '$tutor-warning-300' },
    { name: 'Warning 400', value: '#fdb022', token: '$tutor-warning-400' },
    { name: 'Warning 500', value: '#f79009', token: '$tutor-warning-500' },
    { name: 'Warning 600', value: '#dc6803', token: '$tutor-warning-600' },
    { name: 'Warning 700', value: '#b54708', token: '$tutor-warning-700' },
    { name: 'Warning 800', value: '#93370d', token: '$tutor-warning-800' },
    { name: 'Warning 900', value: '#7a2e0e', token: '$tutor-warning-900' },
    { name: 'Warning 950', value: '#4e1d09', token: '$tutor-warning-950' }
  ],
  error: [
    { name: 'Error 25', value: '#fffbfa', token: '$tutor-error-25' },
    { name: 'Error 50', value: '#fef3f2', token: '$tutor-error-50' },
    { name: 'Error 100', value: '#fee4e2', token: '$tutor-error-100' },
    { name: 'Error 200', value: '#fecdca', token: '$tutor-error-200' },
    { name: 'Error 300', value: '#fda29b', token: '$tutor-error-300' },
    { name: 'Error 400', value: '#f97066', token: '$tutor-error-400' },
    { name: 'Error 500', value: '#f04438', token: '$tutor-error-500' },
    { name: 'Error 600', value: '#d92d20', token: '$tutor-error-600' },
    { name: 'Error 700', value: '#b42318', token: '$tutor-error-700' },
    { name: 'Error 800', value: '#912018', token: '$tutor-error-800' },
    { name: 'Error 900', value: '#7a271a', token: '$tutor-error-900' },
    { name: 'Error 950', value: '#55160c', token: '$tutor-error-950' }
  ]
};

// Initialize when Alpine is ready
document.addEventListener('alpine:init', () => {
  // Register color scales data
  Alpine.store('colorScales', colorScales);
});

// Utility functions
window.docsUtils = {
  // Format code for display
  formatCode(code) {
    return code.trim().replace(/^\s+/gm, '');
  },

  // Generate component examples
  generateButtonExample() {
    return `<button class="tutor-btn tutor-btn--primary">Primary</button>
<button class="tutor-btn tutor-btn--secondary">Secondary</button>
<button class="tutor-btn tutor-btn--outline">Outline</button>
<button class="tutor-btn tutor-btn--ghost">Ghost</button>`;
  },

  generateCardExample() {
    return `<div class="tutor-card">
  <div class="tutor-card__header">
    <h3 class="tutor-card__title">Card Title</h3>
    <p class="tutor-card__subtitle">Card subtitle</p>
  </div>
  <div class="tutor-card__body">
    <p>Card content goes here...</p>
  </div>
  <div class="tutor-card__footer">
    <button class="tutor-btn tutor-btn--primary">Action</button>
  </div>
</div>`;
  },

  generateFormExample() {
    return `<div class="tutor-form-group">
  <label class="tutor-label" for="email">Email</label>
  <input type="email" id="email" class="tutor-input" placeholder="Enter your email" />
  <p class="tutor-help-text">We'll never share your email.</p>
</div>

<div class="tutor-form-group">
  <label class="tutor-label" for="message">Message</label>
  <textarea id="message" class="tutor-textarea" placeholder="Enter your message"></textarea>
</div>`;
  }
};

// Performance optimization: Debounce search
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Add debounced search to the app
document.addEventListener('alpine:init', () => {
  Alpine.data('docsApp', () => {
    const app = docsApp();
    app.filterNavigation = debounce(app.filterNavigation.bind(app), 300);
    return app;
  });
});