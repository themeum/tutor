import { type AlpineComponentMeta } from '@Core/ts/types';

// Global type declarations for WordPress editor
declare global {
  interface Window {
    tinymce?: {
      get(id: string): TinyMCEEditor | null;
    };
    quicktags?: unknown;
  }
}

interface TinyMCEEditor {
  getContent(): string;
  setContent(content: string): void;
  on(event: string, callback: () => void): void;
  isHidden(): boolean;
  settings: {
    placeholder?: string;
  };
}

interface WPEditorConfig {
  name: string;
  placeholder?: string;
}

interface AlpineComponent {
  $el: HTMLElement;
}

/**
 * WordPress Editor Alpine.js Component
 * Integrates wp_editor (TinyMCE/QuickTags) with tutorForm validation system
 */
export const wpEditor = (config: WPEditorConfig) => {
  const { name, placeholder = '' } = config;

  return {
    name,
    placeholder,
    editorInstance: null as TinyMCEEditor | null,
    isVisualMode: true,
    initialized: false,

    init(this) {
      // Wait for TinyMCE to be ready
      if (typeof window.tinymce !== 'undefined') {
        this.setupTinyMCE();
      } else {
        // Fallback to textarea if TinyMCE is not available
        this.setupTextarea();
      }

      // Setup QuickTags if available
      if (typeof window.quicktags !== 'undefined') {
        this.setupQuickTags();
      }

      this.initialized = true;
    },

    setupTinyMCE(this: AlpineComponent & ReturnType<typeof wpEditor>) {
      // TinyMCE might not be initialized immediately
      const checkEditor = () => {
        const editor = window.tinymce?.get(this.name);

        if (editor) {
          this.editorInstance = editor;
          this.isVisualMode = !editor.isHidden();

          // Set placeholder if provided
          if (this.placeholder) {
            editor.settings.placeholder = this.placeholder;
          }

          // Sync editor content with form value on change
          editor.on('change keyup', () => {
            this.syncEditorToForm();
          });

          // Sync on blur for validation
          editor.on('blur', () => {
            this.syncEditorToForm();
            this.triggerBlur();
          });

          // Sync when switching between Visual/Text modes
          editor.on('hide', () => {
            this.isVisualMode = false;
          });

          editor.on('show', () => {
            this.isVisualMode = true;
            this.syncEditorToForm();
          });

          // Dispatch custom focus event
          editor.on('focus', () => {
            this.$el.dispatchEvent(new CustomEvent('editor-focus', { bubbles: true }));
          });
        } else {
          // Retry after a short delay
          setTimeout(checkEditor, 100);
        }
      };

      // Start checking for editor
      checkEditor();
    },

    setupTextarea(this: AlpineComponent & ReturnType<typeof wpEditor>) {
      const textarea = document.getElementById(this.name) as HTMLTextAreaElement;

      if (textarea) {
        // Set placeholder
        if (this.placeholder) {
          textarea.placeholder = this.placeholder;
        }

        // Sync textarea content with form value
        textarea.addEventListener('input', () => {
          this.syncTextareaToForm();
        });

        textarea.addEventListener('blur', () => {
          this.syncTextareaToForm();
          this.triggerBlur();
        });

        // Dispatch custom focus event
        textarea.addEventListener('focus', () => {
          this.$el.dispatchEvent(new CustomEvent('editor-focus', { bubbles: true }));
        });
      }
    },

    setupQuickTags(this: AlpineComponent & ReturnType<typeof wpEditor>) {
      // QuickTags buttons trigger changes in text mode
      const textarea = document.getElementById(this.name) as HTMLTextAreaElement;

      if (textarea) {
        // Monitor for QuickTags button clicks
        // Monitor for QuickTags button clicks
        // QuickTags toolbars usually have ID "qt_{name}_toolbar"
        const toolbarId = `qt_${this.name}_toolbar`;
        const toolbar = document.getElementById(toolbarId);

        if (toolbar) {
          toolbar.addEventListener('click', () => {
            // Small delay to allow QuickTags to update the textarea
            setTimeout(() => {
              if (!this.isVisualMode) {
                this.syncTextareaToForm();
              }
            }, 50);
          });
        }
      }
    },

    syncEditorToForm(this: AlpineComponent & ReturnType<typeof wpEditor>) {
      if (this.editorInstance) {
        const content = this.editorInstance.getContent();
        this.updateFormValue(content);
      }
    },

    syncTextareaToForm(this: AlpineComponent & ReturnType<typeof wpEditor>) {
      const textarea = document.getElementById(this.name) as HTMLTextAreaElement;
      if (textarea) {
        this.updateFormValue(textarea.value);
      }
    },

    updateFormValue(this: AlpineComponent & ReturnType<typeof wpEditor>, content: string) {
      // Get the hidden input that's bound to the form
      const hiddenInput = this.$el.querySelector(`input[name="${this.name}"]`) as HTMLInputElement;

      if (hiddenInput) {
        // Update the hidden input value
        hiddenInput.value = content;

        // Trigger input event to update form state
        hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
      }
    },

    triggerBlur(this: AlpineComponent & ReturnType<typeof wpEditor>) {
      const hiddenInput = this.$el.querySelector(`input[name="${this.name}"]`) as HTMLInputElement;

      if (hiddenInput) {
        hiddenInput.dispatchEvent(new Event('blur', { bubbles: true }));
      }
    },

    getContent(this: AlpineComponent & ReturnType<typeof wpEditor>): string {
      if (this.isVisualMode && this.editorInstance) {
        return this.editorInstance.getContent();
      }

      const textarea = document.getElementById(this.name) as HTMLTextAreaElement;
      return textarea ? textarea.value : '';
    },

    setContent(this: AlpineComponent & ReturnType<typeof wpEditor>, content: string) {
      if (this.isVisualMode && this.editorInstance) {
        this.editorInstance.setContent(content);
      }

      const textarea = document.getElementById(this.name) as HTMLTextAreaElement;
      if (textarea) {
        textarea.value = content;
      }

      this.updateFormValue(content);
    },
  };
};

export const wpEditorMeta: AlpineComponentMeta<WPEditorConfig> = {
  name: 'WPEditor',
  component: wpEditor,
};
