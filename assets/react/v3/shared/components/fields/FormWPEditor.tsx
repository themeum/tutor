import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';

import Button from '@Atoms/Button';
import WPEditor from '@Atoms/WPEditor';

import { useModal } from '@Components/modals/Modal';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import For from '@Controls/For';
import Show from '@Controls/Show';
import EditorModal from '@CourseBuilderComponents/modals/EditorModal';
import type { CourseDetailsResponse } from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import FormFieldWrapper from './FormFieldWrapper';

interface FormWPEditorProps extends FormControllerProps<string | null> {
  label?: string;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: string) => void;
  showCustomEditorOverlay?: boolean;
}

const courseId = getCourseId();

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
  showCustomEditorOverlay,
}: FormWPEditorProps) => {
  const queryClient = useQueryClient();
  const { showModal } = useModal();

  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      disabled={disabled}
      readOnly={readOnly}
      loading={loading}
      placeholder={placeholder}
      helpText={helpText}
    >
      {() => {
        return (
          <Show
            when={showCustomEditorOverlay}
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
              when={courseDetails?.editor_used?.name === 'classic'}
              fallback={
                <div css={styles.editorOverlay}>
                  <Button
                    variant="primary"
                    loading={
                      !!queryClient.isFetching({
                        queryKey: ['CourseDetails', courseId],
                      })
                    }
                    onClick={() =>
                      showModal({
                        component: EditorModal,
                        props: {
                          title: `${
                            courseDetails?.editor_used?.name.charAt(0).toUpperCase() + courseDetails?.editor_used.name
                          } editor`,
                          editorUsed: courseDetails?.editor_used,
                        },
                      })
                    }
                  >
                    {courseDetails?.editor_used?.label}
                  </Button>
                </div>
              }
            >
              <div css={styles.editorsButtonWrapper}>
                <For each={courseDetails?.editors}>
                  {(editor) => (
                    <Button
                      key={editor.name}
                      onClick={() => {
                        showModal({
                          component: EditorModal,
                          props: {
                            title: editor.label,
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
