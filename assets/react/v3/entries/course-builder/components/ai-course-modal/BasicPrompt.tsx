import MagicButton from '@Atoms/MagicButton';
import SVGIcon from '@Atoms/SVGIcon';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { modal } from '@Config/constants';
import { Breakpoint, borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';
import { useGenerateCourseContent } from '../../hooks/useGenerateCourseContent';
import { useContentGenerationContext } from './ContentGenerationContext';

interface BasicPromptProps {
  onClose: () => void;
}

const BasicPrompt = ({ onClose }: BasicPromptProps) => {
  const form = useFormWithGlobalError<{ prompt: string }>({
    defaultValues: {
      prompt: '',
    },
  });

  const { setCurrentStep } = useContentGenerationContext();
  const { startGeneration } = useGenerateCourseContent();

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    form.setFocus('prompt');
  }, []);

  return (
    <form
      css={styles.container}
      onSubmit={form.handleSubmit(async (values) => {
        setCurrentStep('generation');
        startGeneration(values.prompt);
      })}
    >
      <div css={styles.header}>
        <div css={styles.headerContent}>
          <div css={styles.iconWithTitle}>
            <SVGIcon name="magicAiColorize" width={24} height={24} />
            <p css={styles.title}>{__('AI Studio', 'tutor')}</p>
          </div>
        </div>
        <div css={styles.actionsWrapper}>
          <button type="button" css={styles.closeButton} onClick={onClose}>
            <SVGIcon name="timesThin" width={24} height={24} />
          </button>
        </div>
      </div>
      <div css={styles.content}>
        <Controller
          control={form.control}
          name="prompt"
          render={(props) => (
            <FormTextareaInput
              {...props}
              placeholder={__('Enter a brief overview of your course topics and structure', 'tutor')}
              isMagicAi
            />
          )}
        />
      </div>
      <div css={styles.footer}>
        <MagicButton type="submit" disabled={form.watch('prompt') === ''}>
          <SVGIcon name="magicAi" width={24} height={24} />
          {__('Generate Now', 'tutor')}
        </MagicButton>
      </div>
    </form>
  );
};

export default BasicPrompt;
const styles = {
  container: css`
		position: absolute;
		background: ${colorTokens.background.white};
		max-width: 1218px;
		box-shadow: ${shadow.modal};
		border-radius: ${borderRadius[10]};
		overflow: hidden;
		top: 50%;
		left: 50%;
		translate: -50% -50%;
		
		${Breakpoint.smallTablet} {
			width: 90%;
		}
	`,
  header: css`
		display: flex;
		align-items: center;
		justify-content: space-between;
		width: 100%;
		height: ${modal.BASIC_MODAL_HEADER_HEIGHT}px;
		background: ${colorTokens.background.white};
		border-bottom: 1px solid ${colorTokens.stroke.divider};
		padding-inline: ${spacing[16]};
	`,
  headerContent: css`
		place-self: center start;
		display: inline-flex;
		align-items: center;
		gap: ${spacing[12]};
	`,
  iconWithTitle: css`
		display: inline-flex;
		align-items: center;
		gap: ${spacing[4]};
		color: ${colorTokens.icon.default};
	`,
  title: css`
		${typography.body('medium')};
		color: ${colorTokens.text.title};
    text-transform: capitalize;
	`,
  subtitle: css`
		${styleUtils.text.ellipsis(1)}
		${typography.caption()};
		color: ${colorTokens.text.hints};
	`,
  actionsWrapper: css`
		place-self: center end;
		display: inline-flex;
		gap: ${spacing[16]};
	`,
  closeButton: css`
		${styleUtils.resetButton};
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 32px;
		height: 32px;
		border-radius: ${borderRadius.circle};
		background: ${colorTokens.background.white};

		svg {
			color: ${colorTokens.icon.default};
			transition: color 0.3s ease-in-out;
		}

		:hover {
			svg {
				color: ${colorTokens.icon.hover};
			}
		}

		:focus {
			box-shadow: ${shadow.focus};
		}
	`,
  content: css`
		background-color: ${colorTokens.background.white};
		overflow-y: auto;
		width: 560px;
		padding: ${spacing[12]} ${spacing[20]} ${spacing[4]} ${spacing[20]};
	`,
  footer: css`
		padding: ${spacing[8]} ${spacing[20]} ${spacing[12]} ${spacing[20]};
	`,
};
