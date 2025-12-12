import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type ServiceMeta } from '@Core/ts/types';
import { type ToastConfig } from '@Core/ts/types/toast';

export class ToastService {
  private hasContainer = false;

  private ensureContainer(): void {
    if (this.hasContainer || document.querySelector('.tutor-toast-container')) {
      this.hasContainer = true;
      return;
    }

    const containerHTML = `
      <div 
        x-data="tutorToast()" 
        class="tutor-toast-container tutor-toast-container--bottom-right"
      >
        <template x-for="toast in toasts" :key="toast.id">
          <div 
            x-show="true"
            x-transition:enter="tutor-toast-enter-right"
            x-transition:enter-end="tutor-toast-enter-active-right"
            x-transition:leave="tutor-toast-leave-right"
            x-transition:leave-end="tutor-toast-leave-active-right"
            :class="'tutor-toast tutor-toast--' + toast.type"
            role="alert"
          >
            <!-- Icon -->
            <div class="tutor-toast__icon">
              <!-- Success Icon -->
              <template x-if="toast.type === 'success'">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M13.3334 4L6.00002 11.3333L2.66669 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </template>

              <!-- Warning Icon -->
              <template x-if="toast.type === 'warning'">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M8 5.33334V8.66668M8 12H8.00667M7.05999 2.42001L1.77999 11.58C1.61479 11.8597 1.52667 12.1777 1.52441 12.5017C1.52216 12.8257 1.60585 13.1449 1.76703 13.427C1.92822 13.7091 2.16129 13.9443 2.44201 14.1081C2.72273 14.2719 3.04132 14.3586 3.36599 14.36H13.926C14.2507 14.3586 14.5693 14.2719 14.85 14.1081C15.1307 13.9443 15.3638 13.7091 15.525 13.427C15.6861 13.1449 15.7698 12.8257 15.7676 12.5017C15.7653 12.1777 15.6772 11.8597 15.512 11.58L10.232 2.42001C10.0638 2.14841 9.82812 1.92347 9.54721 1.76812C9.26629 1.61277 8.95004 1.53174 8.62866 1.53174C8.30727 1.53174 7.99102 1.61277 7.71011 1.76812C7.42919 1.92347 7.19355 2.14841 7.02532 2.42001H7.05999Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </template>

              <!-- Error Icon -->
              <template x-if="toast.type === 'error'">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </template>

              <!-- Info Icon -->
              <template x-if="toast.type === 'info'">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M8 14C11.3137 14 14 11.3137 14 8C14 4.68629 11.3137 2 8 2C4.68629 2 2 4.68629 2 8C2 11.3137 4.68629 14 8 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M8 10.6667V8M8 5.33334H8.00667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </template>
            </div>

            <!-- Content -->
            <div class="tutor-toast__content">
              <p class="tutor-toast__title" x-text="toast.title"></p>
              <p class="tutor-toast__message" x-text="toast.message"></p>
            </div>

            <!-- Close Button -->
            <button 
              class="tutor-toast__close" 
              @click="remove(toast.id)"
              aria-label="Close notification"
            >
              <svg class="tutor-icon" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10.5 3.5L3.5 10.5M3.5 3.5L10.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
        </template>
      </div>
    `;

    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = containerHTML;
    const container = tempDiv.firstElementChild as HTMLElement;
    document.body.appendChild(container);

    // Initialize Alpine on the new element
    if (window.Alpine) {
      window.Alpine.initTree(container);
    }

    this.hasContainer = true;
  }

  show(message: string, config: ToastConfig = {}): void {
    this.ensureContainer();
    document.dispatchEvent(
      new CustomEvent(TUTOR_CUSTOM_EVENTS.TOAST_SHOW, {
        detail: { message, config },
      }),
    );
  }

  success(message: string, duration?: number): void {
    this.show(message, { type: 'success', ...(duration !== undefined && { duration }) });
  }

  error(message: string, duration?: number): void {
    this.show(message, { type: 'error', ...(duration !== undefined && { duration }) });
  }

  warning(message: string, duration?: number): void {
    this.show(message, { type: 'warning', ...(duration !== undefined && { duration }) });
  }

  info(message: string, duration?: number): void {
    this.show(message, { type: 'info', ...(duration !== undefined && { duration }) });
  }

  clear(): void {
    document.dispatchEvent(new CustomEvent(TUTOR_CUSTOM_EVENTS.TOAST_CLEAR));
  }
}

export const toastServiceMeta: ServiceMeta<ToastService> = {
  name: 'toast',
  instance: new ToastService(),
};
