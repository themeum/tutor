import { useState } from 'react';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import SVGIcon from '@Atoms/SVGIcon';

import { typography } from '@Config/typography';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { styleUtils } from '@Utils/style-utils';

const TrueFalse = () => {
  const [selectedAnswer, setSelectedAnswer] = useState<boolean | null>(null);

  return (
    <div css={styles.optionWrapper}>
      <div css={styles.option({ isSelected: selectedAnswer === true })}>
        <div
          onClick={() => {
            setSelectedAnswer(true);
          }}
          onKeyDown={(event) => {
            if (event.key === 'Enter') {
              setSelectedAnswer(true);
            }
          }}
        >
          <SVGIcon data-check-icon name={selectedAnswer === true ? 'checkFilled' : 'check'} height={32} width={32} />
        </div>
        <div
          css={styles.optionLabel({ isSelected: selectedAnswer === true })}
          onClick={() => {
            setSelectedAnswer(true);
          }}
          onKeyDown={(event) => {
            if (event.key === 'Enter') {
              setSelectedAnswer(true);
            }
          }}
        >
          {__('True', 'tutor')}
        </div>
      </div>

      <div css={styles.option({ isSelected: selectedAnswer === false })}>
        <div
          onClick={() => {
            setSelectedAnswer(false);
          }}
          onKeyDown={(event) => {
            if (event.key === 'Enter') {
              setSelectedAnswer(false);
            }
          }}
        >
          <SVGIcon data-check-icon name={selectedAnswer === false ? 'checkFilled' : 'check'} height={32} width={32} />
        </div>
        <div
          css={styles.optionLabel({ isSelected: selectedAnswer === false })}
          onClick={() => {
            setSelectedAnswer(false);
          }}
          onKeyDown={(event) => {
            if (event.key === 'Enter') {
              setSelectedAnswer(false);
            }
          }}
        >
          {__('False', 'tutor')}
        </div>
      </div>
    </div>
  );
};

export default TrueFalse;

const styles = {
  optionWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
  `,
  option: ({
    isSelected,
  }: {
    isSelected: boolean;
  }) => css`
    ${styleUtils.display.flex()};
    ${typography.caption('medium')};
    align-items: center;
    color: ${colorTokens.text.subdued};
    gap: ${spacing[10]};
    height: 48px;
    align-items: center;

    [data-check-icon] {
      opacity: 0;
      transition: opacity 0.15s ease-in-out;
      fill: none;
    }

    &:hover {
      [data-check-icon] {
        opacity: 1;
      }
    }


    ${
      isSelected &&
      css`
        [data-check-icon] {
          opacity: 1;
          fill: ${colorTokens.bg.success};
        }
      `
    }
  `,
  optionLabel: ({
    isSelected,
  }: {
    isSelected: boolean;
  }) => css`
    width: 100%;
    border-radius: ${borderRadius.card};
    padding: ${spacing[12]} ${spacing[16]};
    transition: box-shadow 0.15s ease-in-out;
    background-color: ${colorTokens.background.white};
    cursor: pointer;

    &:hover {
      box-shadow: 0 0 0 1px ${colorTokens.stroke.hover};
    }

    ${
      isSelected &&
      css`
        background-color: ${colorTokens.background.success.fill40};
        color: ${colorTokens.text.primary};

        &:hover {
          box-shadow: 0 0 0 1px ${colorTokens.stroke.success.fill70};
        }
      `
    }
  `,
};
