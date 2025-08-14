import { css } from '@emotion/react';
import Button from '@TutorShared/atoms/Button';
import ToastProvider, { useToast } from '@TutorShared/atoms/Toast';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Atoms/Toast',
  component: ToastProvider,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'ToastProvider provides a context and UI for showing toast notifications. Use the `useToast` hook to trigger toasts of different types, positions, and durations.',
      },
    },
  },
  argTypes: {
    children: { control: false },
    position: {
      control: 'select',
      options: ['top-left', 'top-right', 'top-center', 'bottom-left', 'bottom-right', 'bottom-center'],
      description: 'Position of the toast container.',
      defaultValue: 'bottom-right',
    },
  },
  args: {
    position: 'bottom-right',
  },
  render: (args) => (
    <ToastProvider {...args}>
      <ToastDemo />
    </ToastProvider>
  ),
} satisfies Meta<typeof ToastProvider>;

export default meta;

type Story = StoryObj<typeof meta>;

const ToastDemo = () => {
  const { showToast } = useToast();

  const handleShowToast = (type: 'success' | 'danger' | 'warning' | 'dark') => () => {
    showToast({
      type,
      message: `This is a ${type} toast!`,
    });
  };

  const handleShowAutoCloseToast = () => {
    showToast({
      type: 'success',
      message: 'This toast will auto-close in 5 seconds.',
      autoCloseDelay: 5000,
    });
  };

  const handleShowManualCloseToast = () => {
    showToast({
      type: 'warning',
      message: 'This toast will stay until you close it.',
      autoCloseDelay: false,
    });
  };

  return (
    <div
      css={css`
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 20px;
        align-items: center;
        min-height: 100dvh;
        min-width: 100dvw;
      `}
    >
      <Button variant="primary" onClick={handleShowToast('success')} aria-label="Show Success Toast" tabIndex={0}>
        Show Success Toast
      </Button>

      <Button variant="danger" onClick={handleShowToast('danger')} aria-label="Show Danger Toast" tabIndex={0}>
        Show Danger Toast
      </Button>

      <Button variant="secondary" onClick={handleShowToast('warning')} aria-label="Show Warning Toast" tabIndex={0}>
        Show Warning Toast
      </Button>

      <Button variant="text" onClick={handleShowToast('dark')} aria-label="Show Dark Toast" tabIndex={0}>
        Show Dark Toast
      </Button>

      <Button variant="primary" onClick={handleShowAutoCloseToast} aria-label="Show Auto Close Toast" tabIndex={0}>
        Show Auto Close (5s)
      </Button>

      <Button
        variant="secondary"
        onClick={handleShowManualCloseToast}
        aria-label="Show Manual Close Toast"
        tabIndex={0}
      >
        Show Manual Close Toast
      </Button>
    </div>
  );
};

export const Default = {
  args: {
    position: 'bottom-right',
    children: <ToastDemo />,
  },
} satisfies Story;
