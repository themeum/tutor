// Preview Trigger Component
// Shows course/lesson preview card on hover (desktop) or tap (mobile)
// Uses the popover component for positioning and display

import { type AlpineComponentMeta } from '@Core/ts/types';
import { popover, type PopoverProps } from './popover';

export interface PreviewTriggerProps extends PopoverProps {
  type?: 'course' | 'lesson';
  id?: number;
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
    isLoading: false,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    previewData: null as any,
    isTouchDevice: false,
    hoverTimeout: null as number | null,
    previewType: props.type,
    previewId: props.id,
    hoverDelay: props.delay || 300,

    init() {
      popoverInstance.init.call(this);
      this.isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
      this.setupPreviewTriggers();
    },

    setupPreviewTriggers() {
      const trigger = this.$refs.trigger;
      if (!trigger) return;

      // Get type and ID from data attributes if not provided in props
      if (!this.previewType) {
        this.previewType = trigger.getAttribute('data-tutor-preview') as 'course' | 'lesson';
      }
      if (!this.previewId) {
        this.previewId = parseInt(trigger.getAttribute('data-tutor-preview-id') || '0', 10);
      }
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

    async showPreview() {
      if (!this.previewType || !this.previewId) return;

      this.isLoading = true;

      // Show popover with loading state
      this.show();

      try {
        // Fetch preview data
        this.previewData = await this.fetchPreviewData(this.previewType, this.previewId);
        this.isLoading = false;

        // Update content after data is loaded
        this.renderPreview();

        // Reposition after content changes
        this.updatePosition();
      } catch (error) {
        // eslint-disable-next-line no-console
        console.error('Failed to fetch preview data:', error);
        this.isLoading = false;
        this.hide();
      }
    },

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    async fetchPreviewData(type: 'course' | 'lesson', id: number): Promise<any> {
      // TODO: Replace with actual API endpoint
      const response = {
        ok: true,
        json: async () => ({
          type,
          id,
          title: type === 'course' ? 'Sample Course Title (mock)' : 'Sample Lesson Title (mock)',
          excerpt: 'This is mock preview content used for testing.',
          thumbnail:
            type === 'course'
              ? 'https://workademy.tutorlms.io/wp-content/uploads/2025/09/Cloud-It-Ops_-Cloud-Fundamentals-for-Enterprise-Teams.webp'
              : undefined,
          instructor: type === 'course' ? 'Mock Instructor' : undefined,
          students: type === 'course' ? 42 : undefined,
          rating: type === 'course' ? 4.2 : undefined,
          duration: type === 'lesson' ? '12m' : undefined,
          url: '#',
        }),
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
      } as { ok: boolean; json: () => Promise<any> };

      if (!response.ok) {
        throw new Error('Failed to fetch preview data');
      }

      // Simulate network latency for preview fetch
      await new Promise<void>((resolve) => setTimeout(resolve, 800));
      return response.json();
    },

    renderPreview() {
      const content = this.$refs.content;
      if (!content || !this.previewData) return;

      const type = this.previewData.type || this.previewType;

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
          ${data.thumbnail ? `<img src="${data.thumbnail}" alt="${data.title}" class="tutor-preview-card-thumbnail" />` : ''}
          <div class="tutor-preview-card-body">
            <h4 class="tutor-preview-card-title">${data.title}</h4>
            ${data.excerpt ? `<p class="tutor-preview-card-excerpt">${data.excerpt}</p>` : ''}
            <div class="tutor-preview-card-meta">
              ${data.instructor ? `<span class="tutor-preview-card-instructor">${data.instructor}</span>` : ''}
              ${data.students ? `<span class="tutor-preview-card-students">${data.students} students</span>` : ''}
              ${data.rating ? `<span class="tutor-preview-card-rating">★ ${data.rating}</span>` : ''}
            </div>
            ${data.url ? `<a href="${data.url}" class="tutor-preview-card-link">View Course →</a>` : ''}
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
            <h4 class="tutor-preview-card-title">${data.title}</h4>
            ${data.excerpt ? `<p class="tutor-preview-card-excerpt">${data.excerpt}</p>` : ''}
            <div class="tutor-preview-card-meta">
              ${data.duration ? `<span class="tutor-preview-card-duration">${data.duration}</span>` : ''}
              ${data.type ? `<span class="tutor-preview-card-type">${data.type}</span>` : ''}
            </div>
            ${data.url ? `<a href="${data.url}" class="tutor-preview-card-link">View Lesson →</a>` : ''}
          </div>
        </div>
      `;
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
