import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';

interface SlideData {
  title: string;
  imageLarge: string;
  imageSmall: string;
}

const TOUR_LOCAL_STORAGE_KEY = 'tutor_tour_seen';
const SLIDE_DIRECTION = {
  NEXT: 'next',
  BACK: 'back',
};

const tour = ({ slidesData, modalId }: { slidesData: SlideData[]; modalId: string }) => {
  const modal = window.TutorCore.modal;
  return {
    currentSlide: 0,
    slides: slidesData || [],
    isOpen: false,
    slideDirection: SLIDE_DIRECTION.NEXT,
    $nextTick: undefined as ((callback: () => void) => void) | undefined,

    init() {
      window.addEventListener(TUTOR_CUSTOM_EVENTS.MODAL_CLOSE, ((e: CustomEvent) => {
        if (e.detail === modalId) {
          this.isOpen = false;
        }
      }) as EventListener);

      if (localStorage.getItem(TOUR_LOCAL_STORAGE_KEY) !== 'true') {
        this.isOpen = true;
        this.$nextTick?.(() => {
          modal.showModal(modalId);
        });
      }
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
      localStorage.setItem(TOUR_LOCAL_STORAGE_KEY, 'true');
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
