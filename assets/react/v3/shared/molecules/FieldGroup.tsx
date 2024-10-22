import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { styleUtils } from '@Utils/style-utils';
import { type SerializedStyles, css } from '@emotion/react';
import { Children, type ReactNode } from 'react';

interface FieldGroupProps {
  children: ReactNode;
  fullWidth?: boolean;
  onRemove: () => void;
}

const FieldGroup = ({ children, fullWidth, onRemove }: FieldGroupProps) => {
  const totalChildren = Children.count(children);

  return (
    <div css={styles.wrapper(fullWidth)}>
      {Children.map(children, (child, index) => {
        return <div css={styles.fieldItemWrapper({ isLastChild: totalChildren === index + 1 })}>{child}</div>;
      })}

      <button type="button" data-clear css={styles.closeButton} onClick={onRemove}>
        <SVGIcon name="times" width={12} height={12} />
      </button>
    </div>
  );
};

interface FieldGroupItemProps {
  children: ReactNode;
  itemStyles?: SerializedStyles;
}

const FieldGroupItem = ({ children, itemStyles }: FieldGroupItemProps) => {
  return <div css={itemStyles}>{children}</div>;
};

FieldGroup.Item = FieldGroupItem;
export default FieldGroup;

const styles = {
  wrapper: (fullWidth = false) => css`
    display: grid;
    grid-template-columns: auto 80px auto;
    position: relative;

    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.border.neutral};
    border-radius: ${borderRadius[5]};
    box-shadow: ${shadow.input};

    width: fit-content;

    ${
      fullWidth &&
      css`
      width: 100%;
    `
    }

    &,
    input {
      transition: background-color 0.3s ease-in-out;
    }

    :hover {
      background-color: ${colorTokens.background.hover};

      input {
        background-color: ${colorTokens.background.hover};
      }

      > [data-clear] {
        visibility: visible;
        opacity: 1;
      }
    }
  `,

  closeButton: css`
    ${styleUtils.resetButton};
    display: grid;
    place-items: center;
    position: absolute;
    right: ${spacing[12]};
    top: 50%;
    transform: translateY(-50%);
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;

    svg {
      color: ${colorTokens.icon.default};
    }
  `,
  fieldItemWrapper: ({ isLastChild }: { isLastChild: boolean }) => css`
    border-left: 1px solid ${colorTokens.border.neutral};
    padding: 0 ${spacing[2]};
    ${styleUtils.flexCenter()};

    :first-of-type {
      border-left: none;
    }

    ${
      isLastChild &&
      css`
      padding: 0 ${spacing[40]} 0 ${spacing[12]};
    `
    }
  `,
};
