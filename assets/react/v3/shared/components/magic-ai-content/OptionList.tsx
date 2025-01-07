import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import { styleUtils } from '@TutorShared/utils/style-utils';
import type { Option } from '@TutorShared/utils/types';
import { css } from '@emotion/react';

export const OptionList = <T,>({ options, onChange }: { options: Option<T>[]; onChange: (value: T) => void }) => {
  return (
    <div css={styles.wrapper}>
      <For each={options}>
        {(option, index) => {
          return (
            <button type="button" key={index} onClick={() => onChange(option.value)} css={styles.item}>
              {option.label}
            </button>
          );
        }}
      </For>
    </div>
  );
};

const styles = {
  wrapper: css`
    display: flex;
    flex-direction: column;
    padding-block: ${spacing[8]};
    max-height: 400px;
    overflow-y: auto;
  `,
  item: css`
    ${styleUtils.resetButton};
    ${typography.caption()};
    width: 100%;
    padding: ${spacing[4]} ${spacing[16]};
    color: ${colorTokens.text.subdued};
    display: flex;
    align-items: center;

    &:hover {
      background-color: ${colorTokens.background.hover};
      color: ${colorTokens.text.title};
    }
  `,
};
