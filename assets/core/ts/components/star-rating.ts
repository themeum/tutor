import { __ } from '@wordpress/i18n';

interface StarRatingConfig {
  initialRating?: number;
  fieldName: string;
}

const starRatingInput = (config: StarRatingConfig) => ({
  rating: config.initialRating || 1,
  hoverRating: 0,
  fieldName: config.fieldName,

  get effectiveRating() {
    return this.hoverRating > 0 ? this.hoverRating : this.rating;
  },

  get feedback(): string {
    const rating = this.effectiveRating;
    if (rating === 0) {
      return '';
    }

    const labels: Record<number, string> = {
      1: __('Poor', 'tutor'),
      2: __('Fair', 'tutor'),
      3: __('Average', 'tutor'),
      4: __('Good', 'tutor'),
      5: __('Amazing', 'tutor'),
    };

    if (Number.isInteger(rating)) {
      return labels[rating] || '';
    }

    // Handle fractional ratings (e.g., 4.5 -> "Good / Amazing")
    const lower = Math.floor(rating);
    const upper = Math.ceil(rating);

    const lowerLabel = labels[lower];
    const upperLabel = labels[upper];

    if (lowerLabel && upperLabel) {
      return `${lowerLabel} / ${upperLabel}`;
    }

    return lowerLabel || upperLabel || '';
  },

  setRating(val: number, onSet: (rating: number) => void) {
    this.rating = val;
    onSet(this.rating);
  },
});

export const starRatingMeta = {
  name: 'starRatingInput',
  component: starRatingInput,
};
