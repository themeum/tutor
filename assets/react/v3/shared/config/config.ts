export const tutorConfig = window._tutorobject;

const config = {
  TUTOR_API_BASE_URL: tutorConfig.home_url,
  WP_API_BASE_URL: `${window.wpApiSettings.root}${window.wpApiSettings.versionString}`,
  WP_AJAX_BASE_URL: tutorConfig.ajaxurl,
  TUTOR_PRICING_PAGE: 'https://tutorlms.com/pricing/',
};

export default config;
