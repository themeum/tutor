import { type AlpineComponentMeta } from '@Core/ts/types';

interface ReadMoreProps {
  lines?: number;
  expanded?: boolean;
}

const DEFAULT_LINES = 4;
const readMore = (props: ReadMoreProps = {}) => {
  return {
    expanded: props.expanded ?? false,
    hasOverflow: false,
    lines: props.lines ?? DEFAULT_LINES,
    collapsedHeightPx: 0,
    resizeObserver: null as ResizeObserver | null,
    mutationObserver: null as MutationObserver | null,
    $el: null as HTMLElement | null,
    $refs: {} as { content?: HTMLElement; readMore?: HTMLElement; readLess?: HTMLElement },
    $watch: null as ((key: string, callback: () => void) => void) | null,
    $nextTick: null as ((callback: () => void) => void) | null,

    init() {
      const content = this.$refs.content;
      if (!content) {
        return;
      }

      // Clear the inline line-clamp styles that prevented flash of full
      // content (set in the PHP template) and switch to max-height.
      content.style.display = '';
      content.style.webkitLineClamp = '';
      content.style.setProperty('-webkit-box-orient', '');
      content.style.removeProperty('-webkit-line-clamp');
      content.style.removeProperty('-webkit-box-orient');
      content.style.removeProperty('display');

      this.computeCollapsedHeight(content);
      this.applyCollapsedStyles(content);

      this.$watch?.('expanded', () => this.applyState());

      this.$nextTick?.(() => {
        this.sync();
        this.setupObservers();
      });
    },

    /**
     * Calculate the collapsed height in px from lines × lineHeight.
     */
    computeCollapsedHeight(content: HTMLElement) {
      const computedStyle = getComputedStyle(content);
      let lineHeight = parseFloat(computedStyle.lineHeight);

      // If lineHeight is 'normal', estimate from font-size × 1.2.
      if (Number.isNaN(lineHeight)) {
        lineHeight = parseFloat(computedStyle.fontSize) * 1.2;
      }

      this.collapsedHeightPx = Math.ceil(this.lines * lineHeight);
    },

    setupObservers() {
      const content = this.$refs.content;
      if (!content) {
        return;
      }

      if (window.ResizeObserver) {
        this.resizeObserver = new ResizeObserver(() => this.sync());
        this.resizeObserver.observe(content);
      }

      if (window.MutationObserver) {
        this.mutationObserver = new MutationObserver(() => this.sync());
        this.mutationObserver.observe(content, {
          childList: true,
          subtree: true,
          characterData: true,
        });
      }
    },

    sync() {
      const content = this.$refs.content;
      if (!content) {
        return;
      }

      this.computeCollapsedHeight(content);

      // Temporarily clamp to measure overflow.
      const previousMaxHeight = content.style.maxHeight;
      const previousOverflow = content.style.overflow;

      content.style.maxHeight = `${this.collapsedHeightPx}px`;
      content.style.overflow = 'hidden';

      this.hasOverflow = content.scrollHeight > content.clientHeight + 1;

      content.style.maxHeight = previousMaxHeight;
      content.style.overflow = previousOverflow;

      this.applyState();
    },

    /**
     * Apply collapsed inline styles so content is hidden before Alpine
     * fully processes the component (prevents flash).
     */
    applyCollapsedStyles(content: HTMLElement) {
      content.style.maxHeight = `${this.collapsedHeightPx}px`;
      content.style.overflow = 'hidden';
    },

    applyState() {
      const root = this.$el;
      const content = this.$refs.content;
      const readMoreBtn = this.$refs.readMore;

      if (!content || !root) {
        return;
      }

      // Root needs relative positioning for the absolute "read more" button.
      root.style.position = 'relative';

      if (this.expanded || !this.hasOverflow) {
        content.style.maxHeight = 'none';
        content.style.overflow = 'visible';
        this.styleReadMoreButton(readMoreBtn, false);
        return;
      }

      content.style.maxHeight = `${this.collapsedHeightPx}px`;
      content.style.overflow = 'hidden';
      this.styleReadMoreButton(readMoreBtn, true);
    },

    /**
     * Position the "read more" button at the bottom-right of the last
     * visible line, with a matching background so it overlaps text
     * seamlessly — exactly like the user-profile bio toggle.
     */
    styleReadMoreButton(btn: HTMLElement | undefined, collapsed: boolean) {
      if (!btn) {
        return;
      }

      if (!collapsed) {
        btn.style.position = '';
        btn.style.bottom = '';
        btn.style.right = '';
        btn.style.paddingLeft = '';
        return;
      }

      btn.style.position = 'absolute';
      btn.style.bottom = '0';
      btn.style.right = '0';
      btn.style.paddingLeft = '4px';
    },

    toggle() {
      if (!this.hasOverflow && !this.expanded) {
        return;
      }

      this.expanded = !this.expanded;
    },

    destroy() {
      this.resizeObserver?.disconnect();
      this.mutationObserver?.disconnect();
    },
  };
};

export const readMoreMeta: AlpineComponentMeta<ReadMoreProps> = {
  name: 'readMore',
  component: readMore,
};
