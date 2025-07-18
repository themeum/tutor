import { css } from '@emotion/react';
import type React from 'react';
import { useEffect } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import ErrorBoundary from '@TutorShared/components/ErrorBoundary';
import FocusTrap from '@TutorShared/components/FocusTrap';

import { modal } from '@TutorShared/config/constants';
import { Breakpoint, borderRadius, colorTokens, shadow, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';

interface ModalWrapperProps {
  children: React.ReactNode;
  onClose: () => void;
  icon?: React.ReactNode;
  title?: string | React.ReactNode;
  subtitle?: string;
  actions?: React.ReactNode;
  headerChildren?: React.ReactNode;
  entireHeader?: React.ReactNode;
  maxWidth?: number;
  blurTriggerElement?: boolean;
}

const ModalWrapper = ({
  children,
  onClose,
  title,
  subtitle,
  icon,
  headerChildren,
  entireHeader,
  actions,
  maxWidth = 1218,
  blurTriggerElement = true,
}: ModalWrapperProps) => {
  useEffect(() => {
    document.body.style.overflow = 'hidden';

    return () => {
      document.body.style.overflow = 'initial';
    };
  }, []);

  return (
    <FocusTrap blurPrevious={blurTriggerElement}>
      <div
        css={styles.container({
          maxWidth,
        })}
      >
        <div
          css={styles.header({
            hasHeaderChildren: !!headerChildren,
          })}
        >
          <Show
            when={entireHeader}
            fallback={
              <>
                <div css={styles.headerContent}>
                  <div css={styles.iconWithTitle}>
                    <Show when={icon}>{icon}</Show>
                    <Show when={title}>
                      <h6 css={styles.title} title={typeof title === 'string' ? title : ''}>
                        {title}
                      </h6>
                    </Show>
                  </div>
                  <Show when={subtitle}>
                    <span css={styles.subtitle}>{subtitle}</span>
                  </Show>
                </div>
                <div css={styles.headerChildren}>
                  <Show when={headerChildren}>{headerChildren}</Show>
                </div>
                <div css={styles.actionsWrapper}>
                  <Show
                    when={actions}
                    fallback={
                      <button type="button" css={styles.closeButton} onClick={onClose}>
                        <SVGIcon name="times" width={14} height={14} />
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
        <div css={styles.content}>
          <ErrorBoundary>{children}</ErrorBoundary>
        </div>
      </div>
    </FocusTrap>
  );
};

export default ModalWrapper;

const styles = {
  container: ({ maxWidth }: { maxWidth?: number }) => css`
    position: relative;
    background: ${colorTokens.background.white};
    margin: ${modal.MARGIN_TOP}px auto ${spacing[24]};
    height: 100%;
    max-width: ${maxWidth}px;
    box-shadow: ${shadow.modal};
    border-radius: ${borderRadius[10]};
    overflow: hidden;
    bottom: 0;
    z-index: ${zIndex.modal};
    width: 100%;

    ${Breakpoint.smallTablet} {
      width: 90%;
    }
  `,
  header: ({ hasHeaderChildren }: { hasHeaderChildren: boolean }) => css`
    display: grid;
    grid-template-columns: ${hasHeaderChildren ? '1fr auto 1fr' : '1fr auto auto'};
    gap: ${spacing[8]};
    align-items: center;
    width: 100%;
    height: ${modal.HEADER_HEIGHT}px;
    background: ${colorTokens.background.white};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    position: sticky;
  `,
  headerContent: css`
    place-self: center start;
    display: inline-flex;
    align-items: center;
    gap: ${spacing[12]};
    padding-left: ${spacing[24]};

    ${Breakpoint.smallMobile} {
      padding-left: ${spacing[16]};
    }
  `,
  headerChildren: css`
    place-self: center center;
  `,
  iconWithTitle: css`
    display: inline-flex;
    align-items: center;
    gap: ${spacing[4]};
    flex-shrink: 0;
    color: ${colorTokens.icon.default};
  `,
  title: css`
    ${typography.heading6('medium')};
    color: ${colorTokens.text.title};
    text-transform: none;
    letter-spacing: normal;
  `,
  subtitle: css`
    ${styleUtils.text.ellipsis(1)}
    ${typography.caption()};
    color: ${colorTokens.text.hints};
    padding-left: ${spacing[12]};
    border-left: 1px solid ${colorTokens.icon.hints};
  `,
  actionsWrapper: css`
    place-self: center end;
    display: inline-flex;
    gap: ${spacing[16]};
    padding-right: ${spacing[24]};

    ${Breakpoint.smallMobile} {
      padding-right: ${spacing[16]};
    }
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
  content: css`
    height: calc(100% - ${modal.HEADER_HEIGHT + modal.MARGIN_TOP}px);
    background-color: ${colorTokens.surface.courseBuilder};
    overflow-x: hidden;
    ${styleUtils.overflowYAuto}
  `,
};
