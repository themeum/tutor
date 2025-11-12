// Preview Trigger Component
// Shows course/lesson preview card on hover (desktop) or tap (mobile)
// Uses props-based data instead of API fetching

import { type AlpineComponentMeta } from '@Core/ts/types';
import { popover, type PopoverProps } from './popover';

export interface PreviewData {
  type: 'course' | 'lesson';
  title: string;
  excerpt?: string;
  thumbnail?: string;
  instructor?: string;
  students?: number;
  rating?: number;
  duration?: string;
  lessonType?: string;
  url?: string;
}

export interface PreviewTriggerProps extends PopoverProps {
  data?: PreviewData;
  delay?: number;
}

export const previewTrigger = (props: PreviewTriggerProps = {}) => {
  const popoverInstance = popover({
    placement: props.placement || 'bottom-start',
    offset: props.offset || 8,
    onShow: props.onShow,
    onHide: props.onHide,
  });

  return {
    ...popoverInstance,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    previewData: props.data || (null as any),
    isTouchDevice: false,
    hoverTimeout: null as number | null,
    hoverDelay: props.delay || 300,

    init() {
      popoverInstance.init.call(this);
      this.isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
      this.setupPreviewTriggers();
    },

    setupPreviewTriggers() {
      const trigger = this.$refs.trigger;
      if (!trigger) return;

      // Get preview data from data attribute if not provided in props
      if (!this.previewData && trigger.hasAttribute('data-tutor-preview-data')) {
        try {
          const dataAttr = trigger.getAttribute('data-tutor-preview-data');
          if (dataAttr) {
            this.previewData = JSON.parse(dataAttr);
          }
        } catch (error) {
          // eslint-disable-next-line no-console
          console.error('Failed to parse preview data:', error);
        }
      }

      // Get hover delay from data attribute
      if (trigger.hasAttribute('data-tutor-preview-delay')) {
        this.hoverDelay = parseInt(trigger.getAttribute('data-tutor-preview-delay') || '300', 10);
      }

      if (this.isTouchDevice) {
        // Mobile: tap to toggle
        trigger.addEventListener('click', (e: Event) => this.handleTap(e as MouseEvent));
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
      event.preventDefault();
      this.toggle();
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
      this.$nextTick(() => {
        this.updatePosition();
      });
    },

    renderPreview() {
      const content = this.$refs.content;
      if (!content || !this.previewData) return;

      const type = this.previewData.type;

      if (type === 'course') {
        this.renderCoursePreview(content);
      } else {
        this.renderLessonPreview(content);
      }
    },

    renderCoursePreview(content: HTMLElement) {
      if (!this.previewData) return;

      const data = this.previewData;

      content.innerHTML = `
        <div class="tutor-preview-card-content">
          ${data.thumbnail ? `<img src="${data.thumbnail}" alt="${this.escapeHtml(data.title)}" class="tutor-preview-card-thumbnail" />` : ''}
          <div class="tutor-preview-card-body">
            <h4 class="tutor-preview-card-title">${this.escapeHtml(data.title)}</h4>
            ${data.excerpt ? `<p class="tutor-preview-card-excerpt">${this.escapeHtml(data.excerpt)}</p>` : ''}
            <div class="tutor-preview-card-meta">
              ${data.instructor ? `<span class="tutor-preview-card-instructor">${this.escapeHtml(data.instructor)}</span>` : ''}
              ${data.students ? `<span class="tutor-preview-card-students">${data.students} students</span>` : ''}
              ${data.rating ? `<span class="tutor-preview-card-rating">★ ${data.rating}</span>` : ''}
            </div>
            ${data.url ? `<a href="${this.escapeHtml(data.url)}" class="tutor-preview-card-link">View Course →</a>` : ''}
          </div>
        </div>
      `;
    },

    renderLessonPreview(content: HTMLElement) {
      if (!this.previewData) return;

      const data = this.previewData;

      content.innerHTML = `
        <div class="tutor-preview-card-content">
          <div class="tutor-preview-card-body">
            <h4 class="tutor-preview-card-title">${this.escapeHtml(data.title)}</h4>
            ${data.excerpt ? `<p class="tutor-preview-card-excerpt">${this.escapeHtml(data.excerpt)}</p>` : ''}
            <div class="tutor-preview-card-meta">
              ${data.duration ? `<span class="tutor-preview-card-duration">${this.escapeHtml(data.duration)}</span>` : ''}
              ${data.lessonType ? `<span class="tutor-preview-card-type">${this.escapeHtml(data.lessonType)}</span>` : ''}
            </div>
            ${data.url ? `<a href="${this.escapeHtml(data.url)}" class="tutor-preview-card-link">View Lesson →</a>` : ''}
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
