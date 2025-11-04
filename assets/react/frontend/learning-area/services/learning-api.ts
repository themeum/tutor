// Learning Area API Service
// Handles all learning-related API calls

import { apiClient } from '@FrontendServices/api/client';
import type { Assignment, Discussion, Lesson, Quiz } from '@FrontendTypes';

export const LearningAPI = {
  // Lesson Management
  async getLesson(lessonId: number): Promise<Lesson> {
    const response = await apiClient.get(`/lessons/${lessonId}`);
    return response.data;
  },

  async completeLesson(lessonId: number): Promise<void> {
    await apiClient.post(`/lessons/${lessonId}/complete`);
  },

  async markLessonStarted(lessonId: number): Promise<void> {
    await apiClient.post(`/lessons/${lessonId}/start`);
  },

  async getNextLesson(currentLessonId: number): Promise<Lesson | null> {
    const response = await apiClient.get(`/lessons/${currentLessonId}/next`);
    return response.data;
  },

  async getPrevLesson(currentLessonId: number): Promise<Lesson | null> {
    const response = await apiClient.get(`/lessons/${currentLessonId}/previous`);
    return response.data;
  },

  async saveProgress(lessonId: number, currentTime: number): Promise<void> {
    await apiClient.post(`/lessons/${lessonId}/progress`, {
      current_time: currentTime,
      timestamp: Date.now()
    });
  },

  async toggleBookmark(lessonId: number): Promise<boolean> {
    const response = await apiClient.post(`/lessons/${lessonId}/bookmark`);
    return response.data.is_bookmarked;
  },

  async trackAttachmentDownload(attachmentId: number): Promise<void> {
    await apiClient.post(`/attachments/${attachmentId}/download`);
  },

  // Quiz Management
  async getQuiz(quizId: number): Promise<Quiz> {
    const response = await apiClient.get(`/quizzes/${quizId}`);
    return response.data;
  },

  async startQuiz(quizId: number): Promise<any> {
    const response = await apiClient.post(`/quizzes/${quizId}/start`);
    return response.data;
  },

  async submitQuiz(quizId: number, data: { answers: Record<number, any>; time_taken: number }): Promise<any> {
    const response = await apiClient.post(`/quizzes/${quizId}/submit`, data);
    return response.data;
  },

  async saveQuizProgress(quizId: number, answers: Record<number, any>): Promise<void> {
    await apiClient.post(`/quizzes/${quizId}/save-progress`, { answers });
  },

  async getQuizAttempt(attemptId: number): Promise<any> {
    const response = await apiClient.get(`/quiz-attempts/${attemptId}`);
    return response.data;
  },

  // Assignment Management
  async getAssignment(assignmentId: number): Promise<Assignment> {
    const response = await apiClient.get(`/assignments/${assignmentId}`);
    return response.data;
  },

  async submitAssignment(assignmentId: number, formData: FormData): Promise<any> {
    const response = await apiClient.post(`/assignments/${assignmentId}/submit`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  async getAssignmentSubmission(assignmentId: number): Promise<any> {
    const response = await apiClient.get(`/assignments/${assignmentId}/submission`);
    return response.data;
  },

  async getAssignmentInstructionsDownload(assignmentId: number): Promise<string> {
    const response = await apiClient.get(`/assignments/${assignmentId}/instructions-download`);
    return response.data.download_url;
  },

  async saveAssignmentDraft(assignmentId: number, content: string): Promise<void> {
    await apiClient.post(`/assignments/${assignmentId}/save-draft`, { content });
  },

  // Discussion Management
  async getDiscussions(courseId: number, filters?: any): Promise<Discussion[]> {
    const params = filters ? new URLSearchParams(filters).toString() : '';
    const response = await apiClient.get(`/courses/${courseId}/discussions${params ? '?' + params : ''}`);
    return response.data;
  },

  async createDiscussion(courseId: number, formData: FormData): Promise<Discussion> {
    const response = await apiClient.post(`/courses/${courseId}/discussions`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  async createReply(discussionId: number, formData: FormData): Promise<any> {
    const response = await apiClient.post(`/discussions/${discussionId}/replies`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  async likeDiscussion(discussionId: number): Promise<void> {
    await apiClient.post(`/discussions/${discussionId}/like`);
  },

  async unlikeDiscussion(discussionId: number): Promise<void> {
    await apiClient.delete(`/discussions/${discussionId}/like`);
  },

  async resolveDiscussion(discussionId: number): Promise<void> {
    await apiClient.post(`/discussions/${discussionId}/resolve`);
  },

  async unresolveDiscussion(discussionId: number): Promise<void> {
    await apiClient.delete(`/discussions/${discussionId}/resolve`);
  },

  async getDiscussionUpdates(courseId: number, since: string): Promise<any[]> {
    const response = await apiClient.get(`/courses/${courseId}/discussions/updates?since=${since}`);
    return response.data;
  },

  // Course Navigation
  async getCourseStructure(courseId: number): Promise<any> {
    const response = await apiClient.get(`/courses/${courseId}/structure`);
    return response.data;
  },

  async getCourseProgress(courseId: number): Promise<any> {
    const response = await apiClient.get(`/courses/${courseId}/progress`);
    return response.data;
  },

  async updateCourseProgress(courseId: number, data: any): Promise<void> {
    await apiClient.post(`/courses/${courseId}/progress`, data);
  },

  // Interactive Content
  async submitPollResponse(pollId: number, optionId: number): Promise<any> {
    const response = await apiClient.post(`/polls/${pollId}/respond`, { option_id: optionId });
    return response.data;
  },

  async submitInlineQuiz(quizId: number, answers: Record<string, any>): Promise<any> {
    const response = await apiClient.post(`/inline-quizzes/${quizId}/submit`, { answers });
    return response.data;
  },

  async saveChecklistProgress(lessonId: number, checklistData: any): Promise<void> {
    await apiClient.post(`/lessons/${lessonId}/checklist`, checklistData);
  },

  // Notes and Annotations
  async saveNote(lessonId: number, note: { content: string; timestamp?: number; position?: any }): Promise<any> {
    const response = await apiClient.post(`/lessons/${lessonId}/notes`, note);
    return response.data;
  },

  async getNotes(lessonId: number): Promise<any[]> {
    const response = await apiClient.get(`/lessons/${lessonId}/notes`);
    return response.data;
  },

  async updateNote(noteId: number, content: string): Promise<void> {
    await apiClient.put(`/notes/${noteId}`, { content });
  },

  async deleteNote(noteId: number): Promise<void> {
    await apiClient.delete(`/notes/${noteId}`);
  },

  // Bookmarks and Favorites
  async getBookmarks(courseId: number): Promise<any[]> {
    const response = await apiClient.get(`/courses/${courseId}/bookmarks`);
    return response.data;
  },

  async addBookmark(lessonId: number, data: { title: string; timestamp?: number }): Promise<any> {
    const response = await apiClient.post(`/lessons/${lessonId}/bookmarks`, data);
    return response.data;
  },

  async removeBookmark(bookmarkId: number): Promise<void> {
    await apiClient.delete(`/bookmarks/${bookmarkId}`);
  },

  // Learning Analytics
  async trackLearningTime(lessonId: number, timeSpent: number): Promise<void> {
    await apiClient.post(`/analytics/learning-time`, {
      lesson_id: lessonId,
      time_spent: timeSpent,
      timestamp: Date.now()
    });
  },

  async trackVideoProgress(lessonId: number, data: { current_time: number; duration: number; completed: boolean }): Promise<void> {
    await apiClient.post(`/analytics/video-progress`, {
      lesson_id: lessonId,
      ...data,
      timestamp: Date.now()
    });
  },

  async trackQuizPerformance(quizId: number, data: { score: number; time_taken: number; attempts: number }): Promise<void> {
    await apiClient.post(`/analytics/quiz-performance`, {
      quiz_id: quizId,
      ...data,
      timestamp: Date.now()
    });
  },

  // Content Interaction
  async reportContent(contentType: 'discussion' | 'reply' | 'lesson', contentId: number, reason: string): Promise<void> {
    await apiClient.post(`/content/report`, {
      content_type: contentType,
      content_id: contentId,
      reason,
    });
  },

  async rateContent(contentType: 'lesson' | 'quiz' | 'assignment', contentId: number, rating: number): Promise<void> {
    await apiClient.post(`/content/rate`, {
      content_type: contentType,
      content_id: contentId,
      rating,
    });
  },

  // Course Completion
  async checkCourseCompletion(courseId: number): Promise<{ is_completed: boolean; completion_percentage: number; next_steps?: string[] }> {
    const response = await apiClient.get(`/courses/${courseId}/completion-status`);
    return response.data;
  },

  async requestCertificate(courseId: number): Promise<any> {
    const response = await apiClient.post(`/courses/${courseId}/request-certificate`);
    return response.data;
  },

  // Learning Preferences
  async updateLearningPreferences(preferences: {
    auto_advance?: boolean;
    playback_speed?: number;
    subtitle_language?: string;
    notification_settings?: any;
  }): Promise<void> {
    await apiClient.post(`/learning/preferences`, preferences);
  },

  async getLearningPreferences(): Promise<any> {
    const response = await apiClient.get(`/learning/preferences`);
    return response.data;
  },

  // Offline Content
  async downloadForOffline(lessonId: number): Promise<{ download_url: string; expires_at: string }> {
    const response = await apiClient.post(`/lessons/${lessonId}/download`);
    return response.data;
  },

  async getOfflineContent(): Promise<any[]> {
    const response = await apiClient.get(`/learning/offline-content`);
    return response.data;
  },

  async removeOfflineContent(lessonId: number): Promise<void> {
    await apiClient.delete(`/lessons/${lessonId}/offline`);
  },

  // Study Groups and Collaboration
  async getStudyGroups(courseId: number): Promise<any[]> {
    const response = await apiClient.get(`/courses/${courseId}/study-groups`);
    return response.data;
  },

  async joinStudyGroup(groupId: number): Promise<void> {
    await apiClient.post(`/study-groups/${groupId}/join`);
  },

  async leaveStudyGroup(groupId: number): Promise<void> {
    await apiClient.delete(`/study-groups/${groupId}/leave`);
  },

  // Learning Path
  async getRecommendedContent(courseId: number): Promise<any[]> {
    const response = await apiClient.get(`/courses/${courseId}/recommendations`);
    return response.data;
  },

  async getPersonalizedLearningPath(courseId: number): Promise<any> {
    const response = await apiClient.get(`/courses/${courseId}/learning-path`);
    return response.data;
  },

  // Accessibility
  async getTranscript(lessonId: number): Promise<{ transcript: string; language: string }> {
    const response = await apiClient.get(`/lessons/${lessonId}/transcript`);
    return response.data;
  },

  async getClosedCaptions(lessonId: number, language?: string): Promise<any> {
    const params = language ? `?language=${language}` : '';
    const response = await apiClient.get(`/lessons/${lessonId}/captions${params}`);
    return response.data;
  },

  // Mobile Learning
  async syncMobileProgress(data: any[]): Promise<void> {
    await apiClient.post(`/learning/sync-mobile`, { progress_data: data });
  },

  async getMobileOfflinePackage(courseId: number): Promise<{ package_url: string; size: number; expires_at: string }> {
    const response = await apiClient.get(`/courses/${courseId}/mobile-package`);
    return response.data;
  },
};