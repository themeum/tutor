import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import { styleUtils } from '@Utils/style-utils';
import type { Option } from '@Utils/types';
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
