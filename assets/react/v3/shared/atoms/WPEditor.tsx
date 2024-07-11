import { spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';

import { useEffect, useRef } from 'react';
import { nanoid } from '@Utils/util';

interface WPEditorProps {
  value: string;
  onChange: (value: string | number) => void;
}

const editorConfig = {
  tinymce: {
    wpautop: false,
    menubar: false,
    branding: false,
    browser_spellcheck: true,
    convert_urls: false,
    end_container_on_empty_block: true,
    entities: '38,amp,60,lt,62,gt',
    entity_encoding: 'raw',
    external_plugins: {
      tutor_button: '/wp-content/plugins/tutor/assets/lib/mce-button.js',
    },
    fix_list_elements: true,
    indent: false,
    plugins:
      'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview, tutor_button',
    skin: 'lightgray',
    submit_patch: true,
    tabfocus_elements: ':prev,:next',
    theme: 'modern',
    toolbar1:
      'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,wp_more,spellchecker,fullscreen,wp_adv,tutor_button',
    toolbar2: 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
    content_css: '/wp-includes/css/dashicons.min.css,/wp-includes/js/tinymce/skins/wordpress/wp-content.css',
    setup: function (editor) {
      editor.on('change', function () {
        console.log('Content changed:', editor.getContent());
      });
    },
    wp_keep_scroll_position: false,
    wpeditimage_html5_captions: true,
  },
  mediaButtons: true,
  dfw: true,
  drag_drop_upload: true,
};

const WPEditor = ({ value, onChange }: WPEditorProps) => {
  const { current: editorId } = useRef(nanoid());

  useEffect(() => {
    if (typeof wp !== 'undefined' && wp.editor) {
      wp.editor.getDefaultSettings = function () {
        return editorConfig;
      };

      wp.editor.initialize(editorId, editorConfig);

      //   setTimeout(function () {
      //     if (typeof tinymce !== 'undefined') {
      //       var editorInstance = tinymce.get(editorId);
      //       if (editorInstance) {
      //         editorInstance.setContent(value);
      //         console.log('Editor instance retrieved:', editorInstance);
      //       } else {
      //         console.error('Editor instance not found');
      //       }
      //     } else {
      //       console.error('TinyMCE not loaded');
      //     }
      //   }, 1000);

      return () => {
        wp.editor.remove(editorId);
      };
    }
  }, [value, onChange]);

  return (
    <>
      <div>
        <textarea id={editorId} />
      </div>
    </>
  );
};

export default WPEditor;

const styles = {
  container: (enableResize = false) => css`
    position: relative;
    display: flex;

    textarea {
      ${typography.body()};
      height: auto;
      padding: ${spacing[8]} ${spacing[12]};
      resize: none;

      ${enableResize &&
      css`
        resize: vertical;
      `}
    }
  `,
};
