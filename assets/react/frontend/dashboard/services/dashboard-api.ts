// Dashboard API Service
// Handles all dashboard-related API calls

import { apiClient } from '@FrontendServices/api/client';
import type { Assignment, Certificate, Course, DashboardStats, QuizAttempt } from '@FrontendTypes';

export const DashboardAPI = {
  // Dashboard Overview
  async getStats(): Promise<DashboardStats> {
    const response = await apiClient.get('/dashboard/stats');
    return response.data;
  },

  // Courses
  async getCourses(filters?: any): Promise<Course[]> {
    const params = filters ? new URLSearchParams(filters).toString() : '';
    const response = await apiClient.get(`/dashboard/courses${params ? '?' + params : ''}`);
    return response.data;
  },

  async unenrollFromCourse(courseId: number): Promise<void> {
    await apiClient.delete(`/courses/${courseId}/enrollment`);
  },

  // Assignments
  async getAssignments(): Promise<Assignment[]> {
    const response = await apiClient.get('/dashboard/assignments');
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

  // Quizzes
  async getQuizAttempts(): Promise<QuizAttempt[]> {
    const response = await apiClient.get('/dashboard/quiz-attempts');
    return response.data;
  },

  async checkQuizRetakeEligibility(quizId: number): Promise<{ allowed: boolean; message?: string }> {
    const response = await apiClient.get(`/quizzes/${quizId}/retake-eligibility`);
    return response.data;
  },

  async getQuizResult(attemptId: number): Promise<any> {
    const response = await apiClient.get(`/quiz-attempts/${attemptId}/result`);
    return response.data;
  },

  // Certificates
  async getCertificates(): Promise<Certificate[]> {
    const response = await apiClient.get('/dashboard/certificates');
    return response.data;
  },

  async getCertificate(certificateId: number): Promise<Certificate> {
    const response = await apiClient.get(`/certificates/${certificateId}`);
    return response.data;
  },

  async getCertificateDownloadUrl(certificateId: number): Promise<string> {
    const response = await apiClient.get(`/certificates/${certificateId}/download-url`);
    return response.data.download_url;
  },

  async getCertificateShareData(certificateId: number): Promise<any> {
    const response = await apiClient.get(`/certificates/${certificateId}/share-data`);
    return response.data;
  },

  // Profile & Settings
  async updateProfile(formData: FormData): Promise<any> {
    const response = await apiClient.post('/profile/update', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  async uploadAvatar(formData: FormData): Promise<any> {
    const response = await apiClient.post('/profile/avatar', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  async changePassword(data: { current_password: string; new_password: string }): Promise<void> {
    await apiClient.post('/profile/change-password', data);
  },

  async updateNotificationPreference(preference: string, enabled: boolean): Promise<void> {
    await apiClient.post('/profile/notification-preferences', {
      preference,
      enabled,
    });
  },

  async updateLanguagePreference(language: string): Promise<void> {
    await apiClient.post('/profile/language-preference', {
      language,
    });
  },

  async updatePreferences(formData: FormData): Promise<any> {
    const response = await apiClient.post('/profile/preferences', formData);
    return response.data;
  },
};