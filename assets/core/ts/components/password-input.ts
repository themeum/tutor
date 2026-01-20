import { type AlpineComponentMeta } from '@Core/ts/types';
import { __ } from '@wordpress/i18n';

export interface PasswordInputProps {
  showStrength?: boolean;
  minStrength?: number;
}

const defaultProps: PasswordInputProps = {
  showStrength: false,
  minStrength: 3,
};

type StrengthLevel = 0 | 1 | 2 | 3 | 4 | 5;

const strengthLabels: Record<StrengthLevel, string> = {
  0: __('Very weak', 'tutor'),
  1: __('Very weak', 'tutor'),
  2: __('Weak', 'tutor'),
  3: __('Medium', 'tutor'),
  4: __('Strong', 'tutor'),
  5: __('Very strong', 'tutor'),
};

const strengthColors: Record<StrengthLevel, string> = {
  0: 'var(--tutor-text-critical)',
  1: 'var(--tutor-text-critical)',
  2: 'var(--tutor-text-critical)',
  3: 'var(--tutor-text-warning)',
  4: 'var(--tutor-text-success)',
  5: 'var(--tutor-text-success)',
};

export const passwordInput = (props: PasswordInputProps = defaultProps) => ({
  showPassword: false,
  strength: 0 as StrengthLevel,
  strengthLabel: '',
  strengthColor: '',
  showStrength: props.showStrength ?? false,
  minStrength: props.minStrength ?? 3,
  password: '',

  init() {
    const input = (this as unknown as { $el: HTMLElement }).$el.querySelector('input') as HTMLInputElement;
    if (input) {
      input.addEventListener('input', (e) => {
        this.password = (e.target as HTMLInputElement).value;
        if (this.showStrength) {
          this.checkStrength();
        }
      });
    }
  },

  destroy() {
    const input = (this as unknown as { $el: HTMLElement }).$el.querySelector('input') as HTMLInputElement;
    if (input) {
      input.removeEventListener('input', (e) => {
        this.password = (e.target as HTMLInputElement).value;
        if (this.showStrength) {
          this.checkStrength();
        }
      });
    }
  },

  toggleVisibility() {
    this.showPassword = !this.showPassword;
    const root = (this as unknown as { $root: HTMLElement }).$root;
    const input = root.querySelector('input') as HTMLInputElement;

    if (input) {
      input.type = this.showPassword ? 'text' : 'password';
    }
  },

  checkStrength() {
    this.strength = this.calculateBasicStrength();

    this.strengthLabel = strengthLabels[this.strength] || '';
    this.strengthColor = strengthColors[this.strength] || '';
  },

  calculateBasicStrength(): StrengthLevel {
    const pwd = this.password;
    if (!pwd) {
      return 0;
    }

    let score = 0;

    if (pwd.length >= 8) score++;
    if (pwd.length >= 12) score++;
    if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score++;
    if (/\d/.test(pwd)) score++;
    if (/[^a-zA-Z0-9]/.test(pwd)) score++;

    return Math.min(score, 5) as StrengthLevel;
  },

  getToggleBindings() {
    return {
      '@click': 'toggleVisibility()',
      class: 'tutor-password-toggle',
      type: 'button',
      'aria-label': this.showPassword ? __('Hide password', 'tutor') : __('Show password', 'tutor'),
    };
  },

  getStrengthTextBindings() {
    return {
      'x-text': 'strengthLabel',
      ':style': `{color: strengthColor}`,
    };
  },
});

export const passwordInputMeta: AlpineComponentMeta<PasswordInputProps> = {
  name: 'passwordInput',
  component: passwordInput,
};
