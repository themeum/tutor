export const backendUrls = {
  admin: '/wp-admin/',
  tutorDashboard: '/wp-admin/admin.php?page=tutor',
  courseBuilder: '/wp-admin/admin.php?page=create-course',
  orders: '/wp-admin/admin.php?page=tutor_orders',
  settings: '/wp-admin/admin.php?page=tutor_settings',
} as const;

export const frontendUrls = {
  dashboard: '/dashboard/',
  myCourses: '/dashboard/my-courses/',
  enrolledCourses: '/dashboard/courses/',
  orderHistory: '/dashboard/purchase_history/',
} as const;
