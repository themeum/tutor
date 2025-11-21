import { type AlpineComponentMeta } from '@Core/ts/types';

export interface CommentConnectorLineProps {
  repliesExpanded?: boolean;
}

interface AlpineContext {
  $el: HTMLElement;
}

interface ElementWithHandlers extends HTMLElement {
  _resizeHandler?: () => void;
  _observer?: MutationObserver;
}

/**
 * Connector line component for comments that connects avatar to replies toggle buttons.
 * Dynamically adjusts its height based on whether replies are expanded or collapsed.
 */
// eslint-disable-next-line @typescript-eslint/no-unused-vars
export const commentConnectorLine = (_props: CommentConnectorLineProps = {}) => {
  const updateLineHeight = (el: HTMLElement, repliesExpanded: boolean): void => {
    const commentEl = el.closest('.tutor-comment');
    if (!commentEl) return;

    const avatar = commentEl.querySelector('.tutor-comment-avatar .tutor-avatar') as HTMLElement;
    if (!avatar) return;

    const toggleBtn = commentEl.querySelector('.tutor-comment-replies-toggle') as HTMLElement | null;
    const collapseBtn = commentEl.querySelector('.tutor-comment-replies-collapse') as HTMLElement | null;

    const commentRect = commentEl.getBoundingClientRect();
    const avatarRect = avatar.getBoundingClientRect();
    const avatarBottomRelative = avatarRect.bottom - commentRect.top;
    const avatarCenterX = avatarRect.left + avatarRect.width / 2 - commentRect.left;

    let targetBtn: HTMLElement | null = null;
    if (repliesExpanded && collapseBtn && (collapseBtn as HTMLElement).offsetParent !== null) {
      targetBtn = collapseBtn;
    } else if (toggleBtn && toggleBtn.offsetParent !== null) {
      targetBtn = toggleBtn;
    }

    if (targetBtn) {
      const targetRect = targetBtn.getBoundingClientRect();
      const targetCenterY = targetRect.top + targetRect.height / 2 - commentRect.top;
      const GAP_SIZE = 4;

      const verticalStart = avatarBottomRelative + GAP_SIZE;
      const verticalHeight = Math.max(20, targetCenterY - verticalStart);

      const targetLeftRelative = targetRect.left - commentRect.left;
      const horizontalWidth = Math.max(0, targetLeftRelative - avatarCenterX - GAP_SIZE);

      el.style.top = `${verticalStart}px`;
      el.style.left = `${avatarCenterX}px`;
      el.style.height = `${verticalHeight}px`;
      el.style.width = `${horizontalWidth}px`;

      el.style.setProperty('--vertical-height', `${verticalHeight}px`);
      el.style.setProperty('--horizontal-width', `${horizontalWidth}px`);
    } else {
      const GAP_SIZE = 4;
      el.style.top = `${avatarBottomRelative + GAP_SIZE}px`;
      el.style.left = `${avatarCenterX}px`;
      el.style.height = '40px';
      el.style.width = '0px';
      el.style.setProperty('--vertical-height', '40px');
      el.style.setProperty('--horizontal-width', '0px');
    }
  };

  return {
    init(this: AlpineContext) {
      const el = this.$el;
      const commentEl = el.closest('.tutor-comment');
      if (!commentEl) return;

      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const parentData = (window.Alpine as any).$data(commentEl) as {
        repliesExpanded?: boolean;
        showReplyForm?: boolean;
      } | null;

      const getRepliesExpanded = (): boolean => {
        if (parentData && typeof parentData.repliesExpanded !== 'undefined') {
          return parentData.repliesExpanded;
        }
        const collapseBtn = commentEl.querySelector('.tutor-comment-replies-collapse') as HTMLElement | null;
        return collapseBtn !== null && collapseBtn.offsetParent !== null;
      };

      const update = (): void => {
        const expanded = getRepliesExpanded();
        updateLineHeight(el, expanded);
      };

      if (parentData) {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        (window.Alpine as any).effect(() => {
          const expanded = parentData.repliesExpanded;
          const replyFormOpen = parentData.showReplyForm;
          if (typeof expanded !== 'undefined' || typeof replyFormOpen !== 'undefined') {
            setTimeout(() => {
              const currentExpanded = getRepliesExpanded();
              updateLineHeight(el, currentExpanded);
            }, 200);
          }
        });
      }

      const observer = new MutationObserver(() => {
        setTimeout(update, 100);
      });
      observer.observe(commentEl, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'style', 'x-show'],
      });
      (el as ElementWithHandlers)._observer = observer;

      setTimeout(update, 100);
      setTimeout(update, 300);
      setTimeout(update, 500);

      const resizeHandler = (): void => update();
      window.addEventListener('resize', resizeHandler);
      (el as ElementWithHandlers)._resizeHandler = resizeHandler;
    },

    destroy(this: AlpineContext) {
      const el = this.$el as ElementWithHandlers;
      const resizeHandler = el._resizeHandler;
      const observer = el._observer;

      if (resizeHandler) {
        window.removeEventListener('resize', resizeHandler);
      }
      if (observer) {
        observer.disconnect();
      }
    },
  };
};

export const commentMeta: AlpineComponentMeta<CommentConnectorLineProps> = {
  name: 'commentConnectorLine',
  component: commentConnectorLine,
};
