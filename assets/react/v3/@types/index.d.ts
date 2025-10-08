import { type InjectedField } from '@CourseBuilderContexts/CourseBuilderSlotContext';
import { type InjectionSlots } from '@TutorShared/utils/types';
import { type LocaleData } from '@wordpress/i18n';

export type {};

interface Tutor {
  readonly CourseBuilder: {
    readonly Basic: {
      readonly registerField: (section: InjectionSlots['Basic'], fields: InjectedField | InjectedField[]) => void;
      readonly registerContent: (section: InjectionSlots['Basic'], contents: InjectedContent) => void;
    };
    readonly Curriculum: {
      readonly Lesson: {
        readonly registerField: (
          section: InjectionSlots['Curriculum']['Lesson'],
          fields: InjectedField | InjectedField[],
        ) => void;
        readonly registerContent: (section: InjectionSlots['Curriculum']['Lesson'], contents: InjectedContent) => void;
      };
      readonly Quiz: {
        readonly registerField: (
          section: InjectionSlots['Curriculum']['Quiz'],
          fields: InjectedField | InjectedField[],
        ) => void;
        readonly registerContent: (section: InjectionSlots['Curriculum']['Quiz'], contents: InjectedContent) => void;
      };
      readonly Assignment: {
        readonly registerField: (
          section: InjectionSlots['Curriculum']['Assignment'],
          fields: InjectedField | InjectedField[],
        ) => void;
        readonly registerContent: (
          section: InjectionSlots['Curriculum']['Assignment'],
          contents: InjectedContent,
        ) => void;
      };
    };
    readonly Additional: {
      readonly registerField: (section: InjectionSlots['Additional'], fields: InjectedField | InjectedField[]) => void;
      readonly registerContent: (section: InjectionSlots['Additional'], contents: InjectedContent) => void;
    };
  };
}

declare module '*.png';
declare module '*.svg';
declare module '*.jpeg';
declare module '*.jpg';

declare global {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const wp: any;
  interface Window {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    wp: any;
    ajaxurl: string;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    tinymce: any;
    _tutorobject: {
      ID: number;
      ajaxurl: string;
      site_url: string;
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
      backend_course_list_url: string;
      backend_bundle_list_url: string;
      frontend_course_list_url: string;
      frontend_bundle_list_url: string;
      wp_date_format: string;
      wp_rest_nonce: string;
      is_admin: string;
      is_admin_bar_showing: string;
      max_upload_size: string; // in bytes
      content_change_event: string;
      is_tutor_course_edit: string;
      assignment_max_file_allowed: string;
      current_page: string;
      quiz_answer_display_time: string;
      is_ssl: string;
      course_list_page_url: string;
      course_post_type: string;
      local: string;
      coupon_main_content_locales: LocaleData;
      course_builder_basic_locales: LocaleData;
      course_builder_curriculum_locales: LocaleData;
      course_builder_additional_locales: LocaleData;
      difficulty_levels: {
        label: string;
        value: string;
      }[];
      supported_video_sources: {
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
        base_name: string;
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
      settings?: {
        monetize_by: 'wc' | 'tutor' | 'edd';
        enable_course_marketplace: 'on' | 'off';
        course_permalink_base: string;
        supported_video_sources: string[] | string;
        enrollment_expiry_enabled: 'on' | 'off';
        enable_q_and_a_on_course: 'on' | 'off';
        instructor_can_delete_course: 'on' | 'off';
        instructor_can_change_course_author: 'on' | 'off';
        instructor_can_manage_co_instructors: 'on' | 'off';
        chatgpt_enable: 'on' | 'off';
        course_builder_logo_url: string | false;
        chatgpt_key_exist: boolean;
        hide_admin_bar_for_users: 'on' | 'off';
        enable_redirect_on_course_publish_from_frontend: 'on' | 'off';
        instructor_can_publish_course: 'on' | 'off';
        youtube_api_key_exist: boolean;
        membership_only_mode: boolean;
        enable_tax: boolean;
        enable_individual_tax_control: boolean;
        is_tax_included_in_price: boolean;
        pagination_per_page: string;
      };
      tutor_currency: {
        symbol: string;
        currency: string;
        position: string;
        thousand_separator: string;
        decimal_separator: string;
        no_of_decimal: string;
      };
      visibility_control?: {
        course_builder?: Record<string, string>;
      };
    };
    wpApiSettings: {
      nonce: string;
      root: string;
      versionString: string;
    };
    Tutor: Tutor;
  }
}
