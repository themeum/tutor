declare module '*.png';
declare module '*.jpg';
declare module '*.svg';
declare module '*.gif';
declare module '*.webp';

interface Window {
  tutor_get_nonce_data: (value: boolean) => { key: string; value: string };
}

declare const __TUTOR_TEXT_DOMAIN__: string;
