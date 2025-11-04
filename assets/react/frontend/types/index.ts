// Frontend Types
// TypeScript type definitions for frontend functionality

// User types
export interface User {
  id: number;
  username: string;
  email: string;
  display_name: string;
  first_name: string;
  last_name: string;
  avatar_url: string;
  roles: string[];
  registration_date: string;
  last_login: string;
  profile_url: string;
}

// Course types
export interface Course {
  id: number;
  title: string;
  slug: string;
  description: string;
  short_description: string;
  thumbnail: string;
  featured_image: string;
  instructor_id: number;
  instructor_name: string;
  instructor_avatar: string;
  category_id: number;
  category_name: string;
  level: 'beginner' | 'intermediate' | 'advanced';
  language: string;
  status: 'published' | 'draft' | 'private';
  price: number;
  sale_price?: number;
  currency: string;
  is_free: boolean;
  enrollment_limit?: number;
  enrolled_count: number;
  rating: number;
  rating_count: number;
  total_lessons: number;
  total_quizzes: number;
  total_assignments: number;
  duration: string;
  created_date: string;
  updated_date: string;
  enrollment_date?: string;
  progress?: number;
  completion_date?: string;
  certificate_url?: string;
  is_enrolled: boolean;
  is_completed: boolean;
  is_wishlist: boolean;
  can_retake: boolean;
  access_expires?: string;
}

// Lesson types
export interface Lesson {
  id: number;
  course_id: number;
  title: string;
  slug: string;
  content: string;
  excerpt: string;
  lesson_type: 'video' | 'text' | 'audio' | 'presentation' | 'interactive';
  video_url?: string;
  video_duration?: number;
  attachments: LessonAttachment[];
  order: number;
  is_preview: boolean;
  is_completed: boolean;
  completion_date?: string;
  time_spent: number;
  created_date: string;
  updated_date: string;
}

export interface LessonAttachment {
  id: number;
  name: string;
  url: string;
  file_type: string;
  file_size: number;
}

// Quiz types
export interface Quiz {
  id: number;
  course_id: number;
  title: string;
  description: string;
  quiz_type: 'graded' | 'survey';
  time_limit?: number;
  attempts_allowed: number;
  passing_grade: number;
  questions_count: number;
  randomize_questions: boolean;
  show_results: 'immediately' | 'after_submission' | 'never';
  order: number;
  is_completed: boolean;
  best_score?: number;
  attempts_count: number;
  created_date: string;
  updated_date: string;
}

export interface QuizQuestion {
  id: number;
  quiz_id: number;
  question_type: 'multiple_choice' | 'true_false' | 'short_answer' | 'essay' | 'fill_in_blank';
  question_text: string;
  question_image?: string;
  points: number;
  order: number;
  options: QuizQuestionOption[];
  correct_answers: string[];
  explanation?: string;
}

export interface QuizQuestionOption {
  id: number;
  option_text: string;
  is_correct: boolean;
  order: number;
}

export interface QuizAttempt {
  id: number;
  quiz_id: number;
  quiz_title: string;
  course_id: number;
  course_title: string;
  user_id: number;
  attempt_number: number;
  started_at: string;
  submitted_at?: string;
  time_taken?: number;
  total_questions: number;
  correct_answers: number;
  score: number;
  percentage: number;
  passed: boolean;
  status: 'in_progress' | 'submitted' | 'graded';
  answers: QuizAnswer[];
}

export interface QuizAnswer {
  question_id: number;
  answer: string | string[];
  is_correct: boolean;
  points_earned: number;
}

// Assignment types
export interface Assignment {
  id: number;
  course_id: number;
  course_title: string;
  title: string;
  description: string;
  instructions: string;
  max_file_size: number;
  allowed_file_types: string[];
  total_marks: number;
  pass_marks: number;
  due_date?: string;
  order: number;
  is_submitted: boolean;
  submission_date?: string;
  grade?: number;
  feedback?: string;
  status: 'not_submitted' | 'submitted' | 'graded';
  created_date: string;
  updated_date: string;
}

export interface AssignmentSubmission {
  id: number;
  assignment_id: number;
  user_id: number;
  submission_text?: string;
  attachments: AssignmentAttachment[];
  submitted_at: string;
  grade?: number;
  feedback?: string;
  graded_at?: string;
  graded_by?: number;
  status: 'submitted' | 'graded' | 'returned';
}

export interface AssignmentAttachment {
  id: number;
  name: string;
  url: string;
  file_type: string;
  file_size: number;
}

// Certificate types
export interface Certificate {
  id: number;
  course_id: number;
  course_title: string;
  user_id: number;
  certificate_id: string;
  title: string;
  preview_url: string;
  download_url: string;
  public_url: string;
  issued_date: string;
  expiry_date?: string;
  is_valid: boolean;
  verification_code: string;
}

// Progress types
export interface CourseProgress {
  course_id: number;
  total_lessons: number;
  completed_lessons: number;
  total_quizzes: number;
  completed_quizzes: number;
  total_assignments: number;
  completed_assignments: number;
  overall_progress: number;
  time_spent: number;
  last_activity: string;
  completion_date?: string;
}

export interface LessonProgress {
  lesson_id: number;
  is_completed: boolean;
  completion_date?: string;
  time_spent: number;
  last_position?: number;
}

// Dashboard types
export interface DashboardStats {
  enrolled_courses: number;
  completed_courses: number;
  in_progress_courses: number;
  total_certificates: number;
  total_quiz_attempts: number;
  total_assignments_submitted: number;
  total_time_spent: number;
  recent_activities: DashboardActivity[];
}

export interface DashboardActivity {
  id: number;
  type: 'course_enrolled' | 'lesson_completed' | 'quiz_completed' | 'assignment_submitted' | 'certificate_earned';
  title: string;
  description: string;
  course_id?: number;
  course_title?: string;
  url?: string;
  date: string;
  icon: string;
}

// Notification types
export interface Notification {
  id: number;
  user_id: number;
  type: 'course' | 'assignment' | 'quiz' | 'certificate' | 'system';
  title: string;
  message: string;
  url?: string;
  is_read: boolean;
  created_date: string;
  read_date?: string;
}

// Discussion types
export interface Discussion {
  id: number;
  course_id: number;
  lesson_id?: number;
  user_id: number;
  user_name: string;
  user_avatar: string;
  title: string;
  content: string;
  replies_count: number;
  likes_count: number;
  is_liked: boolean;
  is_resolved: boolean;
  created_date: string;
  updated_date: string;
  replies: DiscussionReply[];
}

export interface DiscussionReply {
  id: number;
  discussion_id: number;
  user_id: number;
  user_name: string;
  user_avatar: string;
  content: string;
  likes_count: number;
  is_liked: boolean;
  created_date: string;
  updated_date: string;
}

// Review types
export interface Review {
  id: number;
  course_id: number;
  user_id: number;
  user_name: string;
  user_avatar: string;
  rating: number;
  title?: string;
  content: string;
  helpful_count: number;
  is_helpful: boolean;
  created_date: string;
  updated_date: string;
}

// Search types
export interface SearchResult {
  type: 'course' | 'lesson' | 'quiz' | 'assignment' | 'discussion';
  id: number;
  title: string;
  description?: string;
  url: string;
  course_title?: string;
  course_id?: number;
  thumbnail?: string;
  relevance_score: number;
}

export interface SearchFilters {
  query?: string;
  type?: string[];
  course_id?: number;
  category_id?: number;
  level?: string[];
  price_range?: [number, number];
  rating_min?: number;
  sort_by?: 'relevance' | 'date' | 'rating' | 'popularity';
  sort_order?: 'asc' | 'desc';
}

// API Response types
export interface ApiResponse<T = any> {
  success: boolean;
  data: T;
  message?: string;
  errors?: string[];
  meta?: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  };
}

export interface PaginatedResponse<T = any> {
  data: T[];
  meta: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
    from: number;
    to: number;
  };
  links: {
    first: string;
    last: string;
    prev?: string;
    next?: string;
  };
}

// Form types
export interface FormField {
  name: string;
  type: 'text' | 'email' | 'password' | 'number' | 'select' | 'textarea' | 'checkbox' | 'radio' | 'file';
  label: string;
  placeholder?: string;
  required?: boolean;
  options?: { value: string; label: string }[];
  validation?: ValidationRule[];
}

export interface ValidationRule {
  type: string;
  value?: any;
  message: string;
}

// Event types
export interface CustomEvent {
  type: string;
  data: any;
  timestamp: number;
}

// Utility types
export type Status = 'idle' | 'loading' | 'success' | 'error';
export type SortDirection = 'asc' | 'desc';
export type NotificationType = 'success' | 'error' | 'warning' | 'info';

// Generic types
export type ID = number | string;
export type Timestamp = string;
export type URL = string;
export type Email = string;