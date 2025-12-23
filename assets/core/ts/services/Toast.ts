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
        class="tutor-toast-container tutor-toast-container-bottom-center"
      >
        <template x-for="toast in toasts" :key="toast.id">
          <div 
            x-show="true"
            :class="'tutor-toast tutor-toast-' + toast.type"
            role="alert"
          >
            <!-- Icon -->
            <div class="tutor-toast-icon">
              <!-- Success Icon -->
              <template x-if="toast.type === 'success'">
                <span x-data="tutorIcon({ name: 'check-2', width: 20, height: 20})"></span>
              </template>

              <!-- Warning Icon -->
              <template x-if="toast.type === 'warning'">
                <span x-data="tutorIcon({ name: 'check-2', width: 20, height: 20})"></span>
              </template>

              <!-- Error Icon -->
              <template x-if="toast.type === 'error'">
                <span x-data="tutorIcon({ name: 'cross-2', width: 20, height: 20})"></span>
              </template>

              <!-- Info Icon -->
              <template x-if="toast.type === 'info'">
                <span x-data="tutorIcon({ name: 'info-octagon', width: 20, height: 20})"></span>
              </template>
            </div>

            <!-- Content -->
            <div class="tutor-toast-content">
              <div class="tutor-toast-title" x-text="toast.title"></div>
              <div class="tutor-toast-message" x-text="toast.message"></div>
            </div>

            <!-- Close Button -->
            <button 
              class="tutor-toast-close" 
              @click="remove(toast.id)"
              aria-label="Close notification"
            >
              <span x-data="tutorIcon({ name: 'cross-2', width: 20, height: 20})"></span>
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
