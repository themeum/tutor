import SVGIcon from '@Atoms/SVGIcon';
import { Breakpoint, borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';
import { type SerializedStyles, css } from '@emotion/react';
import type React from 'react';
import { useEffect } from 'react';

interface TaxModalWrapperProps {
  children: React.ReactNode;
  onClose: () => void;
  title?: string | React.ReactNode;
  modalStyle?: SerializedStyles;
  stickyFooter?: React.ReactNode;
}

const TaxModalWrapper = ({ children, onClose, title, modalStyle, stickyFooter }: TaxModalWrapperProps) => {
  useEffect(() => {
    document.body.style.overflow = 'hidden';

    return () => {
      document.body.style.overflow = 'initial';
    };
  }, []);

  return (
    <div css={styles.wrapper}>
      <div css={[styles.container, modalStyle]}>
        <div css={styles.header}>
          {title && <h6 css={typography.heading6('medium')}>{title}</h6>}
          <button type="button" css={styles.closeButton} onClick={onClose}>
            <SVGIcon name="times" />
          </button>
        </div>
        <div css={styles.content}>{children}</div>
        {stickyFooter && <div css={styles.stickyFooter}>{stickyFooter}</div>}
      </div>
    </div>
  );
};

export default TaxModalWrapper;

const styles = {
  wrapper: css`
    ${styleUtils.flexCenter()};
    width: 100%;
    height: 100%;
  `,
  container: css`
    background: ${colorTokens.background.white};
    margin: ${spacing[24]};
    max-width: 1236px;
    box-shadow: ${shadow.modal};
    border-radius: ${borderRadius[10]};
    overflow: hidden;
		max-height: 90vh;

    ${Breakpoint.smallTablet} {
      width: 90%;
    }
  `,
  header: css`
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: ${spacing[12]} ${spacing[20]};
    width: 100%;
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  closeButton: css`
    ${styleUtils.resetButton};
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: ${borderRadius.circle};
    background-color: ${colorTokens.background.white};

    & > span {
      display: inline;
    }

    svg {
      color: ${colorTokens.icon.default};
      transition: color 0.3s ease-in-out;
    }

    :hover {
      svg {
        color: ${colorTokens.icon.hover};
      }
    }
  `,
  content: css`
    overflow: hidden;
    overflow-y: auto;
    height: 100%;
  `,
  stickyFooter: css`
    box-shadow: ${shadow.dividerTop};
  `,
};
