import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';

const meta: Meta = {
  title: 'TutorCore/Themes/Dark Theme',
  parameters: {
    docs: {
      description: {
        component: `
# Dark Theme

The TutorCore dark theme provides a modern, eye-friendly interface optimized for low-light environments. It maintains excellent contrast ratios and readability while reducing eye strain.

## Features

- **Reduced Eye Strain**: Optimized for low-light environments
- **High Contrast**: Maintains accessibility standards in dark mode
- **Semantic Colors**: Meaningful color assignments adapted for dark backgrounds
- **Surface Hierarchy**: Clear depth and organization with dark surfaces
- **Brand Consistency**: Brand colors adapted for dark theme visibility

## Theme Activation

\`\`\`html
<html data-theme="dark">
  <!-- Your content -->
</html>
\`\`\`
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;
// Dark theme color tokens
const darkTheme = {
  // Text colors
  textPrimary: '#ffffff',
  textSecondary: '#cecfd2',
  textDisabled: '#94969c',
  textInverse: '#333741',
  textBrand: '#a4bcf4',
  textSuccess: '#a6ef67',
  textWarning: '#fec84b',
  textError: '#fda29b',
  
  // Surface colors
  surfaceBase: '#0c111d',
  surfaceL1: '#161b26',
  surfaceL2: '#1f242f',
  surfaceL3: '#2d3039',
  
  // Border colors
  borderIdle: '#333741',
  borderHover: '#61646c',
  borderActive: '#a4bcf4',
  borderError: '#fda29b',
  borderSuccess: '#a6ef67',
  
  // Action colors
  actionsPrimary: '#4979e8',
  actionsSecondary: '#2d3039',
  actionsSuccess: '#66c61c',
  actionsWarning: '#f79009',
  actionsError: '#f04438',
};

const ColorSwatch = ({ 
  name, 
  value, 
  description, 
  textColor = '#fff' 
}: { 
  name: string; 
  value: string; 
  description: string;
  textColor?: string;
}) => (
  <div css={css`
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    padding: 12px;
    background: ${darkTheme.surfaceL1};
    border: 1px solid ${darkTheme.borderIdle};
    border-radius: 8px;
  `}>
    <div css={css`
      width: 48px;
      height: 48px;
      background: ${value};
      border-radius: 6px;
      border: 1px solid ${darkTheme.borderIdle};
      margin-right: 16px;
      flex-shrink: 0;
    `} />
    <div css={css`flex: 1;`}>
      <div css={css`
        font-size: 14px;
        font-weight: 600;
        color: ${textColor};
        margin-bottom: 4px;
      `}>
        {name}
      </div>
      <div css={css`
        font-size: 12px;
        color: ${darkTheme.textSecondary};
        margin-bottom: 4px;
      `}>
        {description}
      </div>
      <div css={css`
        font-size: 11px;
        font-family: monospace;
        color: ${darkTheme.textDisabled};
        background: ${darkTheme.surfaceL2};
        padding: 2px 6px;
        border-radius: 3px;
        display: inline-block;
      `}>
        {value}
      </div>
    </div>
  </div>
);

export const TextColors: Story = {
  render: () => (
    <div css={css`background: ${darkTheme.surfaceBase}; padding: 24px; border-radius: 8px;`}>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600; color: ${darkTheme.textPrimary};`}>
        Dark Theme Text Colors
      </h2>
      <ColorSwatch 
        name="Primary Text" 
        value={darkTheme.textPrimary}
        description="Main text content, headings, labels"
      />
      <ColorSwatch 
        name="Secondary Text" 
        value={darkTheme.textSecondary}
        description="Supporting text, descriptions, metadata"
      />
      <ColorSwatch 
        name="Disabled Text" 
        value={darkTheme.textDisabled}
        description="Disabled form elements, inactive states"
      />
      <ColorSwatch 
        name="Brand Text" 
        value={darkTheme.textBrand}
        description="Links, brand elements, call-to-action text"
      />
      <ColorSwatch 
        name="Success Text" 
        value={darkTheme.textSuccess}
        description="Success messages, positive feedback"
      />
      <ColorSwatch 
        name="Warning Text" 
        value={darkTheme.textWarning}
        description="Warning messages, caution text"
      />
      <ColorSwatch 
        name="Error Text" 
        value={darkTheme.textError}
        description="Error messages, validation feedback"
      />
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Dark theme text colors optimized for readability on dark backgrounds.',
      },
    },
  },
};
export const SurfaceColors: Story = {
  render: () => (
    <div css={css`background: #000; padding: 24px; border-radius: 8px;`}>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600; color: ${darkTheme.textPrimary};`}>
        Dark Theme Surface Colors
      </h2>
      <ColorSwatch 
        name="Base Surface" 
        value={darkTheme.surfaceBase}
        description="Main background, page background"
      />
      <ColorSwatch 
        name="Level 1 Surface" 
        value={darkTheme.surfaceL1}
        description="Cards, modals, elevated content"
      />
      <ColorSwatch 
        name="Level 2 Surface" 
        value={darkTheme.surfaceL2}
        description="Input backgrounds, secondary cards"
      />
      <ColorSwatch 
        name="Level 3 Surface" 
        value={darkTheme.surfaceL3}
        description="Hover states, subtle highlights"
      />
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Dark theme surface hierarchy creating depth and visual organization.',
      },
    },
  },
};

export const ComponentShowcase: Story = {
  render: () => (
    <div css={css`
      background: ${darkTheme.surfaceBase};
      padding: 24px;
      border-radius: 8px;
      border: 1px solid ${darkTheme.borderIdle};
      min-height: 600px;
    `}>
      <h2 css={css`
        margin: 0 0 24px 0; 
        font-size: 20px; 
        font-weight: 600;
        color: ${darkTheme.textPrimary};
      `}>
        Dark Theme Components
      </h2>
      
      {/* Card Example */}
      <div css={css`
        background: ${darkTheme.surfaceL1};
        border: 1px solid ${darkTheme.borderIdle};
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 24px;
      `}>
        <h3 css={css`
          margin: 0 0 12px 0;
          font-size: 18px;
          font-weight: 600;
          color: ${darkTheme.textPrimary};
        `}>
          Dark Theme Card
        </h3>
        <p css={css`
          margin: 0 0 16px 0;
          color: ${darkTheme.textSecondary};
          line-height: 1.5;
        `}>
          This card demonstrates the dark theme color scheme with proper contrast ratios 
          optimized for low-light environments and reduced eye strain.
        </p>
        <div css={css`display: flex; gap: 12px; flex-wrap: wrap;`}>
          <button css={css`
            padding: 8px 16px;
            background: ${darkTheme.actionsPrimary};
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            
            &:hover {
              background: #3e64de;
            }
          `}>
            Primary Action
          </button>
          <button css={css`
            padding: 8px 16px;
            background: ${darkTheme.actionsSecondary};
            color: ${darkTheme.textPrimary};
            border: 1px solid ${darkTheme.borderIdle};
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            
            &:hover {
              background: ${darkTheme.surfaceL2};
            }
          `}>
            Secondary Action
          </button>
        </div>
      </div>

      {/* Form Example */}
      <div css={css`
        background: ${darkTheme.surfaceL1};
        border: 1px solid ${darkTheme.borderIdle};
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 24px;
      `}>
        <h3 css={css`
          margin: 0 0 16px 0;
          font-size: 18px;
          font-weight: 600;
          color: ${darkTheme.textPrimary};
        `}>
          Dark Theme Form Elements
        </h3>
        
        <div css={css`margin-bottom: 16px;`}>
          <label css={css`
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 500;
            color: ${darkTheme.textPrimary};
          `}>
            Email Address
          </label>
          <input 
            type="email" 
            placeholder="Enter your email"
            css={css`
              width: 100%;
              padding: 10px 12px;
              background: ${darkTheme.surfaceL2};
              border: 1px solid ${darkTheme.borderIdle};
              border-radius: 6px;
              font-size: 14px;
              color: ${darkTheme.textPrimary};
              
              &:focus {
                outline: none;
                border-color: ${darkTheme.borderActive};
                box-shadow: 0 0 0 3px rgba(164, 188, 244, 0.2);
              }
              
              &::placeholder {
                color: ${darkTheme.textDisabled};
              }
            `}
          />
        </div>

        <div css={css`margin-bottom: 16px;`}>
          <label css={css`
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 500;
            color: ${darkTheme.textPrimary};
          `}>
            Message
          </label>
          <textarea 
            placeholder="Enter your message"
            rows={3}
            css={css`
              width: 100%;
              padding: 10px 12px;
              background: ${darkTheme.surfaceL2};
              border: 1px solid ${darkTheme.borderIdle};
              border-radius: 6px;
              font-size: 14px;
              color: ${darkTheme.textPrimary};
              resize: vertical;
              
              &:focus {
                outline: none;
                border-color: ${darkTheme.borderActive};
                box-shadow: 0 0 0 3px rgba(164, 188, 244, 0.2);
              }
              
              &::placeholder {
                color: ${darkTheme.textDisabled};
              }
            `}
          />
        </div>
      </div>

      {/* Status Messages */}
      <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;`}>
        <div css={css`
          padding: 12px 16px;
          background: rgba(166, 239, 103, 0.1);
          border: 1px solid ${darkTheme.borderSuccess};
          border-radius: 6px;
          border-left: 4px solid ${darkTheme.actionsSuccess};
        `}>
          <div css={css`
            font-size: 14px;
            font-weight: 600;
            color: ${darkTheme.textSuccess};
            margin-bottom: 4px;
          `}>
            Success Message
          </div>
          <div css={css`
            font-size: 12px;
            color: ${darkTheme.textSuccess};
          `}>
            Operation completed successfully
          </div>
        </div>

        <div css={css`
          padding: 12px 16px;
          background: rgba(254, 200, 75, 0.1);
          border: 1px solid rgba(254, 200, 75, 0.3);
          border-radius: 6px;
          border-left: 4px solid ${darkTheme.actionsWarning};
        `}>
          <div css={css`
            font-size: 14px;
            font-weight: 600;
            color: ${darkTheme.textWarning};
            margin-bottom: 4px;
          `}>
            Warning Message
          </div>
          <div css={css`
            font-size: 12px;
            color: ${darkTheme.textWarning};
          `}>
            Please review your input
          </div>
        </div>

        <div css={css`
          padding: 12px 16px;
          background: rgba(253, 162, 155, 0.1);
          border: 1px solid ${darkTheme.borderError};
          border-radius: 6px;
          border-left: 4px solid ${darkTheme.actionsError};
        `}>
          <div css={css`
            font-size: 14px;
            font-weight: 600;
            color: ${darkTheme.textError};
            margin-bottom: 4px;
          `}>
            Error Message
          </div>
          <div css={css`
            font-size: 12px;
            color: ${darkTheme.textError};
          `}>
            Something went wrong
          </div>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Complete dark theme component showcase demonstrating color usage and contrast in dark environments.',
      },
    },
  },
};