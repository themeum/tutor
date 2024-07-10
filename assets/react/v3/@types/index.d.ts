export type {};

declare module '*.png';
declare module '*.svg';
declare module '*.jpeg';
declare module '*.jpg';

declare global {
  interface Window {
    // biome-ignore lint/suspicious/noExplicitAny: <Allow explicit any for this>
    wp: any;
    _tutorobject: {
      ajaxurl: string;
      home_url: string;
      site_title: string;
      base_path: string;
      tutor_url: string;
      tutor_pro_url: string;
      dashboard_url: string;
      nonce_key: string;
      _tutor_nonce: string;
      loading_icon_url: string;
      placeholder_img_src: string;
      enable_lesson_classic_editor: string;
      tutor_frontend_dashboard_url: string;
      wp_date_format: string;
      is_admin: string;
      is_admin_bar_showing: string;
      timezones: {
        [key: string]: string;
      };
      addons_data: {
        name: string;
        description: string;
        url: string;
        is_enabled: number;
      }[];
      current_user: {
        data: {
          id: string;
          user_login: string;
          user_pass: string;
          user_nicename: string;
          user_email: string;
          user_url: string;
          user_registered: string;
          user_activation_key: string;
          user_status: string;
          display_name: string;
        };
        ID: number;
        caps: {
          [key: string]: boolean;
        };
        cap_key: string;
        roles: string[];
        allcaps: {
          [key: string]: boolean;
        };
        filter: null;
      };
      content_change_event: string;
      is_tutor_course_edit: string;
      assignment_max_file_allowed: string;
      current_page: string;
      quiz_answer_display_time: string;
      is_ssl: string;
      course_list_page_url: string;
      course_post_type: string;
      settings: {
        monetize_by: 'wc' | 'tutor' | 'edd';
        enable_course_marketplace: string;
        course_permalink_base: string;
        supported_video_sources: string[] | string;
      };
    };
    wpApiSettings: {
      nonce: string;
      root: string;
      versionString: string;
    };
  }
}
