import { tutorConfig } from '@Config/config';
import { nanoid } from '@Utils/util';
import { css } from '@emotion/react';
import { __, _x } from '@wordpress/i18n';
import { useCallback, useEffect, useRef, useState } from 'react';

interface WPEditorProps {
  value: string;
  onChange: (value: string) => void;
}

const isTutorPro = !!tutorConfig.tutor_pro_url;

// Without getDefaultSettings function editor does not initiate
if (!window.wp.editor.getDefaultSettings) {
  window.wp.editor.getDefaultSettings = () => ({});
}

function editorConfig(onChange: (value: string) => void, setIsFocused: (value: boolean) => void) {
  return {
    tinymce: {
      wpautop: true,
      menubar: false,
      branding: false,
      height: 200,
      browser_spellcheck: true,
      convert_urls: false,
      end_container_on_empty_block: true,
      entities: '38,amp,60,lt,62,gt',
      entity_encoding: 'raw',
      ...(isTutorPro && {
        external_plugins: {
          codesample: 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.1.2/plugins/codesample/plugin.min.js',
        },
        codesample_languages: [
          { text: 'HTML/XML', value: 'markup' },
          { text: 'JavaScript', value: 'javascript' },
          { text: 'CSS', value: 'css' },
          { text: 'PHP', value: 'php' },
          { text: 'Ruby', value: 'ruby' },
          { text: 'Python', value: 'python' },
          { text: 'Java', value: 'java' },
          { text: 'C', value: 'c' },
          { text: 'C#', value: 'csharp' },
          { text: 'C++', value: 'cpp' },
        ],
      }),
      fix_list_elements: true,
      indent: false,
      relative_urls: 0,
      remove_script_host: 0,
      plugins:
        'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview',
      skin: 'lightgray',
      submit_patch: true,
      link_context_toolbar: false,
      theme: 'modern',
      toolbar1: `formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_more,fullscreen,wp_adv,tutor_button${
        isTutorPro ? ',codesample' : ''
      }`,
      toolbar2: 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
      content_css: '/wp-includes/css/dashicons.min.css,/wp-includes/js/tinymce/skins/wordpress/wp-content.css',
      // biome-ignore lint/suspicious/noExplicitAny: <explanation>
      setup: (editor: any) => {
        editor.addButton('tutor_button', {
          text: __('Tutor ShortCode', 'tutor'),
          icon: false,
          type: 'menubutton',
          menu: [
            {
              text: __('Student Registration Form', 'tutor'),
              onclick: () => {
                editor.insertContent('[tutor_student_registration_form]');
              },
            },
            {
              text: __('Instructor Registration Form', 'tutor'),
              onclick: () => {
                editor.insertContent('[tutor_instructor_registration_form]');
              },
            },
            {
              text: _x('Courses', 'tinyMCE button courses', 'tutor'),
              onclick: () => {
                editor.windowManager.open({
                  title: __('Courses Shortcode', 'tutor'),
                  body: [
                    {
                      type: 'textbox',
                      name: 'id',
                      label: __('Course id, separate by (,) comma', 'tutor'),
                      value: '',
                    },
                    {
                      type: 'textbox',
                      name: 'exclude_ids',
                      label: __('Exclude Course IDS', 'tutor'),
                      value: '',
                    },
                    {
                      type: 'textbox',
                      name: 'category',
                      label: __('Category IDS', 'tutor'),
                      value: '',
                    },
                    {
                      type: 'listbox',
                      name: 'orderby',
                      label: _x('Order By', 'tinyMCE button order by', 'tutor'),
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
                      label: _x('Order', 'tinyMCE button order', 'tutor'),
                      onselect: () => {},
                      values: [
                        { text: 'DESC', value: 'DESC' },
                        { text: 'ASC', value: 'ASC' },
                      ],
                    },
                    {
                      type: 'textbox',
                      name: 'count',
                      label: __('Count', 'tutor'),
                      value: '6',
                    },
                  ],
                  // biome-ignore lint/suspicious/noExplicitAny: <explanation>
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
        editor.on('change keyup paste', () => {
          onChange(editor.getContent());
        });
        editor.on('focus', () => setIsFocused(true));
        editor.on('blur', () => setIsFocused(false));
      },
      wp_keep_scroll_position: false,
      wpeditimage_html5_captions: true,
    },
    mediaButtons: true,
    drag_drop_upload: true,
    quicktags: {
      buttons: ['strong', 'em', 'block', 'del', 'ins', 'img', 'ul', 'ol', 'li', 'code', 'more', 'close'],
    },
  };
}

const WPEditor = ({ value, onChange }: WPEditorProps) => {
  const editorRef = useRef<HTMLTextAreaElement>(null);
  const { current: editorId } = useRef(nanoid());
  const [isFocused, setIsFocused] = useState(false);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
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

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    const timeoutId = setTimeout(() => {
      updateEditorContent(value);
    }, 100);

    return () => {
      clearTimeout(timeoutId);
    };
  }, [value]);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (typeof window.wp !== 'undefined' && window.wp.editor) {
      window.wp.editor.remove(editorId);
      window.wp.editor.initialize(editorId, editorConfig(onChange, setIsFocused));

      editorRef.current?.addEventListener('change', (e) => {
        onChange((e.target as HTMLInputElement)?.value);
      });
      editorRef.current?.addEventListener('input', (e) => {
        onChange((e.target as HTMLInputElement)?.value);
      });

      return () => {
        window.wp.editor.remove(editorId);

        editorRef.current?.removeEventListener('change', (e) => {
          onChange((e.target as HTMLInputElement)?.value);
        });
        editorRef.current?.removeEventListener('input', (e) => {
          onChange((e.target as HTMLInputElement)?.value);
        });
      };
    }
  }, []);

  return (
    <div css={styles.wrapper}>
      <textarea ref={editorRef} id={editorId} />
    </div>
  );
};

export default WPEditor;

const styles = {
  wrapper: css`
    flex: 1;

    .wp-editor-tools {
      z-index: auto;
    }

    textarea {
      visibility: visible !important;
      width: 100%;
      resize: none;
      border: none;
      outline: none;
    }

    .mce-active {
      background: #555c66 !important;
    }
  `,
};
