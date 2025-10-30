import { type AlpineComponentMeta } from '@Core/types';

export interface ButtonProps {
  loading?: boolean;
  disabled?: boolean;
  onClick?: (event: Event) => void;
}

export const button = (props: ButtonProps = {}) => ({
  loading: props.loading || false,
  disabled: props.disabled || false,

  handleClick(event: Event) {
    if (this.disabled || this.loading) {
      event.preventDefault();
      return;
    }
    if (props.onClick) {
      props.onClick(event);
    }
  },
});

export const buttonMeta: AlpineComponentMeta<ButtonProps> = {
  name: 'button',
  component: button,
  global: true,
};
