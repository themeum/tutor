import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { rgba } from 'polished';
import type React from 'react';
import { useState } from 'react';

import Button from '@Atoms/Button';
import LoadingSpinner from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import Tooltip from '@Atoms/Tooltip';
import WPEditor from '@Atoms/WPEditor';

import AITextModal from '@Components/modals/AITextModal';
import { useModal } from '@Components/modals/Modal';
import ProIdentifierModal from '@CourseBuilderComponents/modals/ProIdentifierModal';
import SetupOpenAiModal from '@CourseBuilderComponents/modals/SetupOpenAiModal';

import config, { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import For from '@Controls/For';
import Show from '@Controls/Show';
import type { Editor } from '@CourseBuilderServices/course';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import type { IconCollection } from '@Utils/types';
import { makeFirstCharacterUpperCase } from '@Utils/util';

import generateText2x from '@Images/pro-placeholders/generate-text-2x.webp';
import generateText from '@Images/pro-placeholders/generate-text.webp';

import { TutorRoles } from '../../config/constants';
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
  editors?: Editor[];
  editorUsed?: Editor;
  isMagicAi?: boolean;
  autoFocus?: boolean;
  onCustomEditorButtonClick?: (editor: Editor) => Promise<void>;
  onFullScreenChange?: (isFullScreen: boolean) => void;
  min_height?: number;
  max_height?: number;
}

interface CustomEditorOverlayProps {
  editorUsed: Editor;
  onCustomEditorButtonClick?: (editor: Editor) => Promise<void>;
}

const customEditorIcons: { [key: string]: IconCollection } = {
  droip: 'droipColorized',
  elementor: 'elementorColorized',
  gutenberg: 'gutenbergColorized',
};

const isTutorPro = !!tutorConfig.tutor_pro_url;
const hasOpenAiAPIKey = tutorConfig.settings?.chatgpt_key_exist;

const CustomEditorOverlay = ({ editorUsed, onCustomEditorButtonClick }: CustomEditorOverlayProps) => {
  const [isLoading, setIsLoading] = useState(false);

  return (
    <div css={styles.editorOverlay}>
      <Button
        variant="tertiary"
        size="small"
        buttonCss={styles.editWithButton}
        loading={isLoading}
        icon={
          customEditorIcons[editorUsed.name] && (
            <SVGIcon name={customEditorIcons[editorUsed.name]} height={24} width={24} />
          )
        }
        onClick={async () => {
          if (editorUsed) {
            try {
              setIsLoading(true);
              await onCustomEditorButtonClick?.(editorUsed);
              window.location.href = editorUsed.link;
            } catch (error) {
              console.error(error);
            } finally {
              setIsLoading(false);
            }
          }
        }}
      >
        {editorUsed?.label}
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
  editors = [],
  editorUsed = { name: 'classic', label: 'Classic Editor', link: '' },
  isMagicAi = false,
  autoFocus = false,
  onCustomEditorButtonClick,
  onFullScreenChange,
  min_height,
  max_height,
}: FormWPEditorProps) => {
  const { showModal } = useModal();
  const hasWpAdminAccess = tutorConfig.settings?.hide_admin_bar_for_users === 'off';
  const isAdmin = tutorConfig.current_user?.roles.includes(TutorRoles.ADMINISTRATOR);
  const isInstructor = tutorConfig.current_user?.roles.includes(TutorRoles.TUTOR_INSTRUCTOR);

  const filteredEditors = editors.filter(
    (editor) => isAdmin || (isInstructor && hasWpAdminAccess) || editor.name === 'droip',
  );

  const hasAvailableCustomEditors = hasCustomEditorSupport && filteredEditors.length > 0;

  const handleAiButtonClick = () => {
    if (!isTutorPro) {
      showModal({
        component: ProIdentifierModal,
        props: {
          title: sprintf(
            __('Upgrade to Tutor LMS Pro today and experience the power of %s', 'tutor'),
            <span css={styleUtils.aiGradientText}>{__('AI Studio', 'tutor')}</span>,
          ),
          featuresTitle: __('Don’t miss out on this game-changing feature!', 'tutor'),
          image: generateText,
          image2x: generateText2x,
          features: [
            __('Generate a complete course outline in seconds!', 'tutor'),
            __('Let the AI Studio create Quizzes on your behalf and give your brain a well-deserved break.', 'tutor'),
            __('Generate images, customize backgrounds, and even remove unwanted objects with ease.', 'tutor'),
            __('Say goodbye to typos and grammar errors with AI-powered copy editing.', 'tutor'),
          ],
          footer: (
            <Button
              onClick={() => window.open(config.TUTOR_PRICING_PAGE, '_blank', 'noopener')}
              icon={<SVGIcon name="crown" width={24} height={24} />}
            >
              {__('Get Tutor LMS Pro', 'tutor')}
            </Button>
          ),
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
          title: __('AI Studio', 'tutor'),
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
      <Show when={filteredEditors.length && editorUsed.name === 'classic'}>
        <div css={styles.editorsButtonWrapper}>
          <span>{__('Edit with', 'tutor')}</span>
          <div css={styles.customEditorButtons}>
            <For each={filteredEditors}>
              {(editor) => (
                <Tooltip key={editor.name} content={makeFirstCharacterUpperCase(editor.name)} delay={200}>
                  <button
                    type="button"
                    css={styles.customEditorButton}
                    onClick={async () => {
                      try {
                        await onCustomEditorButtonClick?.(editor);
                        window.location.href = editor.link;
                      } catch (error) {
                        console.error(error);
                      }
                    }}
                  >
                    <SVGIcon name={customEditorIcons[editor.name]} height={24} width={24} />
                  </button>
                </Tooltip>
              )}
            </For>
          </div>
        </div>
      </Show>
    </div>
  );

  const editor = (
    <WPEditor
      value={field.value ?? ''}
      onChange={(value) => {
        field.onChange(value);
        if (onChange) {
          onChange(value);
        }
      }}
      isMinimal={isMinimal}
      autoFocus={autoFocus}
      onFullScreenChange={onFullScreenChange}
      readonly={readOnly}
      min_height={min_height}
      max_height={max_height}
    />
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
          <Show when={hasCustomEditorSupport} fallback={editor}>
            <Show
              when={editorUsed.name === 'classic'}
              fallback={
                <CustomEditorOverlay editorUsed={editorUsed} onCustomEditorButtonClick={onCustomEditorButtonClick} />
              }
            >
              {editor}
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
  aiButton: css`
    ${styleUtils.resetButton};
    ${styleUtils.flexCenter()};
    width: 32px;
    height: 32px;
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
  `,
  editorOverlay: css`
    height: 300px;
    ${styleUtils.flexCenter()};
    background-color: ${rgba(colorTokens.background.modal, 0.6)};
    border-radius: ${borderRadius.card};
  `,
  editWithButton: css`
    background: ${colorTokens.action.secondary};
    color: ${colorTokens.text.primary};
    box-shadow: inset 0 -1px 0 0 ${rgba('#1112133D', 0.24)}, 0 1px 0 0 ${rgba('#1112133D', 0.8)};
  `,
};
