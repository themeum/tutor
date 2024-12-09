export const tutorConfig = window._tutorobject;
window.ajaxurl = tutorConfig.ajaxurl;

const config = {
  // @TODO: the api base url key needs to be replaced with ajax base url key 
  TUTOR_API_BASE_URL: tutorConfig.site_url,
  WP_AJAX_BASE_URL: tutorConfig.ajaxurl,
  WP_API_BASE_URL: `${window.wpApiSettings.root}${window.wpApiSettings.versionString}`,
  VIDEO_SOURCES_SETTINGS_URL: `${tutorConfig.site_url}/wp-admin/admin.php?page=tutor_settings&tab_page=course#field_supported_video_sources`,
  TUTOR_PRICING_PAGE: 'https://tutorlms.com/pricing/',
  TUTOR_ADDONS_PAGE: `${tutorConfig.site_url}/wp-admin/admin.php?page=tutor-addons`,
  CHATGPT_PLATFORM_URL: 'https://platform.openai.com/account/api-keys',
  TUTOR_MY_COURSES_PAGE_URL: `${tutorConfig.tutor_frontend_dashboard_url}/my-courses`,
};

export default config;
