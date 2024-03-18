import { colorTokens, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { type Step, useSidebar } from '@CourseBuilderContexts/SidebarContext';
import { styleUtils } from '@Utils/style-utils';
import type { IconCollection } from '@Utils/types';
import { css } from '@emotion/react';
import SVGIcon from './SVGIcon';

export type ProgressStatus = 'inactive' | 'active' | 'completed';

type ProgressStep = {
	step: Step;
	index: number;
	onClick: (step: string) => void;
};

const ProgressStep = ({ step, index, onClick }: ProgressStep) => {
	const { currentIndex } = useSidebar();
	const statusIcon = step.isActive ? 'active' : step.isCompleted ? 'completed' : 'inactive';
	const isActive = step.isActive || step.isCompleted || step.isVisited;

	return (
		<div css={styles.wrapper({ isActive: index < currentIndex })}>
			<div css={styles.icon({ isActive })}>
				<SVGIcon name={(statusIcon ?? 'inactive') as IconCollection} width={24} height={24} />
			</div>
			<button
				type="button"
				css={styles.button({ isActive })}
				onClick={() => onClick(step.path)}
				disabled={step.isDisabled}
			>
				{step.label}
			</button>
		</div>
	);
};

export default ProgressStep;

const styles = {
	wrapper: ({ isActive }: { isActive: boolean }) => css`
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
    position: relative;

    &:not(:last-child)::before {
      content: '';
      width: 1px;
      height: 40px;
      background-color: ${isActive ? colorTokens.brand.blue : colorTokens.color.black[10]};
      position: absolute;
      left: ${spacing[12]};
      top: ${spacing[20]};
    }
  `,
	icon: ({ isActive }: { isActive: boolean }) => css`
    display: flex;
    color: ${!isActive ? colorTokens.color.black[10] : colorTokens.design.brand};

    svg {
      z-index: ${zIndex.positive};
    }
  `,
	button: ({ isActive }: { isActive: boolean }) => css`
    ${styleUtils.resetButton};
    ${typography.caption('regular')};
    color: ${!isActive ? colorTokens.text.hints : colorTokens.text.primary};
    cursor: pointer;
  `,
};
