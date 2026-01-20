const endpoints = {
  ADMIN_AJAX: 'wp-admin/admin-ajax.php',
  TAGS: 'course-tag',
  CATEGORIES: 'course-category',
  USERS: 'users',
  USERS_LIST: 'tutor_user_list',
  ORDER_DETAILS: 'tutor_order_details',
  ADMIN_COMMENT: 'tutor_order_comment',
  ORDER_MARK_AS_PAID: 'tutor_order_paid',
  ORDER_REFUND: 'tutor_order_refund',
  ORDER_CANCEL: 'tutor_order_cancel',
  ADD_ORDER_DISCOUNT: 'tutor_order_discount',
  COURSE_LIST: 'course_list',
  BUNDLE_LIST: 'tutor_get_bundle_list',
  CATEGORY_LIST: 'category_list',
  CREATED_COURSE: 'tutor_create_course',
  TUTOR_INSTRUCTOR_SEARCH: 'tutor_course_instructor_search',

  TUTOR_YOUTUBE_VIDEO_DURATION: 'tutor_youtube_video_duration',
  TUTOR_UNLINK_PAGE_BUILDER: 'tutor_unlink_page_builder',

  // AI CONTENT GENERATION
  GENERATE_AI_IMAGE: 'tutor_pro_generate_image',
  MAGIC_FILL_AI_IMAGE: 'tutor_pro_magic_fill_image',
  MAGIC_TEXT_GENERATION: 'tutor_pro_generate_text_content',
  MAGIC_AI_MODIFY_CONTENT: 'tutor_pro_modify_text_content',
  USE_AI_GENERATED_IMAGE: 'tutor_pro_use_magic_image',
  OPEN_AI_SAVE_SETTINGS: 'tutor_pro_chatgpt_save_settings',

  GENERATE_COURSE_CONTENT: 'tutor_pro_generate_course_content',
  GENERATE_COURSE_TOPIC_CONTENT: 'tutor_pro_generate_course_topic_content',
  SAVE_AI_GENERATED_COURSE_CONTENT: 'tutor_pro_ai_course_create',
  GENERATE_QUIZ_QUESTIONS: 'tutor_pro_generate_quiz_questions',

  // SUBSCRIPTION
  GET_SUBSCRIPTIONS_LIST: 'tutor_subscription_plans',
  SAVE_SUBSCRIPTION: 'tutor_subscription_plan_save',
  DELETE_SUBSCRIPTION: 'tutor_subscription_plan_delete',
  DUPLICATE_SUBSCRIPTION: 'tutor_subscription_plan_duplicate',
  SORT_SUBSCRIPTION: 'tutor_subscription_plan_sort',
  UPDATE_SUBSCRIPTION_STATUS: 'tutor_subscription_status_update',
  RESUME_SUBSCRIPTION: 'tutor_subscription_resume',
  EARLY_RENEW_SUBSCRIPTION: 'tutor_subscription_early_renew',

  // COURSE
  GET_COURSE_DETAILS: 'tutor_course_details',
  UPDATE_COURSE: 'tutor_update_course',
  GET_COURSE_LIST: 'tutor_course_list',

  // WOO COMMERCE PRODUCTS
  GET_WC_PRODUCTS: 'tutor_get_wc_products',
  GET_WC_PRODUCT_DETAILS: 'tutor_get_wc_product',

  // QUIZ
  GET_QUIZ_DETAILS: 'tutor_quiz_details',
  SAVE_QUIZ: 'tutor_quiz_builder_save',
  QUIZ_IMPORT_DATA: 'quiz_import_data',
  QUIZ_EXPORT_DATA: 'quiz_export_data',
  DELETE_QUIZ: 'tutor_quiz_delete',

  // ZOOM
  GET_ZOOM_MEETING_DETAILS: 'tutor_zoom_meeting_details',
  SAVE_ZOOM_MEETING: 'tutor_zoom_save_meeting',
  DELETE_ZOOM_MEETING: 'tutor_zoom_delete_meeting',

  // GOOGLE MEET
  GET_GOOGLE_MEET_DETAILS: 'tutor_google_meet_meeting_details',
  SAVE_GOOGLE_MEET: 'tutor_google_meet_new_meeting',
  DELETE_GOOGLE_MEET: 'tutor_google_meet_delete',

  // TOPIC
  GET_COURSE_CONTENTS: 'tutor_course_contents',
  SAVE_TOPIC: 'tutor_save_topic',
  DELETE_TOPIC: 'tutor_delete_topic',
  DELETE_TOPIC_CONTENT: 'tutor_delete_lesson',
  UPDATE_COURSE_CONTENT_ORDER: 'tutor_update_course_content_order',
  DUPLICATE_CONTENT: 'tutor_duplicate_content',
  ADD_CONTENT_BANK_CONTENT_TO_COURSE: 'tutor_content_bank_add_content_to_course',
  DELETE_CONTENT_BANK_CONTENT_FROM_COURSE: 'tutor_content_bank_remove_content_from_course',

  // LESSON
  GET_LESSON_DETAILS: 'tutor_lesson_details',
  SAVE_LESSON: 'tutor_save_lesson',
  DELETE_LESSON_COMMENT: 'tutor_delete_lesson_comment',
  REPLY_LESSON_COMMENT: 'tutor_reply_lesson_comment',

  // Q&A
  QNA_SINGLE_ACTION: 'tutor_qna_single_action',
  DELETE_DASHBOARD_QNA: 'tutor_delete_dashboard_question',
  CREATE_UPDATE_QNA: 'tutor_qna_create_update',

  // ASSIGNMENT
  GET_ASSIGNMENT_DETAILS: 'tutor_assignment_details',
  SAVE_ASSIGNMENT: 'tutor_assignment_save',

  // TAX SETTINGS
  GET_TAX_SETTINGS: 'tutor_get_tax_settings',
  GET_H5P_QUIZ_CONTENT: 'tutor_h5p_list_quiz_contents',
  GET_H5P_LESSON_CONTENT: 'tutor_h5p_list_lesson_contents',
  GET_H5P_QUIZ_CONTENT_BY_ID: 'tutor_h5p_quiz_content_by_id',

  // PAYMENT SETTINGS
  GET_PAYMENT_SETTINGS: 'tutor_payment_settings',
  GET_PAYMENT_GATEWAYS: 'tutor_payment_gateways',
  INSTALL_PAYMENT_GATEWAY: 'tutor_install_payment_gateway',
  REMOVE_PAYMENT_GATEWAY: 'tutor_remove_payment_gateway',

  // ADDON LIST
  GET_ADDON_LIST: 'tutor_get_all_addons',
  ADDON_ENABLE_DISABLE: 'addon_enable_disable',

  // INSTALL PLUGIN
  TUTOR_INSTALL_PLUGIN: 'tutor_install_plugin',

  // COUPON
  GET_COUPON_DETAILS: 'tutor_coupon_details',
  CREATE_COUPON: 'tutor_coupon_create',
  UPDATE_COUPON: 'tutor_coupon_update',
  COUPON_APPLIES_TO: 'tutor_coupon_applies_to_list',

  // ENROLLMENT
  CREATE_ENROLLMENT: 'tutor_enroll_bulk_student',
  GET_COURSE_BUNDLE_LIST: 'tutor_course_bundle_list',
  GET_UNENROLLED_USERS: 'tutor_unenrolled_users',

  // MEMBERSHIP
  GET_MEMBERSHIP_PLANS: 'tutor_membership_plans',
  SAVE_MEMBERSHIP_PLAN: 'tutor_membership_plan_save',
  DUPLICATE_MEMBERSHIP_PLAN: 'tutor_membership_plan_duplicate',
  DELETE_MEMBERSHIP_PLAN: 'tutor_membership_plan_delete',

  // COURSE BUNDLE
  GET_BUNDLE_DETAILS: 'tutor_get_course_bundle_data',
  UPDATE_BUNDLE: 'tutor_create_course_bundle',
  ADD_REMOVE_COURSE_TO_BUNDLE: 'tutor_add_remove_course_to_bundle',

  // IMPORT EXPORT
  GET_EXPORTABLE_CONTENT: 'tutor_pro_exportable_contents',
  EXPORT_CONTENTS: 'tutor_pro_export',
  EXPORT_SETTINGS_FREE: 'tutor_export_settings',
  IMPORT_CONTENTS: 'tutor_pro_import',
  IMPORT_SETTINGS_FREE: 'tutor_import_settings',
  GET_IMPORT_EXPORT_HISTORY: 'tutor_pro_export_import_history',
  DELETE_IMPORT_EXPORT_HISTORY: 'tutor_pro_delete_export_import_history',

  // CONTENT BANK
  GET_CONTENT_BANK_COLLECTIONS: 'tutor_content_bank_collections',
  SAVE_CONTENT_BANK_COLLECTION: 'tutor_content_bank_collection_save',
  DELETE_CONTENT_BANK_COLLECTION: 'tutor_content_bank_collection_delete',
  GET_CONTENT_BANK_CONTENTS: 'tutor_content_bank_contents',
  DELETE_CONTENT_BANK_CONTENTS: 'tutor_content_bank_content_delete',
  GET_CONTENT_DETAILS: 'tutor_pro_get_content_details',
  GET_CONTENT_BANK_LESSON_DETAILS: 'tutor_content_bank_lesson_details',
  GET_CONTENT_BANK_ASSIGNMENT_DETAILS: 'tutor_content_bank_assignment_details',
  SAVE_CONTENT_BANK_LESSON_CONTENT: 'tutor_content_bank_lesson_save',
  SAVE_CONTENT_BANK_ASSIGNMENT_CONTENT: 'tutor_content_bank_assignment_save',
  SAVE_QUESTION_CONTENT: 'tutor_content_bank_question_save',
  GET_CONTENT_BANK_QUESTION_DETAILS: 'tutor_content_bank_question_details',
  DUPLICATE_CONTENT_BANK_CONTENT: 'tutor_content_bank_content_duplicate',
  MOVE_CONTENT_BANK_CONTENT: 'tutor_content_bank_content_move',
  DUPLICATE_CONTENT_BANK_COLLECTION: 'tutor_content_bank_collection_duplicate',
  IMPORT_FROM_COURSES: 'tutor_content_bank_content_synchronize',

  // Calendar
  GET_CALENDAR_EVENTS: 'get_calendar_materials',

  // Announcement
  CREATE_ANNOUNCEMENT: 'tutor_announcement_create',
  DELETE_ANNOUNCEMENT: 'tutor_announcement_delete',

  //Reviews
  PLACE_RATING: 'tutor_place_rating',
  DELETE_REVIEW: 'delete_tutor_review',
} as const;

export default endpoints;
