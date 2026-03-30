import { type SerializedStyles, css } from '@emotion/react';
import type React from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import ErrorBoundary from '@TutorShared/components/ErrorBoundary';
import FocusTrap from '@TutorShared/components/FocusTrap';

import { modal } from '@TutorShared/config/constants';
import { Breakpoint, borderRadius, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { useScrollLock } from '@TutorShared/hooks/useScrollLock';
import { styleUtils } from '@TutorShared/utils/style-utils';

interface BasicModalWrapperProps {
  children: React.ReactNode;
  onClose: () => void;
  icon?: React.ReactNode;
  title?: string | React.ReactNode;
  subtitle?: string;
  actions?: React.ReactNode;
  entireHeader?: React.ReactNode;
  fullScreen?: boolean;
  modalStyle?: SerializedStyles;
  maxWidth?: number;
  isCloseAble?: boolean;
  blurTriggerElement?: boolean;
}

const BasicModalWrapper = ({
  children,
  onClose,
  title,
  subtitle,
  icon,
  entireHeader,
  actions,
  fullScreen,
  modalStyle,
  maxWidth = modal.BASIC_MODAL_MAX_WIDTH,
  isCloseAble = true,
  blurTriggerElement = true,
}: BasicModalWrapperProps) => {
  useScrollLock();

  return (
    <FocusTrap blurPrevious={blurTriggerElement}>
      <div
        css={[styles.container({ isFullScreen: fullScreen }), modalStyle]}
        style={{
          maxWidth: `${maxWidth}px`,
        }}
      >
        <div
          css={styles.header({
            hasEntireHeader: !!entireHeader,
          })}
        >
          <Show when={!entireHeader} fallback={entireHeader}>
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
          </Show>

          <div
            css={styles.actionsWrapper({
              hasEntireHeader: !!entireHeader,
            })}
          >
            <Show
              when={actions}
              fallback={
                <Show when={isCloseAble}>
                  <button data-cy="close-modal" type="button" css={styles.closeButton} onClick={onClose}>
                    <SVGIcon name="timesThin" width={24} height={24} />
                  </button>
                </Show>
              }
            >
              {actions}
            </Show>
          </div>
        </div>
        <div css={styles.content({ isFullScreen: fullScreen })}>
          <ErrorBoundary>{children}</ErrorBoundary>
        </div>
      </div>
    </FocusTrap>
  );
};

export default BasicModalWrapper;

const styles = {
  container: ({ isFullScreen }: { isFullScreen?: boolean }) => css`
    position: relative;
    background: ${colorTokens.background.white};
    box-shadow: ${shadow.modal};
    border-radius: ${borderRadius[10]};
    overflow: hidden;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    ${isFullScreen &&
    css`
      max-width: 100vw;
      width: 100vw;
      height: 95vh;
    `}

    ${Breakpoint.smallTablet} {
      width: 90%;
    }
  `,
  header: ({ hasEntireHeader }: { hasEntireHeader?: boolean }) => css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    height: ${!hasEntireHeader ? `${modal.BASIC_MODAL_HEADER_HEIGHT}px` : 'auto'};
    background: ${colorTokens.background.white};
    border-bottom: ${!hasEntireHeader ? `1px solid ${colorTokens.stroke.divider}` : 'none'};
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
  actionsWrapper: ({ hasEntireHeader }: { hasEntireHeader: boolean }) => css`
    place-self: center end;
    display: inline-flex;
    gap: ${spacing[16]};

    ${hasEntireHeader &&
    css`
      position: absolute;
      right: ${spacing[16]};
      top: ${spacing[16]};
    `}
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

    &:focus,
    &:active,
    &:hover {
      background: ${colorTokens.background.white};
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

    :focus {
      box-shadow: ${shadow.focus};
    }
  `,
  content: ({ isFullScreen }: { isFullScreen?: boolean }) => css`
    background-color: ${colorTokens.background.white};
    overflow-y: auto;
    max-height: 90vh;

    ${isFullScreen &&
    css`
      height: calc(100% - ${modal.BASIC_MODAL_HEADER_HEIGHT}px);
    `}
  `,
};
