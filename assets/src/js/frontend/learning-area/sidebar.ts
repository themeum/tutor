/**
 * Learning Area Sidebar logic
 *
 * @since 4.0.0
 */

import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { type AxiosError } from 'axios';

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

export const sidebarComponent = ({
  isCollapsed,
  courseId,
  resetModalId,
}: {
  isCollapsed: boolean;
  courseId: number;
  resetModalId: string;
}) => {
  const { query, modal, toast } = window.TutorCore;

  return {
    pagesHeight: 0,
    resizing: false,
    collapsed: isCollapsed,
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

      this.resetProgressMutation = query.useMutation(
        (payload) => wpAjaxInstance.post(endpoints.RESET_COURSE_PROGRESS, payload),
        {
          onSuccess: (response) => {
            if (response.status_code === 200 && response.data?.redirect_to) {
              window.location.href = response.data.redirect_to;
              modal.closeModal(this.resetModalId);
            }
          },
          onError: (error: AxiosError) => {
            toast.error(convertToErrorMessage(error));
            if (!error || !error.response || !error.response.data) {
              window.location.reload();
            }
          },
        },
      );
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
      if (this.pagesHeight > 36) {
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
