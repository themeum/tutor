import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import WPEditor from '@Atoms/WPEditor';

import SVGIcon from '@Atoms/SVGIcon';
import { useModal } from '@Components/modals/Modal';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import For from '@Controls/For';
import Show from '@Controls/Show';
import EditorModal from '@CourseBuilderComponents/modals/EditorModal';
import type { Editor } from '@CourseBuilderServices/course';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import type { IconCollection } from '@Utils/types';
import FormFieldWrapper from './FormFieldWrapper';

interface FormWPEditorProps extends FormControllerProps<string | null> {
  label?: string;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: string) => void;
  hasCustomEditorSupport?: boolean;
  editors?: Editor[];
  editorUsed?: Editor;
}

const customEditorIcons: { [key: string]: IconCollection } = {
  droip: 'droip',
};

const FormWPEditor = ({
  label,
  field,
  fieldState,
  disabled,
  readOnly,
  loading,
  placeholder,
  helpText,
  onChange,
  hasCustomEditorSupport = false,
  editors,
  editorUsed = { name: 'classic', label: 'Classic Editor', link: '' },
}: FormWPEditorProps) => {
  const { showModal } = useModal();

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      disabled={disabled}
      readOnly={readOnly}
      placeholder={placeholder}
      helpText={helpText}
    >
      {() => {
        return (
          <Show
            when={hasCustomEditorSupport}
            fallback={
              <WPEditor
                value={field.value ?? ''}
                onChange={(value) => {
                  field.onChange(value);

                  if (onChange) {
                    onChange(value);
                  }
                }}
              />
            }
          >
            <Show
              when={editorUsed.name === 'classic' && !loading}
              fallback={
                <div css={styles.editorOverlay}>
                  <Button
                    variant="primary"
                    loading={loading}
                    icon={
                      customEditorIcons[editorUsed.name] && (
                        <SVGIcon name={customEditorIcons[editorUsed.name]} height={24} width={24} />
                      )
                    }
                    onClick={() =>
                      editorUsed &&
                      showModal({
                        component: EditorModal,
                        props: {
                          title: __(`${editorUsed.name} Editor`, 'tutor'),
                          editorUsed: editorUsed,
                        },
                      })
                    }
                  >
                    {editorUsed?.label}
                  </Button>
                </div>
              }
            >
              <div css={styles.editorsButtonWrapper}>
                <For each={editors || []}>
                  {(editor) => (
                    <Button
                      key={editor.name}
                      icon={
                        customEditorIcons[editor.name] && (
                          <SVGIcon name={customEditorIcons[editor.name]} height={24} width={24} />
                        )
                      }
                      onClick={() => {
                        showModal({
                          component: EditorModal,
                          props: {
                            title: __(`${editorUsed.name} Editor`, 'tutor'),
                            editorUsed: editor,
                          },
                        });
                      }}
                      type="button"
                      variant="secondary"
                    >
                      {editor.label}
                    </Button>
                  )}
                </For>
              </div>
              <WPEditor
                value={field.value ?? ''}
                onChange={(value) => {
                  field.onChange(value);

                  if (onChange) {
                    onChange(value);
                  }
                }}
              />
            </Show>
          </Show>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormWPEditor;

const styles = {
  editorsButtonWrapper: css`
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    padding-bottom: ${spacing[10]};
    gap: ${spacing[8]};

    * {
      flex-shrink: 0;
      margin-right: ${spacing[8]};
    }
  `,
  editorOverlay: css`
    height: 360px;
    ${styleUtils.flexCenter()};
    background-color: ${colorTokens.bg.gray20};
    border-radius: ${borderRadius.card};
  `,
};
