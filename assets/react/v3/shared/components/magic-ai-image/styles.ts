import { Breakpoint, colorTokens, fontWeight, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';

export const magicAIStyles = {
  wrapper: css`
		min-width: 1000px;
		display: grid;
		grid-template-columns: 1fr 330px;

		${Breakpoint.tablet} {
			width: 90%;
		}
	`,
  left: css`
		display: flex;
		justify-content: center;
		align-items: center;
		background-color: #F7F7F7;
		z-index: ${zIndex.level};
	`,
  right: css`
		padding: ${spacing[20]};
		display: flex;
		flex-direction: column;
		align-items: space-between;
		z-index: ${zIndex.positive};
	`,
  rightFooter: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[8]};
		margin-top: auto;
	`,
  rightFooterInfo: css`
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
