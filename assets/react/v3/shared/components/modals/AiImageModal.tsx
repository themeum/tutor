import { MagicImageGenerationProvider, useMagicImageGeneration } from '@Components/magic-ai-image/ImageContext';
import { ImageGeneration } from '@Components/magic-ai-image/ImageGeneration';
import MagicFill from '@Components/magic-ai-image/MagicFill';
import { Breakpoint, borderRadius, colorTokens, fontWeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import BasicModalWrapper from './BasicModalWrapper';
import type { ModalProps } from './Modal';

interface AiImageModalProps extends ModalProps {}
export type StyleType =
  | 'none'
  | 'photo'
  | 'illustration'
  | '3d'
  | 'painting'
  | 'sketch'
  | 'black-and-white'
  | 'cartoon';

export interface GenerateAiImageFormFields {
  prompt: string;
  style: StyleType;
}

function RenderModalContent() {
  const { state } = useMagicImageGeneration();

  switch (state) {
    case 'generation':
      return <ImageGeneration />;
    case 'magic-fill':
      return <MagicFill />;
    default:
      return null;
  }
}

const AiImageModal = ({ title, icon, closeModal }: AiImageModalProps) => {
  return (
    <BasicModalWrapper onClose={closeModal} title={title} icon={icon}>
      <MagicImageGenerationProvider>
        <RenderModalContent />
      </MagicImageGenerationProvider>
    </BasicModalWrapper>
  );
};

export default AiImageModal;

const styles = {
  wrapper: css`
		width: 870px;
		display: grid;
		grid-template-columns: auto 330px;

		${Breakpoint.tablet} {
			width: 90%;
		}
	`,
  dropdownOptions: css`
		display: flex;
		flex-direction: column;
		padding-block: ${spacing[8]};
	`,
  dropdownItem: css`
		${typography.caption()};
		height: 40px;
		display: flex;
		gap: ${spacing[10]};
		align-items: center;
		transition: background-color 0.3s ease;
		color: ${colorTokens.text.title};
		padding-inline: ${spacing[8]};
		cursor: pointer;

		svg {
			color: ${colorTokens.icon.default};
		}

		&:hover {
			background-color: ${colorTokens.background.hover};
		}
	`,
  images: css`
		display: grid;
		grid-template-columns: repeat(2, 1fr);
		gap: ${spacing[24]};
	`,
  image: css`
		width: 234px;
		height: 234px;
		overflow: hidden;
		border-radius: ${borderRadius[12]};
		position: relative;
		border: 2px solid transparent;
		transition: border-radius 0.3s ease;

		[data-actions] {
			opacity: 0;
			transition: opacity 0.3s ease;
		}

		img {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		&:hover {
			border-color: ${colorTokens.stroke.brand};
			[data-actions] {
				opacity: 1;
			}
		}
	`,
  threeDots: css`
		position: absolute;
		top: ${spacing[8]};
		right: ${spacing[8]};
		border-radius: ${borderRadius[4]};
	`,
  useButton: css`
		position: absolute;
		left: 50%;
		bottom: ${spacing[12]};
		transform: translateX(-50%);

		button {
			display: inline-flex;
			align-items: center;
			gap: ${spacing[4]};	
		}
		
	`,
  fields: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[12]};
	`,
  promptWrapper: css`
		position: relative;
	`,
  inspireButton: css`
		${styleUtils.resetButton};	
		${typography.small()};
		position: absolute;
		height: 28px;
		bottom: ${spacing[12]};
		left: ${spacing[12]};
		border: 1px solid ${colorTokens.stroke.brand};
		border-radius: ${borderRadius[4]};
		display: flex;
		align-items: center;
		gap: ${spacing[4]};
		color: ${colorTokens.text.brand};
		padding-inline: ${spacing[12]};
		background-color: ${colorTokens.background.white};

		&:hover {
			background-color: ${colorTokens.background.brand};
			color: ${colorTokens.text.white};
		}
	`,

  left: css`
		display: flex;
		width: 540px;
		height: 540px;
		justify-content: center;
		align-items: center;
		background-color: #F7F7F7;
	`,
  right: css`
		padding: ${spacing[20]};
		display: flex;
		flex-direction: column;
		align-items: space-between;
	`,
  footer: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[8]};
		margin-top: auto;
	`,
  footerInfo: css`
		${typography.small()};
		display: flex;
		align-items: center;
		justify-content: center;

		& > a {
			color: ${colorTokens.text.brand};
			text-decoration: underline;
			padding-left: ${spacing[12]};
			font-weight: ${fontWeight.medium};
		}

		& > div {
			display: flex;
			align-items: center;
			gap: ${spacing[4]};
			color: ${colorTokens.text.title};
			padding-right: ${spacing[12]};
			border-right: 1px solid ${colorTokens.stroke.default};

			svg {
				color: ${colorTokens.icon.default};
			}
		}
	`,
};
