import { CourseProgressSteps, Option } from '@Utils/types';
import SVGIcon from './SVGIcon';
import { css } from '@emotion/react';
import { colorPalateTutor, spacing } from '@Config/styles';
import { styleUtils } from '@Utils/style-utils';
import { typography } from '@Config/typography';

export type ProgressStatus = 'inactive' | 'active' | 'completed';

type ProgressStep = {
  step: Option<string>;
  status: ProgressStatus;
  onClick: (step: string) => void;
};

const ProgressStep = ({ step, status, onClick }: ProgressStep) => {
  return (
    <div css={styles.wrapper(status)}>
      <div css={styles.icon(status)}>
        <SVGIcon name={status} width={24} height={24} />
      </div>
      <button css={styles.button(status)} onClick={() => onClick(step.value)}>
        {step.label}
      </button>
    </div>
  );
};

export default ProgressStep;

const styles = {
  wrapper: (status: string) => css`
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
    position: relative;

    &:not(:last-child)::before {
      content: '';
      width: 1px;
      height: 40px;
      background-color: ${status === 'completed' ? colorPalateTutor.brand.blue : colorPalateTutor.color.black[10]};
      position: absolute;
      left: ${spacing[12]};
      top: ${spacing[20]};

      html[dir='rtl'] & {
        left: auto;
        right: ${spacing[12]};
      }
    }
  `,
  icon: (status: string) => css`
    display: flex;
    color: ${status === 'inactive' ? colorPalateTutor.color.black[10] : colorPalateTutor.design.brand};

    svg {
      z-index: 1;
    }
  `,
  button: (status: string) => css`
    ${styleUtils.resetButton};
    ${typography.caption('regular')};
    color: ${status === 'inactive' ? colorPalateTutor.text.hints : colorPalateTutor.text.primary};
    cursor: pointer;
  `,
};
