export const backendUrls = {
  admin: '/wp-admin/',
  tutorDashboard: '/wp-admin/admin.php?page=tutor',
  courses: '/wp-admin/admin.php?page=tutor',
  courseBuilder: '/wp-admin/admin.php?page=create-course',
  categories: '/wp-admin/edit-tags.php?taxonomy=course-category&post_type=courses',
  tags: '/wp-admin/edit-tags.php?taxonomy=course-tag&post_type=courses',
  students: '/wp-admin/admin.php?page=tutor-students',
  orders: '/wp-admin/admin.php?page=tutor_orders',
  announcements: '/wp-admin/admin.php?page=tutor_announcements',
  quizAttempts: '/wp-admin/admin.php?page=tutor_quiz_attempts',
  qna: '/wp-admin/admin.php?page=question_answer',
  instructors: '/wp-admin/admin.php?page=tutor-instructors',
  withdrawRequests: '/wp-admin/admin.php?page=tutor_withdraw_requests',
  addons: '/wp-admin/admin.php?page=tutor-addons',
  tools: '/wp-admin/admin.php?page=tutor-tools',
  settings: '/wp-admin/admin.php?page=tutor_settings',
} as const;

export const frontendUrls = {
  dashboard: '/dashboard/',
  myCourses: '/dashboard/my-courses/',
  enrolledCourses: '/dashboard/courses/',
  orderHistory: '/dashboard/purchase_history/',
} as const;
