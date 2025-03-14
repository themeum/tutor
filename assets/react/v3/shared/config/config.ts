export const tutorConfig = window._tutorobject;
window.ajaxurl = tutorConfig.ajaxurl;

const config = {
  TUTOR_SITE_URL: tutorConfig.site_url,
  WP_AJAX_BASE_URL: tutorConfig.ajaxurl,
  WP_API_BASE_URL: `${window.wpApiSettings.root}${window.wpApiSettings.versionString}`,
  VIDEO_SOURCES_SETTINGS_URL: `${tutorConfig.site_url}/wp-admin/admin.php?page=tutor_settings&tab_page=course#field_supported_video_sources`,
  MONETIZATION_SETTINGS_URL: `${tutorConfig.site_url}/wp-admin/admin.php?page=tutor_settings&tab_page=monetization`,
  TUTOR_PRICING_PAGE: 'https://tutorlms.com/pricing/',
  TUTOR_ADDONS_PAGE: `${tutorConfig.site_url}/wp-admin/admin.php?page=tutor-addons`,
  CHATGPT_PLATFORM_URL: 'https://platform.openai.com/account/api-keys',
  TUTOR_MY_COURSES_PAGE_URL: `${tutorConfig.tutor_frontend_dashboard_url}/my-courses`,
  TUTOR_SUPPORT_PAGE_URL: 'https://tutorlms.com/support',
  TUTOR_SUBSCRIPTIONS_PAGE: `${tutorConfig.site_url}/wp-admin/admin.php?page=tutor-subscriptions`,
  TUTOR_ENROLLMENTS_PAGE: `${tutorConfig.site_url}/wp-admin/admin.php?page=enrollments`,
};

export default config;
