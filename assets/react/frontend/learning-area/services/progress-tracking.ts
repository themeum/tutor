// Progress Tracking Service
// Handles learning progress tracking and analytics

import { storage } from '@FrontendServices/storage';
import { LearningAPI } from './learning-api';

interface LessonProgress {
  lessonId: number;
  timeSpent: number;
  currentPosition: number;
  isCompleted: boolean;
  lastAccessed: number;
  readingProgress?: number;
  videoProgress?: number;
}

interface CourseProgress {
  courseId: number;
  totalTimeSpent: number;
  completedLessons: number;
  totalLessons: number;
  lastActivity: number;
  overallProgress: number;
}

class ProgressTrackingService {
  private isTracking: boolean = false;
  private currentSession: {
    lessonId?: number;
    startTime: number;
    timeSpent: number;
    isActive: boolean;
  } = {
    startTime: Date.now(),
    timeSpent: 0,
    isActive: true
  };

  private syncQueue: any[] = [];
  private syncInterval: NodeJS.Timeout | null = null;

  constructor() {
    this.initializeTracking();
    this.setupVisibilityTracking();
    this.setupSyncScheduler();
    this.loadStoredProgress();
  }

  // Initialize progress tracking
  private initializeTracking(): void {
    this.isTracking = true;
    this.startSession();
  }

  // Setup visibility change tracking
  private setupVisibilityTracking(): void {
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        this.pauseTracking();
      } else {
        this.resumeTracking();
      }
    });

    // Track when user leaves the page
    window.addEventListener('beforeunload', () => {
      this.endSession();
      this.syncProgress();
    });

    // Track focus/blur events
    window.addEventListener('focus', () => {
      this.resumeTracking();
    });

    window.addEventListener('blur', () => {
      this.pauseTracking();
    });
  }

  // Setup automatic sync scheduler
  private setupSyncScheduler(): void {
    // Sync progress every 30 seconds
    this.syncInterval = setInterval(() => {
      this.syncProgress();
    }, 30000);
  }

  // Start a new learning session
  startSession(lessonId?: number): void {
    this.currentSession = {
      lessonId,
      startTime: Date.now(),
      timeSpent: 0,
      isActive: true
    };

    if (lessonId) {
      this.trackLessonStart(lessonId);
    }
  }

  // End current session
  endSession(): void {
    if (this.currentSession.lessonId && this.currentSession.isActive) {
      this.addTimeSpent(this.currentSession.lessonId, this.getSessionTime());
    }

    this.currentSession.isActive = false;
  }

  // Pause tracking (when user becomes inactive)
  pauseTracking(): void {
    if (this.currentSession.isActive && this.currentSession.lessonId) {
      const sessionTime = this.getSessionTime();
      this.addTimeSpent(this.currentSession.lessonId, sessionTime);
      this.currentSession.startTime = Date.now();
    }
    this.currentSession.isActive = false;
  }

  // Resume tracking (when user becomes active)
  resumeTracking(): void {
    if (!this.currentSession.isActive) {
      this.currentSession.startTime = Date.now();
      this.currentSession.isActive = true;
    }
  }

  // Update lesson progress
  updateLessonProgress(lessonId: number, currentPosition: number, totalDuration?: number): void {
    const progress = this.getLessonProgress(lessonId);
    
    progress.currentPosition = currentPosition;
    progress.lastAccessed = Date.now();

    // Calculate video progress percentage
    if (totalDuration && totalDuration > 0) {
      progress.videoProgress = Math.min(100, (currentPosition / totalDuration) * 100);
      
      // Mark as completed if watched 90% or more
      if (progress.videoProgress >= 90 && !progress.isCompleted) {
        this.markLessonCompleted(lessonId);
      }
    }

    this.saveLessonProgress(lessonId, progress);
    this.queueForSync('lesson_progress', { lessonId, progress });
  }

  // Update reading progress for text-based lessons
  updateReadingProgress(lessonId: number, scrollPercentage: number): void {
    const progress = this.getLessonProgress(lessonId);
    
    progress.readingProgress = Math.max(progress.readingProgress || 0, scrollPercentage);
    progress.lastAccessed = Date.now();

    // Mark as completed if read 90% or more
    if (progress.readingProgress >= 90 && !progress.isCompleted) {
      this.markLessonCompleted(lessonId);
    }

    this.saveLessonProgress(lessonId, progress);
    this.queueForSync('reading_progress', { lessonId, scrollPercentage });
  }

  // Add time spent on a lesson
  addTimeSpent(lessonId: number, timeSpent: number): void {
    if (timeSpent <= 0) return;

    const progress = this.getLessonProgress(lessonId);
    progress.timeSpent += timeSpent;
    progress.lastAccessed = Date.now();

    this.saveLessonProgress(lessonId, progress);
    this.updateCourseProgress(lessonId, timeSpent);
    
    this.queueForSync('time_spent', { lessonId, timeSpent });
  }

  // Mark lesson as completed
  markLessonCompleted(lessonId: number): void {
    const progress = this.getLessonProgress(lessonId);
    
    if (!progress.isCompleted) {
      progress.isCompleted = true;
      progress.lastAccessed = Date.now();
      
      this.saveLessonProgress(lessonId, progress);
      this.updateCourseCompletionStats(lessonId);
      
      this.queueForSync('lesson_completed', { lessonId });
      
      // Trigger completion event
      this.triggerLessonCompletedEvent(lessonId);
    }
  }

  // Track lesson start
  private trackLessonStart(lessonId: number): void {
    const progress = this.getLessonProgress(lessonId);
    progress.lastAccessed = Date.now();
    
    this.saveLessonProgress(lessonId, progress);
    this.queueForSync('lesson_started', { lessonId });
  }

  // Get lesson progress
  private getLessonProgress(lessonId: number): LessonProgress {
    const stored = storage.get(`lesson_progress_${lessonId}`);
    
    return stored || {
      lessonId,
      timeSpent: 0,
      currentPosition: 0,
      isCompleted: false,
      lastAccessed: Date.now(),
      readingProgress: 0,
      videoProgress: 0
    };
  }

  // Save lesson progress
  private saveLessonProgress(lessonId: number, progress: LessonProgress): void {
    storage.set(`lesson_progress_${lessonId}`, progress);
  }

  // Update course-level progress
  private updateCourseProgress(lessonId: number, timeSpent: number): void {
    const courseId = this.getCourseIdFromLesson(lessonId);
    if (!courseId) return;

    const courseProgress = this.getCourseProgress(courseId);
    courseProgress.totalTimeSpent += timeSpent;
    courseProgress.lastActivity = Date.now();

    this.saveCourseProgress(courseId, courseProgress);
  }

  // Update course completion statistics
  private updateCourseCompletionStats(lessonId: number): void {
    const courseId = this.getCourseIdFromLesson(lessonId);
    if (!courseId) return;

    const courseProgress = this.getCourseProgress(courseId);
    courseProgress.completedLessons += 1;
    courseProgress.overallProgress = (courseProgress.completedLessons / courseProgress.totalLessons) * 100;
    courseProgress.lastActivity = Date.now();

    this.saveCourseProgress(courseId, courseProgress);
    
    // Check if course is completed
    if (courseProgress.completedLessons >= courseProgress.totalLessons) {
      this.triggerCourseCompletedEvent(courseId);
    }
  }

  // Get course progress
  private getCourseProgress(courseId: number): CourseProgress {
    const stored = storage.get(`course_progress_${courseId}`);
    
    return stored || {
      courseId,
      totalTimeSpent: 0,
      completedLessons: 0,
      totalLessons: this.getTotalLessonsCount(courseId),
      lastActivity: Date.now(),
      overallProgress: 0
    };
  }

  // Save course progress
  private saveCourseProgress(courseId: number, progress: CourseProgress): void {
    storage.set(`course_progress_${courseId}`, progress);
  }

  // Get current session time
  private getSessionTime(): number {
    if (!this.currentSession.isActive) return 0;
    return Date.now() - this.currentSession.startTime;
  }

  // Queue data for sync with server
  private queueForSync(type: string, data: any): void {
    this.syncQueue.push({
      type,
      data,
      timestamp: Date.now()
    });

    // Limit queue size
    if (this.syncQueue.length > 100) {
      this.syncQueue = this.syncQueue.slice(-50);
    }
  }

  // Sync progress with server
  private async syncProgress(): Promise<void> {
    if (this.syncQueue.length === 0) return;

    try {
      const dataToSync = [...this.syncQueue];
      this.syncQueue = [];

      // Group by type for efficient API calls
      const groupedData = this.groupSyncData(dataToSync);

      // Sync different types of data
      await Promise.all([
        this.syncTimeSpent(groupedData.time_spent || []),
        this.syncLessonProgress(groupedData.lesson_progress || []),
        this.syncReadingProgress(groupedData.reading_progress || []),
        this.syncCompletions(groupedData.lesson_completed || [])
      ]);

    } catch (error) {
      console.error('Failed to sync progress:', error);
      // Re-queue failed items (keep only recent ones)
      this.syncQueue = [...this.syncQueue.slice(-20)];
    }
  }

  // Group sync data by type
  private groupSyncData(data: any[]): Record<string, any[]> {
    return data.reduce((groups, item) => {
      groups[item.type] = groups[item.type] || [];
      groups[item.type].push(item.data);
      return groups;
    }, {} as Record<string, any[]>);
  }

  // Sync time spent data
  private async syncTimeSpent(data: any[]): Promise<void> {
    if (data.length === 0) return;

    // Aggregate time spent by lesson
    const aggregated = data.reduce((acc, item) => {
      acc[item.lessonId] = (acc[item.lessonId] || 0) + item.timeSpent;
      return acc;
    }, {} as Record<number, number>);

    // Send to server
    for (const [lessonId, timeSpent] of Object.entries(aggregated)) {
      await LearningAPI.trackLearningTime(parseInt(lessonId), timeSpent);
    }
  }

  // Sync lesson progress data
  private async syncLessonProgress(data: any[]): Promise<void> {
    if (data.length === 0) return;

    // Use latest progress for each lesson
    const latest = data.reduce((acc, item) => {
      acc[item.lessonId] = item.progress;
      return acc;
    }, {} as Record<number, any>);

    // Send to server
    for (const [lessonId, progress] of Object.entries(latest)) {
      if (progress.videoProgress !== undefined) {
        await LearningAPI.trackVideoProgress(parseInt(lessonId), {
          current_time: progress.currentPosition,
          duration: progress.currentPosition / (progress.videoProgress / 100),
          completed: progress.isCompleted
        });
      }
    }
  }

  // Sync reading progress data
  private async syncReadingProgress(data: any[]): Promise<void> {
    if (data.length === 0) return;

    // Use latest reading progress for each lesson
    const latest = data.reduce((acc, item) => {
      if (!acc[item.lessonId] || acc[item.lessonId] < item.scrollPercentage) {
        acc[item.lessonId] = item.scrollPercentage;
      }
      return acc;
    }, {} as Record<number, number>);

    // Send to server
    for (const [lessonId, scrollPercentage] of Object.entries(latest)) {
      await LearningAPI.saveProgress(parseInt(lessonId), scrollPercentage);
    }
  }

  // Sync lesson completions
  private async syncCompletions(data: any[]): Promise<void> {
    if (data.length === 0) return;

    // Remove duplicates
    const uniqueLessons = [...new Set(data.map(item => item.lessonId))];

    // Send to server
    for (const lessonId of uniqueLessons) {
      await LearningAPI.completeLesson(lessonId);
    }
  }

  // Load stored progress on initialization
  private loadStoredProgress(): void {
    // This could load progress from server on app start
    // For now, we rely on localStorage
  }

  // Trigger lesson completed event
  private triggerLessonCompletedEvent(lessonId: number): void {
    const event = new CustomEvent('lessonCompleted', {
      detail: { lessonId }
    });
    document.dispatchEvent(event);
  }

  // Trigger course completed event
  private triggerCourseCompletedEvent(courseId: number): void {
    const event = new CustomEvent('courseCompleted', {
      detail: { courseId }
    });
    document.dispatchEvent(event);
  }

  // Utility methods
  private getCourseIdFromLesson(lessonId: number): number | null {
    // This would typically be stored or retrieved from the DOM/API
    const courseData = document.body.dataset.courseId;
    return courseData ? parseInt(courseData) : null;
  }

  private getTotalLessonsCount(courseId: number): number {
    // This would typically be retrieved from the course data
    const lessonsData = document.body.dataset.totalLessons;
    return lessonsData ? parseInt(lessonsData) : 0;
  }

  // Public API methods
  public getCurrentProgress(lessonId: number): LessonProgress {
    return this.getLessonProgress(lessonId);
  }

  public getCourseOverallProgress(courseId: number): CourseProgress {
    return this.getCourseProgress(courseId);
  }

  public getSessionStats(): any {
    return {
      ...this.currentSession,
      currentSessionTime: this.getSessionTime()
    };
  }

  public forceSync(): Promise<void> {
    return this.syncProgress();
  }

  public resetProgress(lessonId: number): void {
    storage.remove(`lesson_progress_${lessonId}`);
    this.queueForSync('progress_reset', { lessonId });
  }

  public exportProgress(): any {
    const allKeys = storage.getAllKeys();
    const progressKeys = allKeys.filter(key => 
      key.startsWith('lesson_progress_') || key.startsWith('course_progress_')
    );

    const exportData: any = {};
    progressKeys.forEach(key => {
      exportData[key] = storage.get(key);
    });

    return {
      progress: exportData,
      exportDate: new Date().toISOString(),
      version: '1.0'
    };
  }

  public importProgress(data: any): void {
    if (!data.progress) return;

    Object.entries(data.progress).forEach(([key, value]) => {
      storage.set(key, value);
    });
  }

  // Cleanup method
  public destroy(): void {
    this.endSession();
    this.syncProgress();
    
    if (this.syncInterval) {
      clearInterval(this.syncInterval);
    }
  }
}

// Create and export singleton instance
export const ProgressTracker = new ProgressTrackingService();

// Export types
export type { CourseProgress, LessonProgress };
