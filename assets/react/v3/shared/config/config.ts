export const tutorConfig = window._tutorobject;
window.ajaxurl = tutorConfig.ajaxurl;

const config = {
  TUTOR_API_BASE_URL: tutorConfig.home_url,
  WP_API_BASE_URL: `${window.wpApiSettings.root}${window.wpApiSettings.versionString}`,
  VIDEO_SOURCES_SETTINGS_URL: `${tutorConfig.home_url}/wp-admin/admin.php?page=tutor_settings&tab_page=course#field_supported_video_sources`,
  WP_AJAX_BASE_URL: tutorConfig.ajaxurl,
  TUTOR_PRICING_PAGE: 'https://tutorlms.com/pricing/',
  TUTOR_ADDONS_PAGE: `${tutorConfig.home_url}/wp-admin/admin.php?page=tutor-addons`,
  CHATGPT_PLATFORM_URL: 'https://platform.openai.com/account/api-keys',
};

export default config;
