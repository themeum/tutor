// Learning Area Entry Point
// Initializes learning area functionality based on current page

import { initializeAssignmentView } from './pages/assignment-view';
import { initializeCoursePlayer } from './pages/course-player';
import { initializeDiscussion } from './pages/discussion';
import { initializeLessonContent } from './pages/lesson-content';
import { initializeQuizInterface } from './pages/quiz-interface';

// Initialize learning area based on current page
const initializeLearningArea = () => {
  const currentPage = document.body.dataset.page || getCurrentPageFromURL();
  
  console.log('Initializing learning area page:', currentPage);
  
  // Initialize page-specific functionality
  switch (currentPage) {
    case 'course-player':
    case 'lesson-video':
      initializeCoursePlayer();
      break;
      
    case 'lesson-content':
    case 'lesson-text':
      initializeLessonContent();
      break;
      
    case 'quiz-interface':
    case 'quiz-attempt':
      initializeQuizInterface();
      break;
      
    case 'assignment-view':
    case 'assignment':
      initializeAssignmentView();
      break;
      
    case 'course-discussion':
    case 'discussion':
      initializeDiscussion();
      break;
      
    default:
      // Try to detect page type from URL or content
      const detectedType = detectPageType();
      if (detectedType) {
        initializeByType(detectedType);
      } else {
        console.warn('Unknown learning area page:', currentPage);
        // Initialize basic functionality as fallback
        initializeBasicLearning();
      }
  }
  
  // Initialize common learning area functionality
  initializeCommonFeatures();
};

const getCurrentPageFromURL = (): string => {
  const path = window.location.pathname;
  const segments = path.split('/').filter(Boolean);
  
  // Extract page identifier from URL
  if (segments.includes('learn')) {
    const learnIndex = segments.indexOf('learn');
    const nextSegment = segments[learnIndex + 1];
    
    if (nextSegment) {
      return `learning-${nextSegment}`;
    }
    return 'course-player';
  }
  
  if (segments.includes('quiz')) {
    return 'quiz-interface';
  }
  
  if (segments.includes('assignment')) {
    return 'assignment-view';
  }
  
  if (segments.includes('discussion')) {
    return 'course-discussion';
  }
  
  return 'course-player';
};

const detectPageType = (): string | null => {
  // Detect based on page content
  if (document.querySelector('.course-video-player')) {
    return 'course-player';
  }
  
  if (document.querySelector('.lesson-content-area')) {
    return 'lesson-content';
  }
  
  if (document.querySelector('.quiz-interface')) {
    return 'quiz-interface';
  }
  
  if (document.querySelector('#assignment-submission-form')) {
    return 'assignment-view';
  }
  
  if (document.querySelector('.discussions-list')) {
    return 'course-discussion';
  }
  
  return null;
};

const initializeByType = (type: string) => {
  switch (type) {
    case 'course-player':
      initializeCoursePlayer();
      break;
    case 'lesson-content':
      initializeLessonContent();
      break;
    case 'quiz-interface':
      initializeQuizInterface();
      break;
    case 'assignment-view':
      initializeAssignmentView();
      break;
    case 'course-discussion':
      initializeDiscussion();
      break;
  }
};

const initializeBasicLearning = () => {
  // Initialize basic learning functionality that works on any page
  setupBasicNavigation();
  setupBasicProgress();
  setupBasicInteractions();
};

const initializeCommonFeatures = () => {
  // Initialize course navigation
  initializeCourseNavigation();
  
  // Initialize progress tracking
  initializeProgressTracking();
  
  // Initialize sidebar functionality
  initializeSidebar();
  
  // Initialize keyboard shortcuts
  initializeKeyboardShortcuts();
  
  // Initialize accessibility features
  initializeAccessibility();
  
  // Initialize offline support
  initializeOfflineSupport();
};

const initializeCourseNavigation = () => {
  // Course navigation is handled by the CourseNavigation service
  // which is automatically initialized when imported
  console.log('Course navigation initialized');
};

const initializeProgressTracking = () => {
  // Progress tracking is handled by the ProgressTracker service
  // which is automatically initialized when imported
  console.log('Progress tracking initialized');
};

const initializeSidebar = () => {
  const sidebar = document.querySelector('.learning-sidebar');
  if (!sidebar) return;
  
  // Toggle sidebar
  const toggleBtn = document.querySelector('.sidebar-toggle');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      
      // Save state
      const isCollapsed = sidebar.classList.contains('collapsed');
      localStorage.setItem('learning_sidebar_collapsed', isCollapsed.toString());
    });
  }
  
  // Restore sidebar state
  const savedState = localStorage.getItem('learning_sidebar_collapsed');
  if (savedState === 'true') {
    sidebar.classList.add('collapsed');
  }
  
  // Auto-collapse on mobile
  if (window.innerWidth <= 768) {
    sidebar.classList.add('collapsed');
  }
};

const initializeKeyboardShortcuts = () => {
  document.addEventListener('keydown', (event) => {
    // Don't handle shortcuts when typing in input fields
    if (event.target instanceof HTMLInputElement || 
        event.target instanceof HTMLTextAreaElement ||
        event.target instanceof HTMLSelectElement) {
      return;
    }
    
    // Handle global learning shortcuts
    if (event.ctrlKey || event.metaKey) {
      switch (event.key) {
        case 'h':
          event.preventDefault();
          goToHome();
          break;
        case 'n':
          event.preventDefault();
          toggleNotes();
          break;
        case 's':
          event.preventDefault();
          toggleSidebar();
          break;
      }
    }
    
    // Handle other shortcuts
    switch (event.key) {
      case '?':
        event.preventDefault();
        showKeyboardShortcuts();
        break;
      case 'Escape':
        closeModals();
        break;
    }
  });
};

const initializeAccessibility = () => {
  // Skip to content link
  const skipLink = document.querySelector('.skip-to-content');
  if (skipLink) {
    skipLink.addEventListener('click', (event) => {
      event.preventDefault();
      const mainContent = document.querySelector('#main-content');
      if (mainContent) {
        (mainContent as HTMLElement).focus();
      }
    });
  }
  
  // High contrast mode
  const highContrastToggle = document.querySelector('.high-contrast-toggle');
  if (highContrastToggle) {
    highContrastToggle.addEventListener('click', () => {
      document.body.classList.toggle('high-contrast');
      const isHighContrast = document.body.classList.contains('high-contrast');
      localStorage.setItem('high_contrast_mode', isHighContrast.toString());
    });
  }
  
  // Restore high contrast mode
  const savedHighContrast = localStorage.getItem('high_contrast_mode');
  if (savedHighContrast === 'true') {
    document.body.classList.add('high-contrast');
  }
  
  // Font size controls
  setupFontSizeControls();
  
  // Screen reader announcements
  setupScreenReaderAnnouncements();
};

const initializeOfflineSupport = () => {
  // Check if service worker is supported
  if ('serviceWorker' in navigator) {
    // Register service worker for offline support
    navigator.serviceWorker.register('/sw.js').catch(error => {
      console.log('Service worker registration failed:', error);
    });
  }
  
  // Handle online/offline status
  window.addEventListener('online', () => {
    showOnlineStatus(true);
  });
  
  window.addEventListener('offline', () => {
    showOnlineStatus(false);
  });
  
  // Show initial status
  showOnlineStatus(navigator.onLine);
};

const setupBasicNavigation = () => {
  // Basic previous/next navigation
  const prevBtn = document.querySelector('.basic-prev-btn');
  const nextBtn = document.querySelector('.basic-next-btn');
  
  if (prevBtn) {
    prevBtn.addEventListener('click', () => {
      window.history.back();
    });
  }
  
  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      // This would need to be implemented based on course structure
      console.log('Navigate to next item');
    });
  }
};

const setupBasicProgress = () => {
  // Basic progress tracking
  const startTime = Date.now();
  
  // Track time spent on page
  window.addEventListener('beforeunload', () => {
    const timeSpent = Date.now() - startTime;
    // Save time spent to localStorage or send to server
    console.log('Time spent on page:', timeSpent);
  });
};

const setupBasicInteractions = () => {
  // Basic interaction tracking
  document.addEventListener('click', (event) => {
    const target = event.target as HTMLElement;
    
    // Track clicks on learning content
    if (target.matches('.learning-content a, .learning-content button')) {
      console.log('Learning interaction:', target.textContent);
    }
  });
};

const setupFontSizeControls = () => {
  const increaseFontBtn = document.querySelector('.increase-font-btn');
  const decreaseFontBtn = document.querySelector('.decrease-font-btn');
  const resetFontBtn = document.querySelector('.reset-font-btn');
  
  if (increaseFontBtn) {
    increaseFontBtn.addEventListener('click', () => adjustFontSize(2));
  }
  
  if (decreaseFontBtn) {
    decreaseFontBtn.addEventListener('click', () => adjustFontSize(-2));
  }
  
  if (resetFontBtn) {
    resetFontBtn.addEventListener('click', () => resetFontSize());
  }
  
  // Load saved font size
  const savedFontSize = localStorage.getItem('learning_font_size');
  if (savedFontSize) {
    document.documentElement.style.fontSize = savedFontSize + 'px';
  }
};

const setupScreenReaderAnnouncements = () => {
  // Create live region for announcements
  const liveRegion = document.createElement('div');
  liveRegion.setAttribute('aria-live', 'polite');
  liveRegion.setAttribute('aria-atomic', 'true');
  liveRegion.className = 'sr-only';
  document.body.appendChild(liveRegion);
  
  // Function to announce messages to screen readers
  window.announceToScreenReader = (message: string) => {
    liveRegion.textContent = message;
    setTimeout(() => {
      liveRegion.textContent = '';
    }, 1000);
  };
};

// Utility functions
const goToHome = () => {
  const courseId = document.body.dataset.courseId;
  if (courseId) {
    window.location.href = `/courses/${courseId}`;
  }
};

const toggleNotes = () => {
  const notesPanel = document.querySelector('.notes-panel');
  if (notesPanel) {
    notesPanel.classList.toggle('visible');
  }
};

const toggleSidebar = () => {
  const sidebar = document.querySelector('.learning-sidebar');
  if (sidebar) {
    sidebar.classList.toggle('collapsed');
  }
};

const showKeyboardShortcuts = () => {
  const modal = document.createElement('div');
  modal.className = 'keyboard-shortcuts-modal';
  modal.innerHTML = `
    <div class="modal-content">
      <div class="modal-header">
        <h3>Keyboard Shortcuts</h3>
        <button class="close-btn">&times;</button>
      </div>
      <div class="modal-body">
        <div class="shortcuts-grid">
          <div class="shortcut-item">
            <kbd>Space</kbd> or <kbd>K</kbd>
            <span>Play/Pause video</span>
          </div>
          <div class="shortcut-item">
            <kbd>←</kbd> / <kbd>→</kbd>
            <span>Previous/Next lesson</span>
          </div>
          <div class="shortcut-item">
            <kbd>Ctrl</kbd> + <kbd>H</kbd>
            <span>Go to course home</span>
          </div>
          <div class="shortcut-item">
            <kbd>Ctrl</kbd> + <kbd>N</kbd>
            <span>Toggle notes</span>
          </div>
          <div class="shortcut-item">
            <kbd>Ctrl</kbd> + <kbd>S</kbd>
            <span>Toggle sidebar</span>
          </div>
          <div class="shortcut-item">
            <kbd>?</kbd>
            <span>Show this help</span>
          </div>
        </div>
      </div>
    </div>
  `;
  
  modal.querySelector('.close-btn')?.addEventListener('click', () => {
    modal.remove();
  });
  
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.remove();
    }
  });
  
  document.body.appendChild(modal);
};

const closeModals = () => {
  const modals = document.querySelectorAll('.modal, .keyboard-shortcuts-modal');
  modals.forEach(modal => modal.remove());
};

const adjustFontSize = (delta: number) => {
  const currentSize = parseInt(getComputedStyle(document.documentElement).fontSize);
  const newSize = Math.max(12, Math.min(24, currentSize + delta));
  
  document.documentElement.style.fontSize = newSize + 'px';
  localStorage.setItem('learning_font_size', newSize.toString());
};

const resetFontSize = () => {
  document.documentElement.style.fontSize = '';
  localStorage.removeItem('learning_font_size');
};

const showOnlineStatus = (isOnline: boolean) => {
  const statusIndicator = document.querySelector('.online-status');
  if (statusIndicator) {
    statusIndicator.className = `online-status ${isOnline ? 'online' : 'offline'}`;
    statusIndicator.textContent = isOnline ? 'Online' : 'Offline';
  }
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeLearningArea);
} else {
  initializeLearningArea();
}

// Export for external use
export { initializeLearningArea };

// Extend window object for global access
declare global {
  interface Window {
    announceToScreenReader: (message: string) => void;
  }
}