// Dashboard Entry Point
// Initializes dashboard functionality based on current page

import { initializeAssignments } from './pages/assignments';
import { initializeCertificates } from './pages/certificates';
import { initializeMyCourses } from './pages/my-courses';
import { initializeOverview } from './pages/overview';
import { initializeQuizAttempts } from './pages/quiz-attempts';
import { initializeSettings } from './pages/settings';

// Initialize dashboard based on current page
const initializeDashboard = () => {
  const currentPage = document.body.dataset.page || getCurrentPageFromURL();
  
  console.log('Initializing dashboard page:', currentPage);
  
  // Initialize page-specific functionality
  switch (currentPage) {
    case 'dashboard-overview':
    case 'dashboard':
      initializeOverview();
      break;
      
    case 'dashboard-courses':
    case 'my-courses':
      initializeMyCourses();
      break;
      
    case 'dashboard-assignments':
    case 'assignments':
      initializeAssignments();
      break;
      
    case 'dashboard-quiz-attempts':
    case 'quiz-attempts':
      initializeQuizAttempts();
      break;
      
    case 'dashboard-settings':
    case 'settings':
      initializeSettings();
      break;
      
    case 'dashboard-certificates':
    case 'certificates':
      initializeCertificates();
      break;
      
    default:
      console.warn('Unknown dashboard page:', currentPage);
      // Initialize overview as fallback
      initializeOverview();
  }
  
  // Initialize common dashboard functionality
  initializeCommonFeatures();
};

const getCurrentPageFromURL = (): string => {
  const path = window.location.pathname;
  const segments = path.split('/').filter(Boolean);
  
  // Extract page identifier from URL
  if (segments.includes('dashboard')) {
    const dashboardIndex = segments.indexOf('dashboard');
    const nextSegment = segments[dashboardIndex + 1];
    
    if (nextSegment) {
      return `dashboard-${nextSegment}`;
    }
    return 'dashboard-overview';
  }
  
  return 'dashboard-overview';
};

const initializeCommonFeatures = () => {
  // Initialize sidebar navigation
  initializeSidebarNavigation();
  
  // Initialize search functionality
  initializeGlobalSearch();
  
  // Initialize notifications
  initializeNotifications();
  
  // Initialize keyboard shortcuts
  initializeKeyboardShortcuts();
};

const initializeSidebarNavigation = () => {
  const sidebar = document.querySelector('.dashboard-sidebar');
  if (!sidebar) return;
  
  // Handle active navigation states
  const currentPath = window.location.pathname;
  const navLinks = sidebar.querySelectorAll('.nav-link');
  
  navLinks.forEach(link => {
    const href = (link as HTMLAnchorElement).getAttribute('href');
    if (href && currentPath.includes(href)) {
      link.classList.add('active');
    }
  });
  
  // Handle sidebar collapse/expand
  const toggleBtn = document.querySelector('.sidebar-toggle');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      
      // Save state to localStorage
      const isCollapsed = sidebar.classList.contains('collapsed');
      localStorage.setItem('dashboard_sidebar_collapsed', isCollapsed.toString());
    });
  }
  
  // Restore sidebar state
  const savedState = localStorage.getItem('dashboard_sidebar_collapsed');
  if (savedState === 'true') {
    sidebar.classList.add('collapsed');
  }
};

const initializeGlobalSearch = () => {
  const searchInput = document.querySelector('.global-search-input') as HTMLInputElement;
  if (!searchInput) return;
  
  let searchTimeout: NodeJS.Timeout;
  
  searchInput.addEventListener('input', (event) => {
    const query = (event.target as HTMLInputElement).value;
    
    // Debounce search
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      if (query.length >= 2) {
        performGlobalSearch(query);
      } else {
        hideSearchResults();
      }
    }, 300);
  });
  
  // Handle search form submission
  const searchForm = searchInput.closest('form');
  if (searchForm) {
    searchForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const query = searchInput.value;
      if (query.trim()) {
        window.location.href = `/dashboard/search?q=${encodeURIComponent(query)}`;
      }
    });
  }
};

const performGlobalSearch = async (query: string) => {
  try {
    // This would typically call a search API
    console.log('Performing global search for:', query);
    
    // Show search results dropdown
    showSearchResults([
      { type: 'course', title: 'Sample Course', url: '/courses/1' },
      { type: 'assignment', title: 'Sample Assignment', url: '/assignments/1' }
    ]);
  } catch (error) {
    console.error('Search failed:', error);
  }
};

const showSearchResults = (results: any[]) => {
  const searchContainer = document.querySelector('.search-container');
  if (!searchContainer) return;
  
  let resultsContainer = searchContainer.querySelector('.search-results');
  if (!resultsContainer) {
    resultsContainer = document.createElement('div');
    resultsContainer.className = 'search-results';
    searchContainer.appendChild(resultsContainer);
  }
  
  resultsContainer.innerHTML = results.map(result => `
    <a href="${result.url}" class="search-result-item">
      <span class="result-type">${result.type}</span>
      <span class="result-title">${result.title}</span>
    </a>
  `).join('');
  
  resultsContainer.classList.add('visible');
};

const hideSearchResults = () => {
  const resultsContainer = document.querySelector('.search-results');
  if (resultsContainer) {
    resultsContainer.classList.remove('visible');
  }
};

const initializeNotifications = () => {
  // Check for new notifications periodically
  setInterval(checkForNotifications, 30000); // Every 30 seconds
  
  // Handle notification interactions
  document.addEventListener('click', (event) => {
    const target = event.target as HTMLElement;
    
    if (target.matches('.notification-item')) {
      handleNotificationClick(target);
    } else if (target.matches('.mark-all-read-btn')) {
      markAllNotificationsRead();
    }
  });
};

const checkForNotifications = async () => {
  try {
    // This would call the notifications API
    console.log('Checking for new notifications...');
  } catch (error) {
    console.error('Failed to check notifications:', error);
  }
};

const handleNotificationClick = (notification: HTMLElement) => {
  const notificationId = notification.dataset.notificationId;
  const url = notification.dataset.url;
  
  if (notificationId) {
    // Mark as read
    markNotificationRead(notificationId);
  }
  
  if (url) {
    window.location.href = url;
  }
};

const markNotificationRead = async (notificationId: string) => {
  try {
    // API call to mark notification as read
    console.log('Marking notification as read:', notificationId);
  } catch (error) {
    console.error('Failed to mark notification as read:', error);
  }
};

const markAllNotificationsRead = async () => {
  try {
    // API call to mark all notifications as read
    console.log('Marking all notifications as read');
  } catch (error) {
    console.error('Failed to mark all notifications as read:', error);
  }
};

const initializeKeyboardShortcuts = () => {
  document.addEventListener('keydown', (event) => {
    // Only handle shortcuts when not in input fields
    if (event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement) {
      return;
    }
    
    // Handle keyboard shortcuts
    if (event.ctrlKey || event.metaKey) {
      switch (event.key) {
        case 'k':
          event.preventDefault();
          focusGlobalSearch();
          break;
        case '/':
          event.preventDefault();
          focusGlobalSearch();
          break;
      }
    }
    
    // Handle navigation shortcuts
    switch (event.key) {
      case 'g':
        // Wait for next key to determine navigation
        handleNavigationShortcut(event);
        break;
    }
  });
};

const focusGlobalSearch = () => {
  const searchInput = document.querySelector('.global-search-input') as HTMLInputElement;
  if (searchInput) {
    searchInput.focus();
    searchInput.select();
  }
};

const handleNavigationShortcut = (event: KeyboardEvent) => {
  // This would implement navigation shortcuts like 'g h' for home, 'g c' for courses, etc.
  console.log('Navigation shortcut triggered');
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeDashboard);
} else {
  initializeDashboard();
}

// Export for external use
export { initializeDashboard };
