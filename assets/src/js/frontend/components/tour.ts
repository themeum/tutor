import { tutorConfig } from '@Core/ts/config/config';
import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';

interface SlideData {
  title: string;
  imageLarge: string;
  imageSmall: string;
}

const SLIDE_DIRECTION = {
  NEXT: 'next',
  BACK: 'back',
};

const tour = ({ slidesData, modalId }: { slidesData: SlideData[]; modalId: string }) => {
  const { modal, api, endpoints } = window.TutorCore;
  const isTourComplete = Number(tutorConfig.is_tour_completed);

  return {
    currentSlide: 0,
    slides: slidesData || [],
    isOpen: false,
    slideDirection: SLIDE_DIRECTION.NEXT,
    $nextTick: undefined as ((callback: () => void) => void) | undefined,
    _onModalClose: undefined as EventListener | undefined,

    init() {
      this._onModalClose = ((e: CustomEvent) => {
        if (e.detail?.id === modalId) {
          this.isOpen = false;
        }
      }) as EventListener;

      document.addEventListener(TUTOR_CUSTOM_EVENTS.MODAL_CLOSE, this._onModalClose);

      if (!isTourComplete) {
        this.isOpen = true;
        this.$nextTick?.(() => {
          modal.showModal(modalId);
        });
      }
    },

    destroy() {
      document.removeEventListener(TUTOR_CUSTOM_EVENTS.MODAL_CLOSE, this._onModalClose!);
    },

    next() {
      this.slideDirection = SLIDE_DIRECTION.NEXT;
      if (this.currentSlide < this.slides.length - 1) {
        this.currentSlide++;
      } else {
        this.skip();
      }
    },

    back() {
      this.slideDirection = SLIDE_DIRECTION.BACK;
      if (this.currentSlide > 0) {
        this.currentSlide--;
      }
    },

    skip() {
      // Fire-and-forget AJAX call to save user meta
      api.wpPost(endpoints.COMPLETE_TOUR);

      this.isOpen = false;
      modal.closeModal(modalId);
    },
  };
};

export const initializeTour = () => {
  window.TutorComponentRegistry.register({
    type: 'component',
    meta: {
      name: 'tour',
      component: tour,
    },
  });
  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
