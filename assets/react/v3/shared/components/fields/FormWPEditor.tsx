import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import rgba from 'polished/lib/color/rgba';
import type React from 'react';
import { useState } from 'react';

import Alert from '@TutorShared/atoms/Alert';
import Button from '@TutorShared/atoms/Button';
import LoadingSpinner, { LoadingOverlay } from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Tooltip from '@TutorShared/atoms/Tooltip';
import WPEditor from '@TutorShared/atoms/WPEditor';

import AITextModal from '@TutorShared/components/modals/AITextModal';
import ConfirmationModal from '@TutorShared/components/modals/ConfirmationModal';
import { useModal } from '@TutorShared/components/modals/Modal';
import ProIdentifierModal from '@TutorShared/components/modals/ProIdentifierModal';
import SetupOpenAiModal from '@TutorShared/components/modals/SetupOpenAiModal';

import { tutorConfig } from '@TutorShared/config/config';
import { TutorRoles } from '@TutorShared/config/constants';
import { borderRadius, colorTokens, spacing, zIndex } from '@TutorShared/config/styles';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { type IconCollection } from '@TutorShared/icons/types';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';
import type { Editor, TutorMutationResponse } from '@TutorShared/utils/types';

import generateText2x from '@SharedImages/pro-placeholders/generate-text-2x.webp';
import generateText from '@SharedImages/pro-placeholders/generate-text.webp';

import FormFieldWrapper from './FormFieldWrapper';

interface FormWPEditorProps extends FormControllerProps<string | null> {
  label?: string | React.ReactNode;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: string) => void;
  generateWithAi?: boolean;
  onClickAiButton?: () => void;
  hasCustomEditorSupport?: boolean;
  isMinimal?: boolean;
  hideMediaButtons?: boolean;
  hideQuickTags?: boolean;
  editors?: Editor[];
  editorUsed?: Editor;
  isMagicAi?: boolean;
  autoFocus?: boolean;
  onCustomEditorButtonClick?: (editor: Editor) => Promise<void>;
  onBackToWPEditorClick?: (builder: string) => Promise<TutorMutationResponse<null>>;
  onFullScreenChange?: (isFullScreen: boolean) => void;
  min_height?: number;
  max_height?: number;
  toolbar1?: string;
  toolbar2?: string;
}

interface CustomEditorOverlayProps {
  editorUsed: Editor;
  onCustomEditorButtonClick?: (editor: Editor) => Promise<void>;
  onBackToWPEditorClick?: (builder: string) => Promise<TutorMutationResponse<null>>;
}

const customEditorIcons: { [key: string]: IconCollection } = {
  droip: 'droipColorized',
  elementor: 'elementorColorized',
  gutenberg: 'gutenbergColorized',
  divi: 'diviColorized',
};

const isTutorPro = !!tutorConfig.tutor_pro_url;
const hasOpenAiAPIKey = tutorConfig.settings?.chatgpt_key_exist;

const CustomEditorOverlay = ({
  editorUsed,
  onBackToWPEditorClick,
  onCustomEditorButtonClick,
}: CustomEditorOverlayProps) => {
  const { showModal } = useModal();
  const [loadingButton, setLoadingButton] = useState('');

  return (
    <div css={styles.editorOverlay}>
      <Show when={editorUsed.name !== 'gutenberg'}>
        <Button
          variant="tertiary"
          size="small"
          buttonCss={styles.editWithButton}
          icon={<SVGIcon name="arrowLeft" height={24} width={24} />}
          loading={loadingButton === 'back_to'}
          onClick={async () => {
            const { action } = await showModal({
              component: ConfirmationModal,
              props: {
                title: __('Back to WordPress Editor', __TUTOR_TEXT_DOMAIN__),
                description: (
                  <Alert type="warning" icon="warning">
                    {
                      // prettier-ignore
                      __( 'Warning: Switching to the WordPress default editor may cause issues with your current layout, design, and content.', __TUTOR_TEXT_DOMAIN__)
                    }
                  </Alert>
                ),
                confirmButtonText: __('Confirm', __TUTOR_TEXT_DOMAIN__),
                confirmButtonVariant: 'primary',
              },
              depthIndex: zIndex.highest,
            });
            if (action === 'CONFIRM') {
              try {
                setLoadingButton('back_to');
                await onBackToWPEditorClick?.(editorUsed.name);
              } finally {
                setLoadingButton('');
              }
            }
          }}
        >
          {__('Back to WordPress Editor', __TUTOR_TEXT_DOMAIN__)}
        </Button>
      </Show>
      <Button
        variant="tertiary"
        size="small"
        buttonCss={styles.editWithButton}
        loading={loadingButton === 'edit_with'}
        icon={
          customEditorIcons[editorUsed.name] && (
            <SVGIcon name={customEditorIcons[editorUsed.name]} height={24} width={24} />
          )
        }
        onClick={async () => {
          try {
            setLoadingButton('edit_with');
            await onCustomEditorButtonClick?.(editorUsed);
            window.location.href = editorUsed.link;
          } finally {
            setLoadingButton('');
          }
        }}
      >
        {
          /* translators: %s is the editor name */
          sprintf(__('Edit with %s', __TUTOR_TEXT_DOMAIN__), editorUsed?.label)
        }
      </Button>
    </div>
  );
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
  generateWithAi = false,
  onClickAiButton,
  hasCustomEditorSupport = false,
  isMinimal = false,
  hideMediaButtons = false,
  hideQuickTags = false,
  editors = [],
  editorUsed = { name: 'classic', label: 'Classic Editor', link: '' },
  isMagicAi = false,
  autoFocus = false,
  onCustomEditorButtonClick,
  onBackToWPEditorClick,
  onFullScreenChange,
  min_height,
  max_height,
  toolbar1,
  toolbar2,
}: FormWPEditorProps) => {
  const { showModal } = useModal();
  const hasWpAdminAccess = tutorConfig.settings?.hide_admin_bar_for_users === 'off';
  const isAdmin = tutorConfig.current_user?.roles?.includes(TutorRoles.ADMINISTRATOR);
  const isInstructor = tutorConfig.current_user?.roles?.includes(TutorRoles.TUTOR_INSTRUCTOR);

  const [customEditorLoading, setCustomEditorLoading] = useState<string | null>(null);

  const filteredEditors = editors.filter(
    (editor) => isAdmin || (isInstructor && hasWpAdminAccess) || editor.name === 'droip',
  );

  const hasAvailableCustomEditors = hasCustomEditorSupport && filteredEditors.length > 0;
  const isOverlayVisible = hasAvailableCustomEditors && editorUsed.name !== 'classic';

  const handleAiButtonClick = () => {
    if (!isTutorPro) {
      showModal({
        component: ProIdentifierModal,
        props: {
          image: generateText,
          image2x: generateText2x,
        },
      });
    } else if (!hasOpenAiAPIKey) {
      showModal({
        component: SetupOpenAiModal,
        props: {
          image: generateText,
          image2x: generateText2x,
        },
      });
    } else {
      showModal({
        component: AITextModal,
        isMagicAi: true,
        props: {
          title: __('AI Studio', __TUTOR_TEXT_DOMAIN__),
          icon: <SVGIcon name="magicAiColorize" width={24} height={24} />,
          characters: 1000,
          field,
          fieldState,
          is_html: true,
        },
      });
      onClickAiButton?.();
    }
  };

  const customLabel = (
    <div css={styles.editorLabel}>
      <span css={styles.labelWithAi}>
        {label}
        <Show when={generateWithAi}>
          <button type="button" css={styles.aiButton} onClick={handleAiButtonClick}>
            <SVGIcon name="magicAiColorize" width={32} height={32} />
          </button>
        </Show>
      </span>
      <div css={styles.editorsButtonWrapper}>
        <span>{__('Edit with', __TUTOR_TEXT_DOMAIN__)}</span>
        <div css={styles.customEditorButtons}>
          <For each={filteredEditors}>
            {(editor) => (
              <Tooltip key={editor.name} content={editor.label} delay={200}>
                <button
                  type="button"
                  css={styles.customEditorButton}
                  disabled={customEditorLoading === editor.name}
                  onClick={async () => {
                    try {
                      setCustomEditorLoading(editor.name);
                      await onCustomEditorButtonClick?.(editor);
                      window.location.href = editor.link;
                    } finally {
                      setCustomEditorLoading(null);
                    }
                  }}
                >
                  <Show when={customEditorLoading === editor.name}>
                    <LoadingOverlay />
                  </Show>
                  <SVGIcon name={customEditorIcons[editor.name]} height={24} width={24} />
                </button>
              </Tooltip>
            )}
          </For>
        </div>
      </div>
    </div>
  );

  return (
    <FormFieldWrapper
      label={hasAvailableCustomEditors ? customLabel : label}
      field={field}
      fieldState={fieldState}
      disabled={disabled}
      readOnly={readOnly}
      placeholder={placeholder}
      helpText={helpText}
      isMagicAi={isMagicAi}
      generateWithAi={!hasAvailableCustomEditors && generateWithAi}
      onClickAiButton={handleAiButtonClick}
      replaceEntireLabel={hasAvailableCustomEditors}
    >
      {() => {
        if (loading) {
          return (
            <div css={styleUtils.flexCenter()}>
              <LoadingSpinner size={20} color={colorTokens.icon.default} />
            </div>
          );
        }

        return (
          <div css={styles.wrapper({ isOverlayVisible })}>
            <Show when={isOverlayVisible}>
              <CustomEditorOverlay
                editorUsed={editorUsed}
                onBackToWPEditorClick={onBackToWPEditorClick}
                onCustomEditorButtonClick={onCustomEditorButtonClick}
              />
            </Show>
            <WPEditor
              value={field.value ?? ''}
              onChange={(value) => {
                field.onChange(value);
                if (onChange) {
                  onChange(value);
                }
              }}
              isMinimal={isMinimal}
              hideMediaButtons={hideMediaButtons}
              hideQuickTags={hideQuickTags}
              autoFocus={autoFocus}
              onFullScreenChange={onFullScreenChange}
              readonly={readOnly}
              min_height={min_height}
              max_height={max_height}
              toolbar1={toolbar1}
              toolbar2={toolbar2}
            />
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormWPEditor;

const styles = {
  wrapper: ({ isOverlayVisible = false }) => css`
    position: relative;

    ${isOverlayVisible &&
    css`
      overflow: hidden;
      border-radius: ${borderRadius[6]};
    `}
  `,
  editorLabel: css`
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: space-between;
  `,
  aiButton: css`
    ${styleUtils.resetButton};
    ${styleUtils.flexCenter()};
    width: 32px;
    height: 32px;
    border-radius: ${borderRadius[4]};

    :disabled {
      cursor: not-allowed;
    }

    &:focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
    }
  `,
  labelWithAi: css`
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
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
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    border-radius: ${borderRadius.circle};

    &:focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 1px;
    }
  `,
  editorOverlay: css`
    position: absolute;
    height: 100%;
    width: 100%;
    ${styleUtils.flexCenter()};
    gap: ${spacing[8]};
    background-color: ${rgba(colorTokens.background.modal, 0.6)};
    border-radius: ${borderRadius[6]};
    z-index: ${zIndex.positive};
    backdrop-filter: blur(8px);
  `,
  editWithButton: css`
    background: ${colorTokens.action.secondary.default};
    color: ${colorTokens.text.primary};
    box-shadow:
      inset 0 -1px 0 0 ${rgba('#1112133D', 0.24)},
      0 1px 0 0 ${rgba('#1112133D', 0.8)};
  `,
};
