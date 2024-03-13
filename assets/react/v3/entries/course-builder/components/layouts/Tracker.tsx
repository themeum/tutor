import { borderRadius, colorTokens, lineHeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { useCourseNavigator } from '@CourseBuilderContexts/CourseNavigatorContext';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { useNavigate } from 'react-router-dom';

const Tracker = () => {
  const { steps } = useCourseNavigator();
  const navigate = useNavigate();
  return (
    <div css={styles.wrapper}>
      <For each={steps}>
        {step => (
          <button
            type="button"
            key={step.id}
            css={styles.element({ isActive: step.isActive, isCompleted: step.isCompleted })}
            onClick={() => {
              navigate(step.path);
            }}
          >
            <span data-element-id>{step.indicator}</span>
            <span>{step.label}</span>
            <Show when={step.indicator < 3}>
              <span data-element-indicator />
            </Show>
          </button>
        )}
      </For>
    </div>
  );
};

export default Tracker;

const styles = {
  wrapper: css`
    display: flex;
    align-items: center;
  `,
  element: ({ isActive = false, isCompleted = false }) => css`
    ${styleUtils.resetButton};
    ${styleUtils.display.flex()};
    ${typography.small()};
    padding: ${spacing[4]} ${spacing[0]} ${spacing[4]} ${spacing[8]};
    gap: ${spacing[8]};
    align-items: center;

    ${(isActive || isCompleted) &&
    css`
      color: ${colorTokens.text.primary};
    `}

    [data-element-id] {
      ${styleUtils.display.flex()};
      ${typography.small('bold')};
      line-height: ${lineHeight[20]};
      width: 24px;
      height: 24px;
      border-radius: ${borderRadius.circle};
      justify-content: center;
      align-items: center;
      border: 1px solid ${colorTokens.color.black[10]};
      color: ${colorTokens.text.hints};

      ${isActive &&
      css`
        border-color: ${colorTokens.stroke.brand};
        color: ${colorTokens.text.brand};
      `}

      ${isCompleted &&
      !isActive &&
      css`
        border-color: ${colorTokens.stroke.brand};
        background-color: ${colorTokens.design.brand};
        color: ${colorTokens.text.white};
      `}
    }

    [data-element-indicator] {
      width: 16px;
      height: 2px;
      border-radius: ${spacing[6]};
      background-color: ${colorTokens.stroke.default};
      margin-inline: 4px;
    }
  `,
};
