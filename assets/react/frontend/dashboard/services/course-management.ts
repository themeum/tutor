// Course Management Service
// Handles course-related functionality in dashboard

import { showNotification } from '@FrontendServices/notifications';
import { storage } from '@FrontendServices/storage';
import { DashboardAPI } from './dashboard-api';

export class CourseManager {
  private courses: any[] = [];
  private filters: any = {};

  constructor() {
    this.loadSavedFilters();
  }

  async loadCourses(filters?: any): Promise<void> {
    try {
      if (filters) {
        this.filters = { ...this.filters, ...filters };
        this.saveFilters();
      }

      this.courses = await DashboardAPI.getCourses(this.filters);
      this.renderCourses();
    } catch (error) {
      console.error('Failed to load courses:', error);
      showNotification('Failed to load courses', 'error');
    }
  }

  async searchCourses(query: string): Promise<void> {
    const searchFilters = { ...this.filters, search: query };
    await this.loadCourses(searchFilters);
  }

  async filterByStatus(status: string): Promise<void> {
    const statusFilters = { ...this.filters, status };
    await this.loadCourses(statusFilters);
  }

  async filterByCategory(category: string): Promise<void> {
    const categoryFilters = { ...this.filters, category };
    await this.loadCourses(categoryFilters);
  }

  async sortCourses(sortBy: string, order: 'asc' | 'desc' = 'asc'): Promise<void> {
    const sortFilters = { ...this.filters, sort_by: sortBy, order };
    await this.loadCourses(sortFilters);
  }

  clearFilters(): void {
    this.filters = {};
    this.saveFilters();
    this.loadCourses();
  }

  private renderCourses(): void {
    const container = document.querySelector('.courses-grid');
    if (!container) return;

    if (this.courses.length === 0) {
      container.innerHTML = this.getEmptyState();
      return;
    }

    container.innerHTML = this.courses.map(course => this.renderCourseCard(course)).join('');
    this.attachCourseEventListeners();
  }

  private renderCourseCard(course: any): string {
    const progressPercentage = course.progress || 0;
    const isCompleted = progressPercentage >= 100;

    return `
      <div class="course-card" data-course-id="${course.id}">
        <div class="course-thumbnail">
          <img src="${course.thumbnail}" alt="${course.title}" />
          ${isCompleted ? '<div class="completion-badge">Completed</div>' : ''}
        </div>
        <div class="course-content">
          <h3 class="course-title">${course.title}</h3>
          <p class="course-instructor">by ${course.instructor_name}</p>
          <div class="course-progress">
            <div class="progress-bar">
              <div class="progress-fill" style="width: ${progressPercentage}%"></div>
            </div>
            <span class="progress-text">${progressPercentage}% Complete</span>
          </div>
          <div class="course-meta">
            <span class="lessons-count">${course.total_lessons} lessons</span>
            <span class="duration">${course.duration}</span>
          </div>
        </div>
        <div class="course-actions">
          ${this.renderCourseActions(course)}
        </div>
      </div>
    `;
  }

  private renderCourseActions(course: any): string {
    const isCompleted = (course.progress || 0) >= 100;
    const isStarted = (course.progress || 0) > 0;

    if (isCompleted) {
      return `
        <button class="btn btn-success view-certificate-btn" data-course-id="${course.id}">
          View Certificate
        </button>
        <button class="btn btn-secondary review-course-btn" data-course-id="${course.id}">
          Review Course
        </button>
      `;
    } else if (isStarted) {
      return `
        <button class="btn btn-primary continue-course-btn" data-course-id="${course.id}">
          Continue Learning
        </button>
        <button class="btn btn-outline unenroll-btn" data-course-id="${course.id}">
          Unenroll
        </button>
      `;
    } else {
      return `
        <button class="btn btn-primary start-course-btn" data-course-id="${course.id}">
          Start Course
        </button>
        <button class="btn btn-outline unenroll-btn" data-course-id="${course.id}">
          Unenroll
        </button>
      `;
    }
  }

  private attachCourseEventListeners(): void {
    document.querySelectorAll('.continue-course-btn, .start-course-btn').forEach(btn => {
      btn.addEventListener('click', this.handleStartCourse.bind(this));
    });

    document.querySelectorAll('.unenroll-btn').forEach(btn => {
      btn.addEventListener('click', this.handleUnenroll.bind(this));
    });

    document.querySelectorAll('.view-certificate-btn').forEach(btn => {
      btn.addEventListener('click', this.handleViewCertificate.bind(this));
    });

    document.querySelectorAll('.review-course-btn').forEach(btn => {
      btn.addEventListener('click', this.handleReviewCourse.bind(this));
    });
  }

  private handleStartCourse(event: Event): void {
    const button = event.target as HTMLElement;
    const courseId = button.dataset.courseId;
    
    if (courseId) {
      window.location.href = `/courses/${courseId}/learn`;
    }
  }

  private async handleUnenroll(event: Event): Promise<void> {
    const button = event.target as HTMLElement;
    const courseId = button.dataset.courseId;
    
    if (!courseId) return;

    const confirmed = confirm('Are you sure you want to unenroll from this course? Your progress will be lost.');
    if (!confirmed) return;

    try {
      await DashboardAPI.unenrollFromCourse(parseInt(courseId));
      showNotification('Successfully unenrolled from course', 'success');
      
      // Remove course from current list
      this.courses = this.courses.filter(course => course.id !== parseInt(courseId));
      this.renderCourses();
    } catch (error) {
      console.error('Failed to unenroll:', error);
      showNotification('Failed to unenroll from course', 'error');
    }
  }

  private handleViewCertificate(event: Event): void {
    const button = event.target as HTMLElement;
    const courseId = button.dataset.courseId;
    
    if (courseId) {
      window.location.href = `/dashboard/certificates?course=${courseId}`;
    }
  }

  private handleReviewCourse(event: Event): void {
    const button = event.target as HTMLElement;
    const courseId = button.dataset.courseId;
    
    if (courseId) {
      window.location.href = `/courses/${courseId}/review`;
    }
  }

  private getEmptyState(): string {
    const hasFilters = Object.keys(this.filters).length > 0;
    
    if (hasFilters) {
      return `
        <div class="empty-state">
          <h3>No courses found</h3>
          <p>Try adjusting your filters or search terms.</p>
          <button class="btn btn-primary clear-filters-btn">Clear Filters</button>
        </div>
      `;
    }

    return `
      <div class="empty-state">
        <h3>No courses enrolled</h3>
        <p>You haven't enrolled in any courses yet. Start learning today!</p>
        <a href="/courses" class="btn btn-primary">Browse Courses</a>
      </div>
    `;
  }

  private loadSavedFilters(): void {
    const saved = storage.get('dashboard_course_filters');
    if (saved) {
      this.filters = saved;
    }
  }

  private saveFilters(): void {
    storage.set('dashboard_course_filters', this.filters);
  }

  // Public getters
  get currentFilters() {
    return { ...this.filters };
  }

  get courseCount() {
    return this.courses.length;
  }
}