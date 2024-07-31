import AiButton from '@Atoms/AiButton';
import SVGIcon from '@Atoms/SVGIcon';
import { Separator } from '@Atoms/Separator';
import FormRangeSliderField from '@Components/fields/FormRangeSliderField';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import imageSrc from '@Images/mock-images/mock-image-2.jpg';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';
import { useMagicImageGeneration } from './ImageContext';
import { magicAIStyles } from './styles';

const MagicFill = () => {
  const form = useFormWithGlobalError<{ brush_size: number; prompt: string }>({
    defaultValues: {
      brush_size: 10,
      prompt: '',
    },
  });
  const { onDropdownMenuChange } = useMagicImageGeneration();

  return (
    <div css={magicAIStyles.wrapper}>
      <div css={magicAIStyles.left}>
        <div css={styles.leftWrapper}>
          <div css={styles.actionBar}>
            <div css={styles.backButtonWrapper}>
              <button type="button" css={styles.backButton} onClick={() => onDropdownMenuChange('generation')}>
                <SVGIcon name="arrowLeft" />
              </button>
              Magic Fill
            </div>
            <div css={styles.actions}>
              <AiButton variant="ghost">Revert to Original</AiButton>
              <Separator variant="vertical" css={css`min-height: 16px;`} />
              <div css={styles.undoRedo}>
                <AiButton variant="ghost" size="icon">
                  <SVGIcon name="undo" width={20} height={20} />
                </AiButton>
                <AiButton variant="ghost" size="icon">
                  <SVGIcon name="redo" width={20} height={20} />
                </AiButton>
              </div>
            </div>
          </div>
          <div css={styles.image}>
            <img src={imageSrc} alt="fill magic item" />
          </div>
          <div css={styles.footerActions}>
            <div css={styles.footerActionsLeft}>
              <AiButton variant="secondary">
                <SVGIcon name="imagePlus" width={24} height={24} />
              </AiButton>
              <AiButton variant="secondary">
                <SVGIcon name="reload" width={24} height={24} />
              </AiButton>
              <AiButton variant="secondary">
                <SVGIcon name="eraser" width={24} height={24} />
              </AiButton>
            </div>
            <div>
              <AiButton variant="secondary">
                <SVGIcon name="download" width={24} height={24} />
              </AiButton>
            </div>
          </div>
        </div>
      </div>
      <div css={magicAIStyles.right}>
        <div css={styles.fields}>
          <Controller
            control={form.control}
            name="brush_size"
            render={(props) => (
              <FormRangeSliderField {...props} label="Brush size" min={0} max={20} isMagicAi hasBorder />
            )}
          />
          <Controller
            control={form.control}
            name="prompt"
            render={(props) => (
              <FormTextareaInput
                {...props}
                label={__('Describe the fill', 'tutor')}
                placeholder={__('Write 5 words to describe...', 'tutor')}
                rows={4}
                isMagicAi
              />
            )}
          />
        </div>
        <div css={magicAIStyles.rightFooter}>
          <div css={styles.footerButtons}>
            <AiButton>
              <SVGIcon name="magicWand" width={24} height={24} />
              Generative erase
            </AiButton>
            <AiButton variant="primary_outline">Use Image</AiButton>
          </div>
          <div css={magicAIStyles.rightFooterInfo}>
            <div>
              <SVGIcon name="seeds" width={20} height={20} />
              {__('Use 1 of 50 icons', 'tutor')}
            </div>
            <a href="/">{__('Upgrade for more', 'tutor')}</a>
          </div>
        </div>
      </div>
    </div>
  );
};

export default MagicFill;
const styles = {
  actionBar: css`
		display: flex;
		align-items: center;
		justify-content: space-between;
	`,
  fields: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[12]};
	`,
  leftWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[8]};
		padding-block: ${spacing[16]};
	`,
  footerButtons: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[8]};
	`,
  footerActions: css`
		display: flex;
		justify-content: space-between;
	`,
  footerActionsLeft: css`
		display: flex;
		align-items: center;
		gap: ${spacing[12]};
	`,
  actions: css`
		display: flex;
		align-items: center;
		gap: ${spacing[16]};
	`,
  undoRedo: css`
		display: flex;
		align-items: center;
		gap: ${spacing[12]};
	`,
  backButtonWrapper: css`
		display: flex;
		align-items: center;
		gap: ${spacing[8]};
		${typography.body('medium')};
		color: ${colorTokens.text.title};
	`,
  backButton: css`
		${styleUtils.resetButton};
		width: 24px;
		height: 24px;
		border-radius: ${borderRadius[4]};
		border: 1px solid ${colorTokens.stroke.default};
		display: flex;
		align-items: center;
		justify-content: center;
	`,
  image: css`
		width: 492px;
		height: 498px;
		position: relative;

		img {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			object-fit: cover;
			ratio: 1/1;
		}
	`,
};
