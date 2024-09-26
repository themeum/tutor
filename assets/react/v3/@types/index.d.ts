export type {};

declare module '*.png';
declare module '*.svg';
declare module '*.jpeg';
declare module '*.jpg';

declare global {
  // biome-ignore lint/suspicious/noExplicitAny: <explanation>
  const wp: any;
  interface Window {
    // biome-ignore lint/suspicious/noExplicitAny: <Allow explicit any for this>
    wp: any;
    ajaxurl: string;
    // biome-ignore lint/suspicious/noExplicitAny: <explanation>
    tinymce: any;
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
      wp_rest_nonce: string;
      is_admin: string;
      is_admin_bar_showing: string;
      difficulty_levels: {
        label: string;
        value: string;
      }[];
      edd_products: {
        ID: string;
        post_title: string;
      }[];
      bp_groups: {
        name: string;
        id: number;
      }[];
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
      settings?: {
        monetize_by: 'wc' | 'tutor' | 'edd';
        enable_course_marketplace: 'on' | 'off';
        course_permalink_base: string;
        supported_video_sources: string[] | string;
        enrollment_expiry_enabled: 'on' | 'off';
        enable_q_and_a_on_course: 'on' | 'off';
        instructor_can_delete_course: 'on' | 'off';
        chatgpt_enable: 'on' | 'off';
        course_builder_logo_url: string | false;
        chatgpt_key_exist: boolean;
      };
      tutor_currency: {
        symbol: string;
        currency: string;
        position: string;
        thousand_separator: string;
        decimal_separator: string;
        no_of_decimal: string;
      };
      local: string;
    };
    wpApiSettings: {
      nonce: string;
      root: string;
      versionString: string;
    };
  }
}
