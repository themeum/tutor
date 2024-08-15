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
  editors = [],
  editorUsed = { name: 'classic', label: 'Classic Editor', link: '' },
}: FormWPEditorProps) => {
  const { showModal } = useModal();

  const editorLabel = hasCustomEditorSupport ? (
    <div css={styles.editorLabel}>
      <span>{label}</span>
      <div css={styles.editorsButtonWrapper}>
        <span>{__('Edit with', 'tutor')}</span>
        <div css={styles.customEditorButtons}>
          <For each={editors}>
            {(editor) => (
              <button
                key={editor.name}
                type="button"
                css={styles.customEditorButton}
                onClick={() => showModal({ component: EditorModal, props: { editorUsed: editor } })}
              >
                {editor.label}
              </button>
            )}
          </For>
        </div>
      </div>
    </div>
  ) : (
    label
  );

  return (
    <FormFieldWrapper
      label={editorLabel}
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
  editorLabel: css`
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: space-between;
  `,
  editorsButtonWrapper: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    color: ${colorTokens.text.hints};
  `,
  customEditorButtons: css`
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
  `,
  customEditorButton: css`
    ${styleUtils.resetButton}
  `,
  editorOverlay: css`
    height: 360px;
    ${styleUtils.flexCenter()};
    background-color: ${colorTokens.bg.gray20};
    border-radius: ${borderRadius.card};
  `,
};
