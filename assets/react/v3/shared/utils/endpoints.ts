const endpoints = {
  ADMIN_AJAX: 'wp-admin/admin-ajax.php',
  TAGS: 'course-tag',
  CATEGORIES: 'course-category',
  USERS: 'users',
  ORDER_DETAILS: 'tutor_order_details',
  ADMIN_COMMENT: 'tutor_order_comment',
  ORDER_MARK_AS_PAID: 'tutor_order_paid',
  ORDER_REFUND: 'tutor_order_refund',
  ADD_ORDER_DISCOUNT: 'tutor_order_discount',
  COURSE_LIST: 'course_list',
  CATEGORY_LIST: 'category_list',
  CREATED_COURSE: 'tutor_create_course',
  TUTOR_INSTRUCTOR_SEARCH: 'tutor_course_instructor_search',
  GET_SUBSCRIPTIONS_LIST: 'tutor_subscription_course_plans',
  SAVE_SUBSCRIPTION: 'tutor_subscription_course_plan_save',
  DELETE_SUBSCRIPTION: 'tutor_subscription_course_plan_delete',
  DUPLICATE_SUBSCRIPTION: 'tutor_subscription_course_plan_duplicate',
  SORT_SUBSCRIPTION: 'tutor_subscription_course_plan_sort',

  GENERATE_AI_IMAGE: 'tutor_generate_image',
  MAGIC_FILL_AI_IMAGE: 'tutor_magic_fill_image',
  MAGIC_TEXT_GENERATION: 'tutor_generate_text_content',
  MAGIC_AI_MODIFY_CONTENT: 'tutor_modify_text_content',
  USE_AI_GENERATED_IMAGE: 'tutor_use_magic_image',
};

export default endpoints;
