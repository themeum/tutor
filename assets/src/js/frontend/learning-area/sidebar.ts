/**
 * Learning Area Sidebar logic
 *
 * @since 4.0.0
 */

import { type MutationState } from '@Core/ts/services/Query';

interface ResetProgressPayload {
  course_id: number;
  context: string;
}

interface ResetProgressResponse {
  success: boolean;
  status_code?: number;
  message?: string;
  data?: {
    redirect_to: string;
  };
}

export const sidebarComponent = ({ courseId, resetModalId }: { courseId: number; resetModalId: string }) => {
  const { query, modal, toast } = window.TutorCore;
  const { wpPost } = window.TutorCore.api;
  const { convertToErrorMessage } = window.TutorCore.error;
  const { endpoints } = window.TutorCore;

  return {
    pagesHeight: null as number | null,
    resizing: false,
    sidebarOpen: false,
    courseId: courseId,
    resetModalId: resetModalId,
    resetProgressMutation: null as MutationState<ResetProgressResponse, ResetProgressPayload> | null,
    $refs: {} as Record<string, HTMLElement>,
    $nextTick: {} as (callback: () => void) => void,

    init() {
      this.$nextTick(() => {
        if (this.$refs.pagesList) {
          this.pagesHeight = this.$refs.pagesList.scrollHeight;
        }
      });

      this.resetProgressMutation = query.useMutation((payload) => wpPost(endpoints.RESET_COURSE_PROGRESS, payload), {
        onSuccess: (response) => {
          if (response.status_code === 200 && response.data?.redirect_to) {
            modal.closeModal(this.resetModalId);
            window.location.href = response.data.redirect_to;
          }
        },
        onError: (error) => {
          toast.error(convertToErrorMessage(error));
          if (error.message?.includes('HTTP')) {
            window.location.reload();
          }
        },
      });
    },

    toggleSidebar() {
      this.sidebarOpen = !this.sidebarOpen;
    },

    closeSidebar() {
      this.sidebarOpen = false;
    },

    startResizing(e: MouseEvent) {
      this.resizing = true;
      const startY = e.clientY;
      const currentHeight = this.$refs.pagesList?.offsetHeight || 0;

      const onMouseMove = (moveEvent: MouseEvent) => {
        const delta = startY - moveEvent.clientY;
        // Constraint height between 36px and 400px.
        this.pagesHeight = Math.max(36, Math.min(400, currentHeight + delta));
      };

      const onMouseUp = () => {
        this.resizing = false;
        window.removeEventListener('mousemove', onMouseMove);
        window.removeEventListener('mouseup', onMouseUp);
      };

      window.addEventListener('mousemove', onMouseMove);
      window.addEventListener('mouseup', onMouseUp);
    },

    togglePagesHeight() {
      if ((this.pagesHeight || 0) > 36) {
        this.pagesHeight = 36;
      } else if (this.$refs.pagesList) {
        this.pagesHeight = this.$refs.pagesList.scrollHeight;
      }
    },

    confirmReset() {
      modal.showModal(this.resetModalId);
    },

    resetProgress() {
      this.resetProgressMutation?.mutate({ course_id: this.courseId, context: 'learning-area-sidebar' });
    },
  };
};

export const initializeSidebar = () => {
  if (window.TutorComponentRegistry) {
    window.TutorComponentRegistry.register({
      type: 'component',
      meta: {
        name: 'learningSidebar',
        component: sidebarComponent,
      },
    });
  }
};
