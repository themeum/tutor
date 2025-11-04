// Course Navigation Service
// Handles navigation between lessons, modules, and course sections

import { showNotification } from '@FrontendServices/notifications';
import { storage } from '@FrontendServices/storage';
import { LearningAPI } from './learning-api';
import { ProgressTracker } from './progress-tracking';

interface CourseStructure {
  courseId: number;
  modules: CourseModule[];
  totalLessons: number;
  totalQuizzes: number;
  totalAssignments: number;
}

interface CourseModule {
  id: number;
  title: string;
  description?: string;
  order: number;
  isLocked: boolean;
  items: CourseItem[];
}

interface CourseItem {
  id: number;
  type: 'lesson' | 'quiz' | 'assignment';
  title: string;
  duration?: string;
  isCompleted: boolean;
  isLocked: boolean;
  isRequired: boolean;
  order: number;
  url: string;
}

interface NavigationState {
  currentItemId: number;
  currentItemType: 'lesson' | 'quiz' | 'assignment';
  currentModuleId: number;
  hasNext: boolean;
  hasPrevious: boolean;
  nextItem?: CourseItem;
  previousItem?: CourseItem;
}

class CourseNavigationService {
  private courseStructure: CourseStructure | null = null;
  private navigationState: NavigationState | null = null;
  private navigationHistory: number[] = [];
  private maxHistorySize = 50;

  constructor() {
    this.initializeNavigation();
    this.setupKeyboardNavigation();
    this.setupNavigationUI();
  }

  // Initialize navigation system
  private async initializeNavigation(): Promise<void> {
    try {
      const courseId = this.getCurrentCourseId();
      if (!courseId) return;

      await this.loadCourseStructure(courseId);
      this.updateNavigationState();
      this.loadNavigationHistory();
      
    } catch (error) {
      console.error('Failed to initialize navigation:', error);
    }
  }

  // Load course structure from API
  private async loadCourseStructure(courseId: number): Promise<void> {
    try {
      this.courseStructure = await LearningAPI.getCourseStructure(courseId);
      this.updateItemCompletionStatus();
      
    } catch (error) {
      console.error('Failed to load course structure:', error);
      throw error;
    }
  }

  // Update completion status for all items
  private updateItemCompletionStatus(): void {
    if (!this.courseStructure) return;

    this.courseStructure.modules.forEach(module => {
      module.items.forEach(item => {
        const progress = ProgressTracker.getCurrentProgress(item.id);
        item.isCompleted = progress.isCompleted;
      });
    });
  }

  // Update current navigation state
  private updateNavigationState(): void {
    if (!this.courseStructure) return;

    const currentItemId = this.getCurrentItemId();
    const currentItemType = this.getCurrentItemType();
    
    if (!currentItemId || !currentItemType) return;

    const currentItem = this.findItemById(currentItemId);
    if (!currentItem) return;

    const currentModule = this.findModuleByItemId(currentItemId);
    const nextItem = this.getNextItem(currentItemId);
    const previousItem = this.getPreviousItem(currentItemId);

    this.navigationState = {
      currentItemId,
      currentItemType,
      currentModuleId: currentModule?.id || 0,
      hasNext: !!nextItem,
      hasPrevious: !!previousItem,
      nextItem,
      previousItem
    };

    this.updateNavigationUI();
  }

  // Setup keyboard navigation
  private setupKeyboardNavigation(): void {
    document.addEventListener('keydown', (event) => {
      // Only handle navigation when not typing in input fields
      if (event.target instanceof HTMLInputElement || 
          event.target instanceof HTMLTextAreaElement ||
          event.target instanceof HTMLSelectElement) {
        return;
      }

      // Check for modifier keys to avoid conflicts
      if (event.ctrlKey || event.metaKey || event.altKey) {
        return;
      }

      switch (event.key) {
        case 'ArrowLeft':
        case 'p':
          event.preventDefault();
          this.navigateToPrevious();
          break;
          
        case 'ArrowRight':
        case 'n':
          event.preventDefault();
          this.navigateToNext();
          break;
          
        case 'h':
          event.preventDefault();
          this.goToHome();
          break;
          
        case 'm':
          event.preventDefault();
          this.toggleModuleList();
          break;
      }
    });
  }

  // Setup navigation UI elements
  private setupNavigationUI(): void {
    // Previous/Next buttons
    const prevBtn = document.querySelector('.nav-previous-btn');
    const nextBtn = document.querySelector('.nav-next-btn');
    
    if (prevBtn) {
      prevBtn.addEventListener('click', () => this.navigateToPrevious());
    }
    
    if (nextBtn) {
      nextBtn.addEventListener('click', () => this.navigateToNext());
    }

    // Module navigation
    const moduleNav = document.querySelector('.module-navigation');
    if (moduleNav) {
      moduleNav.addEventListener('click', (event) => {
        const target = event.target as HTMLElement;
        
        if (target.matches('.module-item')) {
          const itemId = parseInt(target.dataset.itemId || '0');
          if (itemId) {
            this.navigateToItem(itemId);
          }
        }
      });
    }

    // Breadcrumb navigation
    const breadcrumbs = document.querySelector('.course-breadcrumbs');
    if (breadcrumbs) {
      breadcrumbs.addEventListener('click', (event) => {
        const target = event.target as HTMLElement;
        
        if (target.matches('.breadcrumb-link')) {
          event.preventDefault();
          const itemId = parseInt(target.dataset.itemId || '0');
          if (itemId) {
            this.navigateToItem(itemId);
          }
        }
      });
    }

    // Progress indicators
    this.setupProgressIndicators();
  }

  // Setup progress indicators
  private setupProgressIndicators(): void {
    const progressItems = document.querySelectorAll('.progress-item');
    
    progressItems.forEach(item => {
      item.addEventListener('click', (event) => {
        const target = event.target as HTMLElement;
        const itemId = parseInt(target.dataset.itemId || '0');
        
        if (itemId && this.canNavigateToItem(itemId)) {
          this.navigateToItem(itemId);
        } else if (itemId) {
          showNotification('This item is locked. Complete previous items to unlock it.', 'warning');
        }
      });
    });
  }

  // Navigate to next item
  public async navigateToNext(): Promise<void> {
    if (!this.navigationState?.hasNext || !this.navigationState.nextItem) {
      showNotification('You have reached the end of the course', 'info');
      return;
    }

    await this.navigateToItem(this.navigationState.nextItem.id);
  }

  // Navigate to previous item
  public async navigateToPrevious(): Promise<void> {
    if (!this.navigationState?.hasPrevious || !this.navigationState.previousItem) {
      showNotification('You are at the beginning of the course', 'info');
      return;
    }

    await this.navigateToItem(this.navigationState.previousItem.id);
  }

  // Navigate to specific item
  public async navigateToItem(itemId: number): Promise<void> {
    const item = this.findItemById(itemId);
    if (!item) {
      showNotification('Item not found', 'error');
      return;
    }

    // Check if item is accessible
    if (!this.canNavigateToItem(itemId)) {
      showNotification('This item is locked. Complete previous items to unlock it.', 'warning');
      return;
    }

    try {
      // Add to navigation history
      this.addToHistory(this.getCurrentItemId());
      
      // Navigate to the item
      window.location.href = item.url;
      
    } catch (error) {
      console.error('Failed to navigate to item:', error);
      showNotification('Failed to navigate to item', 'error');
    }
  }

  // Go to course home/overview
  public goToHome(): void {
    const courseId = this.getCurrentCourseId();
    if (courseId) {
      window.location.href = `/courses/${courseId}`;
    }
  }

  // Toggle module list visibility
  public toggleModuleList(): void {
    const moduleList = document.querySelector('.module-list-sidebar');
    if (moduleList) {
      moduleList.classList.toggle('visible');
    }
  }

  // Get next item in sequence
  private getNextItem(currentItemId: number): CourseItem | undefined {
    if (!this.courseStructure) return undefined;

    let foundCurrent = false;
    
    for (const module of this.courseStructure.modules) {
      for (const item of module.items) {
        if (foundCurrent && this.canNavigateToItem(item.id)) {
          return item;
        }
        if (item.id === currentItemId) {
          foundCurrent = true;
        }
      }
    }
    
    return undefined;
  }

  // Get previous item in sequence
  private getPreviousItem(currentItemId: number): CourseItem | undefined {
    if (!this.courseStructure) return undefined;

    let previousItem: CourseItem | undefined;
    
    for (const module of this.courseStructure.modules) {
      for (const item of module.items) {
        if (item.id === currentItemId) {
          return previousItem;
        }
        previousItem = item;
      }
    }
    
    return undefined;
  }

  // Check if user can navigate to item
  private canNavigateToItem(itemId: number): boolean {
    const item = this.findItemById(itemId);
    if (!item) return false;

    // Always allow navigation to completed items
    if (item.isCompleted) return true;

    // Check if item is locked
    if (item.isLocked) return false;

    // Check if all required previous items are completed
    return this.arePrerequisitesCompleted(itemId);
  }

  // Check if prerequisites are completed
  private arePrerequisitesCompleted(itemId: number): boolean {
    if (!this.courseStructure) return false;

    const targetModule = this.findModuleByItemId(itemId);
    if (!targetModule) return false;

    // Check all items before this one in the same module
    const itemIndex = targetModule.items.findIndex(item => item.id === itemId);
    
    for (let i = 0; i < itemIndex; i++) {
      const prerequisiteItem = targetModule.items[i];
      if (prerequisiteItem.isRequired && !prerequisiteItem.isCompleted) {
        return false;
      }
    }

    // Check if previous modules are completed (if required)
    const moduleIndex = this.courseStructure.modules.findIndex(module => module.id === targetModule.id);
    
    for (let i = 0; i < moduleIndex; i++) {
      const previousModule = this.courseStructure.modules[i];
      const requiredItems = previousModule.items.filter(item => item.isRequired);
      const completedRequired = requiredItems.filter(item => item.isCompleted);
      
      if (completedRequired.length < requiredItems.length) {
        return false;
      }
    }

    return true;
  }

  // Find item by ID
  private findItemById(itemId: number): CourseItem | undefined {
    if (!this.courseStructure) return undefined;

    for (const module of this.courseStructure.modules) {
      const item = module.items.find(item => item.id === itemId);
      if (item) return item;
    }
    
    return undefined;
  }

  // Find module containing item
  private findModuleByItemId(itemId: number): CourseModule | undefined {
    if (!this.courseStructure) return undefined;

    return this.courseStructure.modules.find(module =>
      module.items.some(item => item.id === itemId)
    );
  }

  // Update navigation UI elements
  private updateNavigationUI(): void {
    if (!this.navigationState) return;

    // Update previous button
    const prevBtn = document.querySelector('.nav-previous-btn') as HTMLButtonElement;
    if (prevBtn) {
      prevBtn.disabled = !this.navigationState.hasPrevious;
      if (this.navigationState.previousItem) {
        prevBtn.title = `Previous: ${this.navigationState.previousItem.title}`;
      }
    }

    // Update next button
    const nextBtn = document.querySelector('.nav-next-btn') as HTMLButtonElement;
    if (nextBtn) {
      nextBtn.disabled = !this.navigationState.hasNext;
      if (this.navigationState.nextItem) {
        nextBtn.title = `Next: ${this.navigationState.nextItem.title}`;
        nextBtn.textContent = this.navigationState.nextItem.type === 'lesson' ? 'Next Lesson' : 
                             this.navigationState.nextItem.type === 'quiz' ? 'Take Quiz' : 'View Assignment';
      }
    }

    // Update progress indicator
    this.updateProgressIndicator();

    // Update breadcrumbs
    this.updateBreadcrumbs();
  }

  // Update progress indicator
  private updateProgressIndicator(): void {
    if (!this.courseStructure || !this.navigationState) return;

    const progressContainer = document.querySelector('.course-progress-indicator');
    if (!progressContainer) return;

    const completedItems = this.getCompletedItemsCount();
    const totalItems = this.getTotalItemsCount();
    const progressPercentage = totalItems > 0 ? (completedItems / totalItems) * 100 : 0;

    progressContainer.innerHTML = `
      <div class="progress-bar">
        <div class="progress-fill" style="width: ${progressPercentage}%"></div>
      </div>
      <div class="progress-text">
        ${completedItems} of ${totalItems} completed (${Math.round(progressPercentage)}%)
      </div>
    `;
  }

  // Update breadcrumbs
  private updateBreadcrumbs(): void {
    if (!this.courseStructure || !this.navigationState) return;

    const breadcrumbsContainer = document.querySelector('.course-breadcrumbs');
    if (!breadcrumbsContainer) return;

    const currentModule = this.findModuleByItemId(this.navigationState.currentItemId);
    const currentItem = this.findItemById(this.navigationState.currentItemId);

    if (!currentModule || !currentItem) return;

    breadcrumbsContainer.innerHTML = `
      <a href="/courses/${this.courseStructure.courseId}" class="breadcrumb-link">Course Home</a>
      <span class="breadcrumb-separator">›</span>
      <span class="breadcrumb-module">${currentModule.title}</span>
      <span class="breadcrumb-separator">›</span>
      <span class="breadcrumb-current">${currentItem.title}</span>
    `;
  }

  // Navigation history management
  private addToHistory(itemId: number): void {
    if (!itemId) return;

    // Remove if already exists to avoid duplicates
    this.navigationHistory = this.navigationHistory.filter(id => id !== itemId);
    
    // Add to beginning
    this.navigationHistory.unshift(itemId);
    
    // Limit history size
    if (this.navigationHistory.length > this.maxHistorySize) {
      this.navigationHistory = this.navigationHistory.slice(0, this.maxHistorySize);
    }
    
    this.saveNavigationHistory();
  }

  private saveNavigationHistory(): void {
    const courseId = this.getCurrentCourseId();
    if (courseId) {
      storage.set(`navigation_history_${courseId}`, this.navigationHistory);
    }
  }

  private loadNavigationHistory(): void {
    const courseId = this.getCurrentCourseId();
    if (courseId) {
      this.navigationHistory = storage.get(`navigation_history_${courseId}`) || [];
    }
  }

  // Utility methods
  private getCurrentCourseId(): number {
    const courseData = document.body.dataset.courseId;
    return courseData ? parseInt(courseData) : 0;
  }

  private getCurrentItemId(): number {
    const itemData = document.body.dataset.itemId || 
                    document.body.dataset.lessonId || 
                    document.body.dataset.quizId || 
                    document.body.dataset.assignmentId;
    return itemData ? parseInt(itemData) : 0;
  }

  private getCurrentItemType(): 'lesson' | 'quiz' | 'assignment' | null {
    if (document.body.dataset.lessonId) return 'lesson';
    if (document.body.dataset.quizId) return 'quiz';
    if (document.body.dataset.assignmentId) return 'assignment';
    return null;
  }

  private getCompletedItemsCount(): number {
    if (!this.courseStructure) return 0;

    return this.courseStructure.modules.reduce((count, module) => {
      return count + module.items.filter(item => item.isCompleted).length;
    }, 0);
  }

  private getTotalItemsCount(): number {
    if (!this.courseStructure) return 0;

    return this.courseStructure.modules.reduce((count, module) => {
      return count + module.items.length;
    }, 0);
  }

  // Public API methods
  public getCourseStructure(): CourseStructure | null {
    return this.courseStructure;
  }

  public getNavigationState(): NavigationState | null {
    return this.navigationState;
  }

  public getNavigationHistory(): number[] {
    return [...this.navigationHistory];
  }

  public refreshNavigation(): Promise<void> {
    return this.initializeNavigation();
  }

  public goBack(): void {
    if (this.navigationHistory.length > 1) {
      // Skip current item (index 0) and go to previous (index 1)
      const previousItemId = this.navigationHistory[1];
      this.navigateToItem(previousItemId);
    } else {
      this.goToHome();
    }
  }

  public getCompletionStats(): { completed: number; total: number; percentage: number } {
    const completed = this.getCompletedItemsCount();
    const total = this.getTotalItemsCount();
    const percentage = total > 0 ? (completed / total) * 100 : 0;

    return { completed, total, percentage };
  }
}

// Create and export singleton instance
export const CourseNavigation = new CourseNavigationService();

// Export types
export type { CourseItem, CourseModule, CourseStructure, NavigationState };
