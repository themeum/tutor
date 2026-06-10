// Preview Trigger Component
// Shows course/lesson preview card on hover

import { __, sprintf } from '@wordpress/i18n';

import { type AlpineComponentMeta } from '@Core/ts/types';

import { popover, type PopoverProps } from './popover';

export interface PreviewData {
  title: string;
  url: string;
  thumbnail: string;
  instructor: string;
  instructor_url: string;
}

export interface PreviewTriggerProps extends PopoverProps {
  data?: PreviewData;
  delay?: number;
}

export const previewTrigger = (props: PreviewTriggerProps = {}) => {
  const popoverInstance = popover({
    placement: props.placement || 'bottom-start',
    offset: props.offset ?? 4,
    onShow: props.onShow,
    onHide: props.onHide,
  });

  return {
    ...popoverInstance,
    previewData: props.data || null,
    isTouchDevice: false,
    hoverTimeout: null as number | null,
    hoverDelay: props.delay || 300,
    $nextTick: undefined as ((callback: () => void) => void) | undefined,

    init() {
      popoverInstance.init.call(this);
      this.isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
      this.setupPreviewTriggers();
    },

    setupPreviewTriggers() {
      const trigger = this.$refs.trigger;
      if (!trigger) return;

      // Get hover delay from data attribute
      if (trigger.hasAttribute('data-tutor-preview-delay')) {
        this.hoverDelay = parseInt(trigger.getAttribute('data-tutor-preview-delay') || '300', 10);
      }

      if (this.isTouchDevice) {
        // Mobile: tap to toggle
        trigger.addEventListener('click', (e: Event) => this.handleTap(e as MouseEvent));

        // Prevent clicks inside content from bubbling to trigger on mobile
        // This ensures links inside content work and tapping content doesn't toggle the popover
        const content = this.$refs.content;
        if (content) {
          content.addEventListener('click', (e: Event) => {
            e.stopPropagation();
          });
        }
      } else {
        // Desktop: hover to show
        trigger.addEventListener('mouseenter', () => this.handleMouseEnter());
        trigger.addEventListener('mouseleave', () => this.handleMouseLeave());

        // Keep popover open when hovering over content
        const content = this.$refs.content;
        if (content) {
          content.addEventListener('mouseenter', () => {
            if (this.hoverTimeout) {
              clearTimeout(this.hoverTimeout);
              this.hoverTimeout = null;
            }
          });
          content.addEventListener('mouseleave', () => this.handleMouseLeave());
        }
      }
    },

    handleTap(event: MouseEvent) {
      const target = event.target as HTMLElement;
      const content = this.$refs.content;

      // If the tap is inside the preview content, let it be handled there
      // This is a fallback in case propagation wasn't stopped
      if (content && content.contains(target)) {
        return;
      }

      event.preventDefault();
      if (this.open) {
        this.hide();
        return;
      }
      this.showPreview();
    },

    handleMouseEnter() {
      // Clear any existing timeout
      if (this.hoverTimeout) {
        clearTimeout(this.hoverTimeout);
      }

      // Show after delay
      this.hoverTimeout = window.setTimeout(() => {
        this.showPreview();
      }, this.hoverDelay);
    },

    handleMouseLeave() {
      // Clear timeout if user moves away before delay
      if (this.hoverTimeout) {
        clearTimeout(this.hoverTimeout);
        this.hoverTimeout = null;
      }

      // Hide preview after a short delay
      setTimeout(() => {
        const content = this.$refs.content;
        if (!content?.matches(':hover') && !this.$refs.trigger?.matches(':hover')) {
          this.hide();
        }
      }, 100);
    },

    showPreview() {
      if (!this.previewData) return;

      // Show popover
      this.show();

      // Render content
      this.renderPreview();

      // Reposition after content is rendered
      if (this.$nextTick) {
        this.updatePosition();
      } else {
        this.updatePosition();
      }
    },

    renderPreview() {
      const content = this.$refs.content;
      if (!content || !this.previewData) return;

      this.renderCoursePreview(content);
    },

    renderCoursePreview(content: HTMLElement) {
      if (!this.previewData) return;

      const data = this.previewData;
      const thumbnailHtml = data.thumbnail
        ? `<img src="${data.thumbnail}" alt="${this.escapeHtml(data.title)}" class="tutor-preview-card-thumbnail" />`
        : '';

      content.innerHTML = `
        <div class="tutor-preview-card-content">
          ${data.thumbnail ? (data.url ? `<a href="${data.url}">${thumbnailHtml}</a>` : thumbnailHtml) : ''}
          <div class="tutor-preview-card-body">
            <h4 class="tutor-preview-card-title">${data.url ? `<a href="${data.url}">${this.escapeHtml(data.title)}</a>` : this.escapeHtml(data.title)}</h4>
            ${data.instructor ? `<div class="tutor-preview-card-instructor">${sprintf(__(`by <a href="${data.instructor_url}">%s</a>`, 'tutor'), this.escapeHtml(data.instructor))}</div>` : ''}
          </div>
        </div>
      `;
    },

    escapeHtml(text: string): string {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    },

    destroy() {
      if (this.hoverTimeout) {
        clearTimeout(this.hoverTimeout);
      }
      popoverInstance.destroy.call(this);
    },
  };
};

export const previewTriggerMeta: AlpineComponentMeta<PreviewTriggerProps> = {
  name: 'previewTrigger',
  component: previewTrigger,
};
