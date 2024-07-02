import SVGIcon from '@Atoms/SVGIcon';
import { modal } from '@Config/constants';
import { Breakpoint, borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import type React from 'react';
import { useEffect } from 'react';

interface BasicModalWrapperProps {
  children: React.ReactNode;
  onClose: () => void;
  icon?: React.ReactNode;
  title?: string;
  subtitle?: string;
  actions?: React.ReactNode;
  headerChildren?: React.ReactNode;
  entireHeader?: React.ReactNode;
}

const BasicModalWrapper = ({
  children,
  onClose,
  title,
  subtitle,
  icon,
  headerChildren,
  entireHeader,
  actions,
}: BasicModalWrapperProps) => {
  useEffect(() => {
    document.body.style.overflow = 'hidden';

    return () => {
      document.body.style.overflow = 'initial';
    };
  }, []);

  return (
    <div css={styles.container}>
      <div css={styles.header}>
        <div css={styles.headerContent}>
          <div css={styles.iconWithTitle}>
            <Show when={icon}>{icon}</Show>
            <Show when={title}>
              <p css={styles.title}>{title}</p>
            </Show>
          </div>
          <Show when={subtitle}>
            <span css={styles.subtitle}>{subtitle}</span>
          </Show>
        </div>
        <div css={styles.actionsWrapper}>
          <Show
            when={actions}
            fallback={
              <button type="button" css={styles.closeButton} onClick={onClose}>
                <SVGIcon name="timesThin" width={24} height={24} />
              </button>
            }
          >
            {actions}
          </Show>
        </div>
      </div>
      <div css={styles.content}>{children}</div>
    </div>
  );
};

export default BasicModalWrapper;

const styles = {
  container: css`
    position: relative;
    background: ${colorTokens.background.white};
    max-width: 1218px;
    box-shadow: ${shadow.modal};
    border-radius: ${borderRadius[10]};
    overflow: hidden;
    top: 50%;
    transform: translateY(-50%);

    ${Breakpoint.smallTablet} {
      width: 90%;
    }
  `,
  header: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    height: ${modal.BASIC_MODAL_HEADER_HEIGHT}px;
    background: ${colorTokens.background.white};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    padding-inline: ${spacing[16]};
  `,
  headerContent: css`
    place-self: center start;
    display: inline-flex;
    align-items: center;
    gap: ${spacing[12]};
  `,
  iconWithTitle: css`
    display: inline-flex;
    align-items: center;
    gap: ${spacing[4]};
    color: ${colorTokens.icon.default};
  `,
  title: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.title};
  `,
  subtitle: css`
    ${styleUtils.text.ellipsis(1)}
    ${typography.caption()};
    color: ${colorTokens.text.hints};
  `,
  actionsWrapper: css`
    place-self: center end;
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
    background-color: ${colorTokens.background.white};
    overflow-y: auto;
  `,
};
