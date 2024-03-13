import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, Breakpoint, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { css } from '@emotion/react';
import { styleUtils } from '@Utils/style-utils';
import React, { useEffect } from 'react';

interface ModalWrapperProps {
  children: React.ReactNode;
  onClose: () => void;
  icon?: React.ReactNode;
  title?: string;
  subtitle?: string;
  actions?: React.ReactNode;
  entireHeader?: React.ReactNode;
}

const ModalWrapper = ({ children, onClose, title, subtitle, icon, entireHeader, actions }: ModalWrapperProps) => {
  useEffect(() => {
    document.body.style.overflow = 'hidden';

    return () => {
      document.body.style.overflow = 'initial';
    };
  }, []);

  return (
    <div css={styles.container}>
      <div css={styles.header}>
        <Show
          when={entireHeader}
          fallback={
            <>
              <div css={styles.headerContent}>
                <div css={styles.iconWithTitle}>
                  {icon && icon}
                  {title && <h6 css={styles.title}>{title}</h6>}
                </div>
                {subtitle && <span css={styles.subtitle}>{subtitle}</span>}
              </div>
              <div css={styles.actionsWrapper}>
                <Show
                  when={actions}
                  fallback={
                    <button type='button' css={styles.closeButton} onClick={onClose}>
                      <SVGIcon name='times' width={14} height={14} />
                    </button>
                  }
                >
                  {actions}
                </Show>
              </div>
            </>
          }
        >
          {entireHeader}
        </Show>
      </div>
      <div css={styles.content}>{children}</div>
    </div>
  );
};

export default ModalWrapper;

const styles = {
  container: css`
    position: relative;
    background: ${colorTokens.background.white};
    margin: ${spacing[24]};
    margin-top: 88px;
    height: 100%;
    max-width: 1218px;
    box-shadow: ${shadow.modal};
    border-radius: ${borderRadius[10]};
    overflow: hidden;
    bottom: 0;

    ${Breakpoint.smallTablet} {
      width: 90%;
    }
  `,
  header: css`
    display: inline-flex;
    justify-content: space-between;
    align-items: center;
    padding: ${spacing[20]};
    width: 100%;
    height: 56px;
    background: ${colorTokens.background.white};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    position: sticky;
  `,
  headerContent: css`
    display: inline-flex;
    align-items: center;
    gap: ${spacing[12]};

    & span {
      ::before {
        content: '';
        border-left: 1px solid ${colorTokens.icon.hints};
        margin-right: ${spacing[12]};
        border-radius: ${borderRadius[14]};
      }
    }
  `,
  iconWithTitle: css`
    display: inline-flex;
    align-items: center;
    gap: ${spacing[4]};
    color: ${colorTokens.icon.default};
  `,
  title: css`
    ${typography.heading6('medium')};
    color: ${colorTokens.text.title};
  `,
  subtitle: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
  `,
  actionsWrapper: css`
    display: inline-flex;
    gap: ${spacing[16]};
  `,
  closeButton: css`
    ${styleUtils.resetButton};
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: ${borderRadius.circle};
    background: ${colorTokens.background.white};

    svg {
      color: ${colorTokens.icon.default};
      transition: color 0.3s ease-in-out;
    }

    :hover {
      svg {
        color: ${colorTokens.icon.hover};
      }
    }

    :focus {
      box-shadow: ${shadow.focus};
    }
  `,
  content: css`
    height: 100%;
    overflow: auto;
  `,
};
