import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useRef, useState } from 'react';

import { tutorConfig } from '@TutorShared/config/config';
import { CURRENT_VIEWPORT } from '@TutorShared/config/constants';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { nanoid } from '@TutorShared/utils/util';

interface WPEditorProps {
  value: string;
  onChange: (value: string) => void;
  isMinimal?: boolean;
  hideMediaButtons?: boolean;
  hideQuickTags?: boolean;
  autoFocus?: boolean;
  onFullScreenChange?: (isFullScreen: boolean) => void;
  readonly?: boolean;
  min_height?: number;
  max_height?: number;
  toolbar1?: string;
  toolbar2?: string;
}

const isTutorPro = !!tutorConfig.tutor_pro_url;

// Without getDefaultSettings function editor does not initiate
if (!window.wp.editor.getDefaultSettings) {
  window.wp.editor.getDefaultSettings = () => ({});
}

function editorConfig(
  isFocused: boolean,
  onChange: (value: string) => void,
  setIsFocused: (value: boolean) => void,
  isMinimal?: boolean,
  hideMediaButtons?: boolean,
  hideQuickTags?: boolean,
  onFullScreenChange?: (isFullScreen: boolean) => void,
  readOnly?: boolean,
  min_height?: number,
  max_height?: number,
  isAboveMobile?: boolean,
  propsToolbar1?: string,
  propsToolbar2?: string,
) {
  let toolbar1 =
    propsToolbar1 ??
    (isMinimal
      ? `bold italic underline | image | ${isTutorPro ? 'codesample' : ''}`
      : `formatselect bold italic underline | bullist numlist | blockquote | alignleft aligncenter alignright | link unlink | wp_more ${
          isTutorPro ? ' codesample' : ''
        } | wp_adv`);

  const toolbar2 =
    propsToolbar2 ??
    'strikethrough hr | forecolor pastetext removeformat | charmap | outdent indent | undo redo | wp_help | fullscreen | tutor_button | undoRedoDropdown';

  toolbar1 = isAboveMobile ? toolbar1 : toolbar1.replaceAll(' | ', ' ');

  return {
    tinymce: {
      wpautop: true,
      menubar: false,
      autoresize_min_height: min_height || 200,
      autoresize_max_height: max_height || 500,
      wp_autoresize_on: true,
      browser_spellcheck: !readOnly,
      convert_urls: false,
      end_container_on_empty_block: true,
      entities: '38,amp,60,lt,62,gt',
      entity_encoding: 'raw',
      fix_list_elements: true,
      indent: false,
      relative_urls: 0,
      remove_script_host: 0,
      plugins: `charmap,colorpicker,hr,lists,image,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview${isTutorPro ? ',codesample' : ''}`,
      skin: 'light',
      skin_url: `${tutorConfig.site_url}/wp-content/plugins/tutor/assets/lib/tinymce/light`,
      submit_patch: true,
      link_context_toolbar: false,
      theme: 'modern',
      toolbar: !readOnly,
      toolbar1: toolbar1,
      toolbar2: isMinimal ? false : toolbar2,
      content_css: `${tutorConfig.site_url}/wp-includes/css/dashicons.min.css,${tutorConfig.site_url}/wp-includes/js/tinymce/skins/wordpress/wp-content.css,${tutorConfig.site_url}/wp-content/plugins/tutor/assets/lib/tinymce/light/content.min.css`,

      statusbar: !readOnly,
      branding: false,
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      setup: (editor: any) => {
        editor.on('init', () => {
          if (isFocused && !readOnly) {
            editor.getBody().focus();
          }

          if (readOnly) {
            editor.setMode('readonly');

            const editorBody = editor.contentDocument.querySelector('.mce-content-body');
            editorBody.style.backgroundColor = 'transparent';

            setTimeout(() => {
              const height = editorBody.scrollHeight;

              if (height) {
                editor.iframeElement.style.height = `${height}px`;
              }
            }, 500);
          }
        });

        if (!isMinimal) {
          editor.addButton('tutor_button', {
            text: __('Tutor ShortCode', __TUTOR_TEXT_DOMAIN__),
            icon: false,
            type: 'menubutton',
            menu: [
              {
                text: __('Student Registration Form', __TUTOR_TEXT_DOMAIN__),
                onclick: () => {
                  editor.insertContent('[tutor_student_registration_form]');
                },
              },
              {
                text: __('Instructor Registration Form', __TUTOR_TEXT_DOMAIN__),
                onclick: () => {
                  editor.insertContent('[tutor_instructor_registration_form]');
                },
              },
              {
                text: __('Courses', __TUTOR_TEXT_DOMAIN__),
                onclick: () => {
                  editor.windowManager.open({
                    title: __('Courses Shortcode', __TUTOR_TEXT_DOMAIN__),
                    body: [
                      {
                        type: 'textbox',
                        name: 'id',
                        label: __('Course id, separate by (,) comma', __TUTOR_TEXT_DOMAIN__),
                        value: '',
                      },
                      {
                        type: 'textbox',
                        name: 'exclude_ids',
                        label: __('Exclude Course IDS', __TUTOR_TEXT_DOMAIN__),
                        value: '',
                      },
                      {
                        type: 'textbox',
                        name: 'category',
                        label: __('Category IDS', __TUTOR_TEXT_DOMAIN__),
                        value: '',
                      },
                      {
                        type: 'listbox',
                        name: 'orderby',
                        label: __('Order By', __TUTOR_TEXT_DOMAIN__),
                        onselect: () => {},
                        values: [
                          { text: 'ID', value: 'ID' },
                          { text: 'title', value: 'title' },
                          { text: 'rand', value: 'rand' },
                          { text: 'date', value: 'date' },
                          { text: 'menu_order', value: 'menu_order' },
                          { text: 'post__in', value: 'post__in' },
                        ],
                      },
                      {
                        type: 'listbox',
                        name: 'order',
                        label: __('Order', __TUTOR_TEXT_DOMAIN__),
                        onselect: () => {},
                        values: [
                          { text: 'DESC', value: 'DESC' },
                          { text: 'ASC', value: 'ASC' },
                        ],
                      },
                      {
                        type: 'textbox',
                        name: 'count',
                        label: __('Count', __TUTOR_TEXT_DOMAIN__),
                        value: '6',
                      },
                    ],
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    onsubmit: (e: any) => {
                      editor.insertContent(
                        `[tutor_course id="${e.data.id}" exclude_ids="${e.data.exclude_ids}" category="${e.data.category}" orderby="${e.data.orderby}" order="${e.data.order}" count="${e.data.count}"]`,
                      );
                    },
                  });
                },
              },
            ],
          });
        }
        editor.on('change keyup paste', () => {
          onChange(editor.getContent());
        });
        editor.on('focus', () => {
          setIsFocused(true);
        });
        editor.on('blur', () => setIsFocused(false));
        editor.on('FullscreenStateChanged', (event: { state: boolean }) => {
          const courseBuilder = document.getElementById('tutor-course-builder');
          const courseBundleBuilder = document.getElementById('tutor-course-bundle-builder-root');
          const builderWrapper = courseBuilder || courseBundleBuilder;
          if (builderWrapper) {
            if (event.state) {
              builderWrapper.style.position = 'relative';
              builderWrapper.style.zIndex = '100000';
            } else {
              builderWrapper.removeAttribute('style');
            }
          }

          onFullScreenChange?.(event.state);
        });
      },
      wp_keep_scroll_position: false,
      wpeditimage_html5_captions: true,
    },
    mediaButtons: !hideMediaButtons && !isMinimal && !readOnly,
    drag_drop_upload: true,
    quicktags:
      hideQuickTags || isMinimal || readOnly
        ? false
        : {
            buttons: ['strong', 'em', 'block', 'del', 'ins', 'img', 'ul', 'ol', 'li', 'code', 'more', 'close'],
          },
  };
}

const WPEditor = ({
  value = '',
  onChange,
  isMinimal,
  hideMediaButtons,
  hideQuickTags,
  autoFocus = false,
  onFullScreenChange,
  readonly = false,
  min_height,
  max_height,
  toolbar1,
  toolbar2,
}: WPEditorProps) => {
  const editorRef = useRef<HTMLTextAreaElement>(null);
  const { current: editorId } = useRef(nanoid());
  const [isFocused, setIsFocused] = useState(autoFocus);

  const handleOnChange = (event: Event) => {
    const target = event.target as HTMLTextAreaElement;
    onChange(target.value);
  };

  const updateEditorContent = useCallback(
    (value: string) => {
      const { tinymce } = window;
      if (!tinymce || isFocused) {
        return;
      }

      const editorInstance = window.tinymce.get(editorId);
      if (editorInstance) {
        if (value !== editorInstance.getContent()) {
          editorInstance.setContent(value);
        }
      }
    },
    [editorId, isFocused],
  );

  useEffect(() => {
    updateEditorContent(value);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [value]);

  useEffect(() => {
    if (typeof window.wp !== 'undefined' && window.wp.editor) {
      window.wp.editor.remove(editorId);
      window.wp.editor.initialize(
        editorId,
        editorConfig(
          isFocused,
          onChange,
          setIsFocused,
          isMinimal,
          hideMediaButtons,
          hideQuickTags,
          onFullScreenChange,
          readonly,
          min_height,
          max_height,
          CURRENT_VIEWPORT.isAboveMobile,
          toolbar1,
          toolbar2,
        ),
      );

      const currentRef = editorRef.current;
      currentRef?.addEventListener('change', handleOnChange);
      currentRef?.addEventListener('input', handleOnChange);

      return () => {
        window.wp.editor.remove(editorId);

        currentRef?.removeEventListener('change', handleOnChange);
        currentRef?.removeEventListener('input', handleOnChange);
      };
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [readonly]);

  return (
    <div
      css={styles.wrapper({
        hideQuickTags,
        isMinimal,
        isFocused,
        isReadOnly: readonly,
      })}
    >
      <textarea data-cy="tutor-tinymce" ref={editorRef} id={editorId} defaultValue={value} />
    </div>
  );
};

export default WPEditor;

const styles = {
  wrapper: ({
    hideQuickTags,
    isMinimal,
    isFocused,
    isReadOnly,
  }: {
    hideQuickTags?: boolean;
    isMinimal?: boolean;
    isFocused: boolean;
    isReadOnly?: boolean;
  }) => css`
    flex: 1;

    .wp-editor-tools {
      z-index: auto;
    }

    .wp-editor-container {
      border-top-left-radius: ${borderRadius[6]};
      border-bottom-left-radius: ${borderRadius[6]};
      border-bottom-right-radius: ${borderRadius[6]};

      ${isFocused &&
      !isReadOnly &&
      css`
        ${styleUtils.inputFocus}
      `}

      :focus-within {
        ${!isReadOnly && styleUtils.inputFocus}
      }
    }

    .wp-switch-editor {
      height: auto;
      border: 1px solid #dcdcde;
      border-radius: 0px;
      border-top-left-radius: ${borderRadius[4]};
      border-top-right-radius: ${borderRadius[4]};
      top: 2px;
      padding: 3px 8px 4px;
      font-size: 13px;
      color: #646970;

      &:focus,
      &:active,
      &:hover {
        background: #f0f0f1;
        color: #646970;
      }
    }

    .mce-btn button {
      &:focus,
      &:active,
      &:hover {
        background: none;
        color: #50575e;
      }
    }

    .mce-toolbar-grp,
    .quicktags-toolbar {
      border-top-left-radius: ${borderRadius[6]};

      ${(hideQuickTags || isMinimal) &&
      css`
        border-top-right-radius: ${borderRadius[6]};
      `}
    }

    .mce-top-part::before {
      display: none;
    }

    .mce-statusbar {
      border-bottom-left-radius: ${borderRadius[6]};
      border-bottom-right-radius: ${borderRadius[6]};
    }

    .mce-tinymce {
      box-shadow: none;
      background-color: transparent;
    }

    .mce-edit-area {
      background-color: unset;
    }

    ${(hideQuickTags || isMinimal) &&
    css`
      .mce-tinymce.mce-container {
        border: ${!isReadOnly ? `1px solid ${colorTokens.stroke.default}` : 'none'};
        border-radius: ${borderRadius[6]};

        ${isFocused &&
        !isReadOnly &&
        css`
          ${styleUtils.inputFocus}
        `}
      }
    `}

    textarea {
      visibility: visible !important;
      width: 100%;
      resize: none;
      border: none;
      outline: none;
      padding: ${spacing[10]};
    }
  `,
};
