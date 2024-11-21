import { Breakpoint, spacing, zIndex } from '@Config/styles';
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
		padding-top: 80px;
	`,
};
