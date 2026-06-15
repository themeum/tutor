import { type AlpineComponentMeta } from '@Core/ts/types';

import { TUTOR_CUSTOM_EVENTS } from '../constant';

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
  editorId: string;
  placeholder?: string;
}

interface AlpineComponent {
  $el: HTMLElement;
}

interface WPEditorComponent {
  name: string;
  editorId: string;
  placeholder: string;
  editorInstance: TinyMCEEditor | null;
  isVisualMode: boolean;
  initialized: boolean;
  init(this: AlpineComponent & WPEditorComponent): void;
  setupTinyMCE(this: AlpineComponent & WPEditorComponent): void;
  setupTextarea(this: AlpineComponent & WPEditorComponent): void;
  setupQuickTags(this: AlpineComponent & WPEditorComponent): void;
  setupFormResetListener(this: AlpineComponent & WPEditorComponent): void;
  syncEditorToForm(this: AlpineComponent & WPEditorComponent): void;
  syncTextareaToForm(this: AlpineComponent & WPEditorComponent): void;
  updateFormValue(this: AlpineComponent & WPEditorComponent, content: string): void;
  triggerBlur(this: AlpineComponent & WPEditorComponent): void;
  getContent(this: AlpineComponent & WPEditorComponent): string;
  setContent(this: AlpineComponent & WPEditorComponent, content: string): void;
}

export const wpEditor = (config: WPEditorConfig): WPEditorComponent => {
  const { name, editorId, placeholder = '' } = config;

  return {
    name,
    editorId,
    placeholder,
    editorInstance: null as TinyMCEEditor | null,
    isVisualMode: true,
    initialized: false,

    init(this) {
      if (this.initialized) {
        return;
      }

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

      // Listen for form reset events
      this.setupFormResetListener();

      this.initialized = true;
    },

    setupTinyMCE(this: AlpineComponent & WPEditorComponent) {
      // TinyMCE might not be initialized immediately
      const checkEditor = () => {
        // Use editorId instead of name to support multiple editors
        const editor = window.tinymce?.get(this.editorId);

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
            this.$el.dispatchEvent(new CustomEvent(TUTOR_CUSTOM_EVENTS.WP_EDITOR_FOCUS, { bubbles: true }));
          });

          // Handle Cmd/Ctrl + Enter for form submission
          editor.on('keydown', (e: KeyboardEvent) => {
            if ((e.metaKey || e.ctrlKey) && e.keyCode === 13) {
              e.preventDefault();
              this.syncEditorToForm();
              const formEl = this.$el.closest('form');
              if (formEl) {
                formEl.requestSubmit();
              }
            }
          });

          // Sync theme to iframe
          const setupThemeSync = () => {
            const syncTheme = () => {
              const iframeDoc = editor.getDoc && editor.getDoc();
              if (!iframeDoc || !iframeDoc.body) return;

              const theme = document.documentElement.getAttribute('data-tutor-theme') || 'light';
              iframeDoc.documentElement.setAttribute('data-tutor-theme', theme);

              const body = iframeDoc.body;
              const computed = window.getComputedStyle(document.body);
              const textColor = computed.getPropertyValue('--tutor-text-primary').trim();

              body.style.backgroundColor = 'transparent';
              body.style.color = textColor;
            };

            syncTheme();
            const observer = new MutationObserver((mutations) => {
              mutations.forEach((mutation) => {
                if (mutation.attributeName === 'data-tutor-theme') {
                  syncTheme();
                }
              });
            });
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-tutor-theme'] });
          };

          if (editor.initialized) {
            setupThemeSync();
          } else {
            editor.on('init', setupThemeSync);
          }
        } else {
          // Retry after a short delay
          setTimeout(checkEditor, 100);
        }
      };

      // Start checking for editor
      checkEditor();
    },

    setupTextarea(this: AlpineComponent & WPEditorComponent) {
      const textarea = document.getElementById(this.editorId) as HTMLTextAreaElement;

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
          this.$el.dispatchEvent(new CustomEvent(TUTOR_CUSTOM_EVENTS.WP_EDITOR_FOCUS, { bubbles: true }));
        });
      }
    },

    setupQuickTags(this: AlpineComponent & WPEditorComponent) {
      // QuickTags buttons trigger changes in text mode
      const textarea = document.getElementById(this.editorId) as HTMLTextAreaElement;

      if (textarea) {
        // Monitor for QuickTags button clicks
        // QuickTags toolbars usually have ID "qt_{editorId}_toolbar"
        const toolbarId = `qt_${this.editorId}_toolbar`;
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

    setupFormResetListener(this: AlpineComponent & WPEditorComponent) {
      // Listen for form reset events from tutorForm
      window.addEventListener(TUTOR_CUSTOM_EVENTS.FORM_RESET, (event: Event) => {
        const customEvent = event as CustomEvent;
        const { formId, defaultValues } = customEvent.detail || {};

        // Check if this reset event is for our form by checking if our element is inside the form
        const form = this.$el.closest('form');
        if (form && form.getAttribute('x-data')?.includes(`id: '${formId}'`)) {
          // Reset editor content to default value
          const defaultValue = defaultValues?.[this.name] || '';
          this.setContent(defaultValue);
        }
      });
    },

    syncEditorToForm(this: AlpineComponent & WPEditorComponent) {
      if (this.editorInstance) {
        const content = this.editorInstance.getContent();
        this.updateFormValue(content);
      }
    },

    syncTextareaToForm(this: AlpineComponent & WPEditorComponent) {
      const textarea = document.getElementById(this.name) as HTMLTextAreaElement;
      if (textarea) {
        this.updateFormValue(textarea.value);
      }
    },

    updateFormValue(this: AlpineComponent & WPEditorComponent, content: string) {
      // Get the hidden input that's bound to the form
      const hiddenInput = this.$el.querySelector(`input[name="${this.name}"]`) as HTMLInputElement;

      if (hiddenInput) {
        // Update the hidden input value
        hiddenInput.value = content;

        // Trigger input event to update form state
        hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
      }
    },

    triggerBlur(this: AlpineComponent & WPEditorComponent) {
      const hiddenInput = this.$el.querySelector(`input[name="${this.name}"]`) as HTMLInputElement;

      if (hiddenInput) {
        hiddenInput.dispatchEvent(new Event('blur', { bubbles: true }));
      }
    },

    getContent(this: AlpineComponent & WPEditorComponent): string {
      if (this.isVisualMode && this.editorInstance) {
        return this.editorInstance.getContent();
      }

      const textarea = document.getElementById(this.name) as HTMLTextAreaElement;
      return textarea ? textarea.value : '';
    },

    setContent(this: AlpineComponent & WPEditorComponent, content: string) {
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
