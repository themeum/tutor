import { css } from '@emotion/react';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';

export const fieldStyles = {
  fieldRow: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[24]};

    &:not(:last-of-type) {
      border-bottom: 1px solid ${colorTokens.stroke.divider};
      padding-bottom: ${spacing[16]};
    }

    @media (max-width: 991px) {
      flex-direction: column;
    }
  `,

  labelColumn: css``,

  labelContainer: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
  `,

  label: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.title};
    margin: 0;
  `,

  labelTitle: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;
  `,

  description: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;
    margin-top: ${spacing[4]};

    p {
      margin: 0;
    }

    a {
      color: ${colorTokens.text.brand};
      text-decoration: none;

      &:hover {
        text-decoration: underline;
      }
    }
  `,

  inputColumn: css``,

  inputContainer: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
};
