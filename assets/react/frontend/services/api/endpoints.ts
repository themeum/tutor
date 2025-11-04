// API Endpoints
// Centralized API endpoint definitions

export const API_ENDPOINTS = {
  // Authentication
  AUTH: {
    LOGIN: '/auth/login',
    LOGOUT: '/auth/logout',
    REFRESH: '/auth/refresh',
    ME: '/auth/me',
    REGISTER: '/auth/register',
    FORGOT_PASSWORD: '/auth/forgot-password',
    RESET_PASSWORD: '/auth/reset-password',
  },

  // Dashboard
  DASHBOARD: {
    STATS: '/dashboard/stats',
    COURSES: '/dashboard/courses',
    ASSIGNMENTS: '/dashboard/assignments',
    QUIZ_ATTEMPTS: '/dashboard/quiz-attempts',
    CERTIFICATES: '/dashboard/certificates',
    NOTIFICATIONS: '/dashboard/notifications',
    ACTIVITY: '/dashboard/activity',
  },

  // Courses
  COURSES: {
    LIST: '/courses',
    DETAIL: (id: number) => `/courses/${id}`,
    ENROLL: (id: number) => `/courses/${id}/enroll`,
    UNENROLL: (id: number) => `/courses/${id}/unenroll`,
    PROGRESS: (id: number) => `/courses/${id}/progress`,
    LESSONS: (id: number) => `/courses/${id}/lessons`,
    CURRICULUM: (id: number) => `/courses/${id}/curriculum`,
    REVIEWS: (id: number) => `/courses/${id}/reviews`,
    WISHLIST: (id: number) => `/courses/${id}/wishlist`,
  },

  // Lessons
  LESSONS: {
    DETAIL: (id: number) => `/lessons/${id}`,
    COMPLETE: (id: number) => `/lessons/${id}/complete`,
    UNCOMPLETE: (id: number) => `/lessons/${id}/uncomplete`,
    CONTENT: (id: number) => `/lessons/${id}/content`,
    ATTACHMENTS: (id: number) => `/lessons/${id}/attachments`,
  },

  // Quizzes
  QUIZZES: {
    DETAIL: (id: number) => `/quizzes/${id}`,
    START: (id: number) => `/quizzes/${id}/start`,
    SUBMIT: (id: number) => `/quizzes/${id}/submit`,
    ATTEMPTS: (id: number) => `/quizzes/${id}/attempts`,
    RESULT: (attemptId: number) => `/quiz-attempts/${attemptId}/result`,
    RETAKE_ELIGIBILITY: (id: number) => `/quizzes/${id}/retake-eligibility`,
  },

  // Assignments
  ASSIGNMENTS: {
    DETAIL: (id: number) => `/assignments/${id}`,
    SUBMIT: (id: number) => `/assignments/${id}/submit`,
    SUBMISSIONS: (id: number) => `/assignments/${id}/submissions`,
    DOWNLOAD: (submissionId: number) => `/assignment-submissions/${submissionId}/download`,
  },

  // Certificates
  CERTIFICATES: {
    LIST: '/certificates',
    DETAIL: (id: number) => `/certificates/${id}`,
    DOWNLOAD: (id: number) => `/certificates/${id}/download`,
    DOWNLOAD_URL: (id: number) => `/certificates/${id}/download-url`,
    SHARE_DATA: (id: number) => `/certificates/${id}/share-data`,
    VERIFY: (code: string) => `/certificates/verify/${code}`,
  },

  // User Profile
  PROFILE: {
    GET: '/profile',
    UPDATE: '/profile/update',
    AVATAR: '/profile/avatar',
    CHANGE_PASSWORD: '/profile/change-password',
    PREFERENCES: '/profile/preferences',
    NOTIFICATION_PREFERENCES: '/profile/notification-preferences',
    LANGUAGE_PREFERENCE: '/profile/language-preference',
    DELETE_ACCOUNT: '/profile/delete-account',
  },

  // Discussions & Q&A
  DISCUSSIONS: {
    LIST: (courseId: number) => `/courses/${courseId}/discussions`,
    CREATE: (courseId: number) => `/courses/${courseId}/discussions`,
    DETAIL: (id: number) => `/discussions/${id}`,
    REPLY: (id: number) => `/discussions/${id}/replies`,
    LIKE: (id: number) => `/discussions/${id}/like`,
    UNLIKE: (id: number) => `/discussions/${id}/unlike`,
  },

  // Notifications
  NOTIFICATIONS: {
    LIST: '/notifications',
    MARK_READ: (id: number) => `/notifications/${id}/read`,
    MARK_ALL_READ: '/notifications/mark-all-read',
    DELETE: (id: number) => `/notifications/${id}`,
    SETTINGS: '/notifications/settings',
  },

  // Search
  SEARCH: {
    GLOBAL: '/search',
    COURSES: '/search/courses',
    LESSONS: '/search/lessons',
    DISCUSSIONS: '/search/discussions',
    SUGGESTIONS: '/search/suggestions',
  },

  // Learning Progress
  PROGRESS: {
    COURSE: (courseId: number) => `/progress/courses/${courseId}`,
    LESSON: (lessonId: number) => `/progress/lessons/${lessonId}`,
    QUIZ: (quizId: number) => `/progress/quizzes/${quizId}`,
    ASSIGNMENT: (assignmentId: number) => `/progress/assignments/${assignmentId}`,
    OVERALL: '/progress/overall',
  },

  // Wishlist
  WISHLIST: {
    LIST: '/wishlist',
    ADD: (courseId: number) => `/wishlist/add/${courseId}`,
    REMOVE: (courseId: number) => `/wishlist/remove/${courseId}`,
    CLEAR: '/wishlist/clear',
  },

  // Reviews & Ratings
  REVIEWS: {
    LIST: (courseId: number) => `/courses/${courseId}/reviews`,
    CREATE: (courseId: number) => `/courses/${courseId}/reviews`,
    UPDATE: (id: number) => `/reviews/${id}`,
    DELETE: (id: number) => `/reviews/${id}`,
    HELPFUL: (id: number) => `/reviews/${id}/helpful`,
  },

  // File Uploads
  UPLOADS: {
    AVATAR: '/uploads/avatar',
    ASSIGNMENT: '/uploads/assignment',
    PROFILE_COVER: '/uploads/profile-cover',
    TEMPORARY: '/uploads/temporary',
  },

  // Analytics & Tracking
  ANALYTICS: {
    TRACK_EVENT: '/analytics/track',
    LESSON_TIME: '/analytics/lesson-time',
    COURSE_COMPLETION: '/analytics/course-completion',
    QUIZ_PERFORMANCE: '/analytics/quiz-performance',
  },

  // Instructor (if applicable)
  INSTRUCTOR: {
    DASHBOARD: '/instructor/dashboard',
    COURSES: '/instructor/courses',
    STUDENTS: '/instructor/students',
    EARNINGS: '/instructor/earnings',
    REVIEWS: '/instructor/reviews',
  },

  // System
  SYSTEM: {
    HEALTH: '/system/health',
    VERSION: '/system/version',
    SETTINGS: '/system/settings',
  },
} as const;

// Helper function to build URLs with query parameters
export const buildUrl = (endpoint: string, params?: Record<string, any>): string => {
  if (!params || Object.keys(params).length === 0) {
    return endpoint;
  }

  const searchParams = new URLSearchParams();
  
  Object.entries(params).forEach(([key, value]) => {
    if (value !== null && value !== undefined) {
      searchParams.append(key, String(value));
    }
  });

  const queryString = searchParams.toString();
  return queryString ? `${endpoint}?${queryString}` : endpoint;
};

// Helper function to replace URL parameters
export const replaceUrlParams = (url: string, params: Record<string, string | number>): string => {
  let result = url;
  
  Object.entries(params).forEach(([key, value]) => {
    result = result.replace(`:${key}`, String(value));
  });
  
  return result;
};

// Export endpoint categories for easier imports
export const {
  AUTH,
  DASHBOARD,
  COURSES,
  LESSONS,
  QUIZZES,
  ASSIGNMENTS,
  CERTIFICATES,
  PROFILE,
  DISCUSSIONS,
  NOTIFICATIONS,
  SEARCH,
  PROGRESS,
  WISHLIST,
  REVIEWS,
  UPLOADS,
  ANALYTICS,
  INSTRUCTOR,
  SYSTEM,
} = API_ENDPOINTS;