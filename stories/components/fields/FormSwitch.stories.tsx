import { css } from '@emotion/react';
import FormSwitch from '@TutorShared/components/fields/FormSwitch';
import { DEFAULT_FORM_FIELD_PROPS, DEFAULT_FORM_FILED_STATE_PROPS } from '@TutorShared/config/constants';
import { colorTokens } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { Controller, useForm } from 'react-hook-form';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const meta = {
  title: 'Components/Fields/FormSwitch',
  component: FormSwitch,
  args: {
    label: 'Enable notifications',
    title: '',
    subTitle: '',
    disabled: false,
    loading: false,
    labelPosition: 'left',
    helpText: '',
    isHidden: false,
    labelCss: undefined,
    field: { ...DEFAULT_FORM_FIELD_PROPS, value: false },
    fieldState: DEFAULT_FORM_FILED_STATE_PROPS,
    onChange: undefined,
  },
  tags: ['autodocs'],
  argTypes: {
    label: {
      control: 'text',
      description: 'Label for the switch. Can be a string or ReactNode.',
    },
    title: {
      control: 'text',
      description: 'Title displayed above the switch (optional).',
    },
    subTitle: {
      control: 'text',
      description: 'Subtitle displayed below the switch (optional).',
    },
    disabled: {
      control: 'boolean',
      description: 'Disables the switch if true.',
    },
    loading: {
      control: 'boolean',
      description: 'Shows a loading indicator if true.',
    },
    labelPosition: {
      control: 'select',
      options: ['left', 'right'],
      defaultValue: 'left',
      description: 'Position of the label relative to the switch.',
    },
    helpText: {
      control: 'text',
      description: 'Additional help text displayed below the switch.',
    },
    isHidden: {
      control: 'boolean',
      description: 'Hides the switch if true.',
    },
    labelCss: {
      control: false,
      description: 'Custom EmotionJs styles for the label.',
    },
    field: {
      control: false,
      description: 'Form controller props for managing switch value and events.',
    },
    fieldState: {
      control: false,
      description: 'Form field state for validation and error handling.',
    },
    onChange: {
      control: false,
      description: 'Callback for switch value change event.',
    },
  },
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: `
FormSwitch is a flexible, accessible switch field for forms, styled with EmotionJs and config tokens.
It supports custom label position, help text, loading, disabled state, error state, controlled usage, and integrates with form controllers.
        `,
      },
    },
  },
  render: (args) => <FormSwitch {...args} />,
} satisfies Meta<typeof FormSwitch>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const WithHelpText = {
  args: {
    helpText: 'Turn on to receive email notifications.',
  },
} satisfies Story;

export const Disabled = {
  args: {
    disabled: true,
    label: 'Disabled Switch',
  },
} satisfies Story;

export const Loading = {
  args: {
    loading: true,
    label: 'Loading Switch',
  },
} satisfies Story;

export const RightLabel = {
  args: {
    labelPosition: 'right',
    label: 'Label on right',
  },
} satisfies Story;

export const CustomLabelStyle = {
  args: {
    label: 'Styled Label',
  },
  render: (args) => (
    <FormSwitch
      {...args}
      label={
        <span
          css={css`
            ${typography.heading6('bold')}
            color: ${colorTokens.brand.blue};
          `}
        >
          Custom Styled Label
        </span>
      }
    />
  ),
} satisfies Story;

export const Controlled = {
  render: (args) => {
    const form = useForm({
      defaultValues: {
        enabled: true,
      },
    });

    return (
      <Controller
        name="enabled"
        control={form.control}
        render={(controllerProps) => (
          <FormSwitch
            {...args}
            {...controllerProps}
            label="Controlled Switch"
            helpText="This switch is controlled by react-hook-form."
          />
        )}
      />
    );
  },
} satisfies Story;

export const WithError = {
  args: {
    label: 'Error Switch',
    fieldState: {
      ...DEFAULT_FORM_FILED_STATE_PROPS,
      error: {
        type: 'required',
        message: 'Please enable this setting',
      },
    },
  },
} satisfies Story;
