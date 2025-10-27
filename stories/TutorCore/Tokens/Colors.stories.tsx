import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';

const meta: Meta = {
  title: 'TutorCore/Tokens/Colors',
  parameters: {
    docs: {
      description: {
        component: `
# Color Tokens

TutorCore uses a comprehensive color system with semantic naming and multiple scales. All colors are designed to work across light and dark themes with proper contrast ratios.

## Color Scales

Each color family includes multiple shades from light to dark, following a consistent naming convention.
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;

// Color palette data based on the SCSS tokens
const colorPalettes = {
  brand: {
    100: '#f6f8fe',
    200: '#e4ebfc',
    300: '#dbe4fa',
    400: '#a4bcf4',
    500: '#4979e8',
    600: '#3e64de',
    700: '#2b49ca',
    800: '#293da4',
    900: '#263782',
    950: '#1c234f',
  },
  gray: {
    1: '#ffffff',
    10: '#fcfcfc',
    25: '#fafafa',
    50: '#f5f5f6',
    100: '#f0f1f1',
    200: '#ececed',
    300: '#cecfd2',
    400: '#94969c',
    500: '#85888e',
    600: '#61646c',
    700: '#333741',
    750: '#2d3039',
    800: '#1f242f',
    900: '#161b26',
    950: '#0c111d',
  },
  success: {
    25: '#fafef5',
    50: '#f3fee7',
    100: '#e3fbcc',
    200: '#d0f8ab',
    300: '#a6ef67',
    400: '#85e13a',
    500: '#66c61c',
    600: '#4ca30d',
    700: '#3b7c0f',
    800: '#326212',
    900: '#2b5314',
    950: '#15290a',
  },
  warning: {
    25: '#fffcf5',
    50: '#fffaeb',
    100: '#fef0c7',
    200: '#fedf89',
    300: '#fec84b',
    400: '#fdb022',
    500: '#f79009',
    600: '#dc6803',
    700: '#b54708',
    800: '#93370d',
    900: '#7a2e0e',
    950: '#4e1d09',
  },
  error: {
    25: '#fffbfa',
    50: '#fef3f2',
    100: '#fee4e2',
    200: '#fecdca',
    300: '#fda29b',
    400: '#f97066',
    500: '#f04438',
    600: '#d92d20',
    700: '#b42318',
    800: '#912018',
    900: '#7a271a',
    950: '#55160c',
  },
  yellow: {
    25: '#fefdf0',
    50: '#fefbe8',
    100: '#fef7c3',
    200: '#feee95',
    300: '#fde272',
    400: '#fac515',
    500: '#eaaa08',
    600: '#ca8504',
    700: '#a15c07',
    800: '#854a0e',
    900: '#713b12',
    950: '#542c0d',
  },
};

const ColorSwatch = ({ color, name, value }: { color: string; name: string; value: string }) => (
  <div
    css={css`
      display: flex;
      flex-direction: column;
      align-items: center;
      margin: 8px;
      min-width: 120px;
    `}
  >
    <div
      css={css`
        width: 80px;
        height: 80px;
        border-radius: 8px;
        background-color: ${color};
        border: 1px solid #e0e0e0;
        margin-bottom: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      `}
    />
    <div
      css={css`
        text-align: center;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 4px;
      `}
    >
      {name}
    </div>
    <div
      css={css`
        text-align: center;
        font-size: 10px;
        color: #666;
        font-family: monospace;
      `}
    >
      {value}
    </div>
  </div>
);

const ColorPalette = ({ title, colors }: { title: string; colors: Record<string, string> }) => (
  <div
    css={css`
      margin-bottom: 32px;
    `}
  >
    <h3
      css={css`
        margin-bottom: 16px;
        font-size: 18px;
        font-weight: 600;
        text-transform: capitalize;
      `}
    >
      {title} Colors
    </h3>
    <div
      css={css`
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
      `}
    >
      {Object.entries(colors).map(([shade, color]) => (
        <ColorSwatch key={shade} color={color} name={`${title}-${shade}`} value={color} />
      ))}
    </div>
  </div>
);

export const BrandColors: Story = {
  render: () => <ColorPalette title="Brand" colors={colorPalettes.brand} />,
  parameters: {
    docs: {
      description: {
        story: 'Primary brand colors used for buttons, links, and key interface elements.',
      },
    },
  },
};

export const GrayColors: Story = {
  render: () => <ColorPalette title="Gray" colors={colorPalettes.gray} />,
  parameters: {
    docs: {
      description: {
        story: 'Neutral colors used for text, borders, backgrounds, and surfaces.',
      },
    },
  },
};

export const SuccessColors: Story = {
  render: () => <ColorPalette title="Success" colors={colorPalettes.success} />,
  parameters: {
    docs: {
      description: {
        story: 'Green colors used for success states, confirmations, and positive feedback.',
      },
    },
  },
};

export const WarningColors: Story = {
  render: () => <ColorPalette title="Warning" colors={colorPalettes.warning} />,
  parameters: {
    docs: {
      description: {
        story: 'Orange colors used for warnings, cautions, and attention-grabbing elements.',
      },
    },
  },
};

export const ErrorColors: Story = {
  render: () => <ColorPalette title="Error" colors={colorPalettes.error} />,
  parameters: {
    docs: {
      description: {
        story: 'Red colors used for errors, destructive actions, and critical alerts.',
      },
    },
  },
};

export const YellowColors: Story = {
  render: () => <ColorPalette title="Yellow" colors={colorPalettes.yellow} />,
  parameters: {
    docs: {
      description: {
        story: 'Yellow colors used for highlights, notifications, and accent elements.',
      },
    },
  },
};

export const AllColors: Story = {
  render: () => (
    <div>
      <ColorPalette title="Brand" colors={colorPalettes.brand} />
      <ColorPalette title="Gray" colors={colorPalettes.gray} />
      <ColorPalette title="Success" colors={colorPalettes.success} />
      <ColorPalette title="Warning" colors={colorPalettes.warning} />
      <ColorPalette title="Error" colors={colorPalettes.error} />
      <ColorPalette title="Yellow" colors={colorPalettes.yellow} />
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Complete color palette showing all available colors in the TutorCore design system.',
      },
    },
  },
};