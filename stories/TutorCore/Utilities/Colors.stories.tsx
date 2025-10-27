import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';

const meta: Meta = {
  title: 'TutorCore/Utilities/Colors',
  parameters: {
    docs: {
      description: {
        component: `
# Color Utilities

TutorCore provides comprehensive color utility classes for text, backgrounds, and borders. All utilities support both light and dark themes and automatically adapt based on the current theme.

## Features

- **Text Colors**: Semantic text colors for different content types
- **Background Colors**: Surface and accent background colors
- **Border Colors**: Consistent border colors for different states
- **Theme Aware**: Automatically adapts to light/dark themes
- **Semantic Naming**: Meaningful color names based on usage

## CSS Classes

\`\`\`css
/* Text colors */
.tutor-text-primary
.tutor-text-secondary
.tutor-text-disabled
.tutor-text-brand
.tutor-text-success
.tutor-text-warning
.tutor-text-error

/* Background colors */
.tutor-bg-surface-base
.tutor-bg-surface-l1
.tutor-bg-surface-l2
.tutor-bg-brand-{100-950}
.tutor-bg-success-{25-950}
.tutor-bg-warning-{25-950}
.tutor-bg-error-{25-950}

/* Border colors */
.tutor-border-idle
.tutor-border-hover
.tutor-border-active
.tutor-border-error
.tutor-border-success
\`\`\`
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;
const textColors = [
  { name: 'Primary Text', class: 'tutor-text-primary', color: '#333741', description: 'Main content, headings' },
  { name: 'Secondary Text', class: 'tutor-text-secondary', color: '#61646c', description: 'Supporting text, descriptions' },
  { name: 'Disabled Text', class: 'tutor-text-disabled', color: '#94969c', description: 'Disabled elements' },
  { name: 'Brand Text', class: 'tutor-text-brand', color: '#4979e8', description: 'Links, brand elements' },
  { name: 'Success Text', class: 'tutor-text-success', color: '#2b5314', description: 'Success messages' },
  { name: 'Warning Text', class: 'tutor-text-warning', color: '#7a2e0e', description: 'Warning messages' },
  { name: 'Error Text', class: 'tutor-text-error', color: '#7a271a', description: 'Error messages' },
];

const backgroundColors = [
  { name: 'Base Surface', class: 'tutor-bg-surface-base', color: '#ffffff', description: 'Page background' },
  { name: 'Level 1 Surface', class: 'tutor-bg-surface-l1', color: '#fafafa', description: 'Cards, modals' },
  { name: 'Level 2 Surface', class: 'tutor-bg-surface-l2', color: '#f5f5f6', description: 'Input backgrounds' },
  { name: 'Brand 100', class: 'tutor-bg-brand-100', color: '#f6f8fe', description: 'Light brand background' },
  { name: 'Brand 500', class: 'tutor-bg-brand-500', color: '#4979e8', description: 'Primary brand background' },
  { name: 'Success 100', class: 'tutor-bg-success-100', color: '#e3fbcc', description: 'Light success background' },
  { name: 'Warning 100', class: 'tutor-bg-warning-100', color: '#fef0c7', description: 'Light warning background' },
  { name: 'Error 100', class: 'tutor-bg-error-100', color: '#fee4e2', description: 'Light error background' },
];

const ColorUtilityExample = ({ 
  name, 
  className, 
  color, 
  description, 
  type = 'text' 
}: { 
  name: string; 
  className: string; 
  color: string; 
  description: string;
  type?: 'text' | 'background' | 'border';
}) => (
  <div css={css`
    display: flex;
    align-items: center;
    padding: 12px;
    margin-bottom: 8px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
  `}>
    <div css={css`
      width: 48px;
      height: 48px;
      border-radius: 6px;
      margin-right: 16px;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: 600;
      ${type === 'text' ? `
        background: #f8f9fa;
        color: ${color};
      ` : type === 'background' ? `
        background: ${color};
        color: ${color === '#ffffff' || color === '#fafafa' || color === '#f5f5f6' ? '#333' : '#fff'};
        border: 1px solid #e0e0e0;
      ` : `
        background: white;
        border: 2px solid ${color};
        color: #333;
      `}
    `}>
      {type === 'text' ? 'Aa' : type === 'background' ? 'BG' : 'BR'}
    </div>
    <div css={css`flex: 1;`}>
      <div css={css`
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
      `}>
        {name}
      </div>
      <div css={css`
        font-size: 12px;
        color: #666;
        margin-bottom: 4px;
      `}>
        {description}
      </div>
      <div css={css`
        font-size: 11px;
        font-family: monospace;
        color: #888;
        background: #f5f5f5;
        padding: 2px 6px;
        border-radius: 3px;
        display: inline-block;
      `}>
        .{className}
      </div>
    </div>
  </div>
);

export const TextColorUtilities: Story = {
  render: () => (
    <div>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600;`}>
        Text Color Utilities
      </h2>
      <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 16px;`}>
        {textColors.map((color) => (
          <ColorUtilityExample
            key={color.class}
            name={color.name}
            className={color.class}
            color={color.color}
            description={color.description}
            type="text"
          />
        ))}
      </div>
      
      <div css={css`
        margin-top: 24px;
        padding: 16px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
      `}>
        <h3 css={css`margin: 0 0 12px 0; font-size: 16px; font-weight: 600;`}>
          Text Color Examples
        </h3>
        <div css={css`display: flex; flex-direction: column; gap: 8px;`}>
          <p css={css`margin: 0; color: #333741;`}>
            <span css={css`font-weight: 600;`}>Primary text:</span> This is the main text color used for headings and body content.
          </p>
          <p css={css`margin: 0; color: #61646c;`}>
            <span css={css`font-weight: 600;`}>Secondary text:</span> This is used for supporting information and descriptions.
          </p>
          <p css={css`margin: 0; color: #94969c;`}>
            <span css={css`font-weight: 600;`}>Disabled text:</span> This represents disabled or inactive content.
          </p>
          <p css={css`margin: 0; color: #4979e8;`}>
            <span css={css`font-weight: 600;`}>Brand text:</span> This is used for links and brand-related content.
          </p>
          <p css={css`margin: 0; color: #2b5314;`}>
            <span css={css`font-weight: 600;`}>Success text:</span> This indicates successful operations or positive feedback.
          </p>
          <p css={css`margin: 0; color: #7a2e0e;`}>
            <span css={css`font-weight: 600;`}>Warning text:</span> This is used for warnings and caution messages.
          </p>
          <p css={css`margin: 0; color: #7a271a;`}>
            <span css={css`font-weight: 600;`}>Error text:</span> This indicates errors or critical issues.
          </p>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Text color utilities for different content types and semantic meanings.',
      },
    },
  },
};

export const BackgroundColorUtilities: Story = {
  render: () => (
    <div>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600;`}>
        Background Color Utilities
      </h2>
      <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 16px;`}>
        {backgroundColors.map((color) => (
          <ColorUtilityExample
            key={color.class}
            name={color.name}
            className={color.class}
            color={color.color}
            description={color.description}
            type="background"
          />
        ))}
      </div>
      
      <div css={css`margin-top: 24px;`}>
        <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
          Background Color Examples
        </h3>
        <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;`}>
          
          <div css={css`
            padding: 16px;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
          `}>
            <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
              Base Surface
            </h4>
            <p css={css`margin: 0; font-size: 12px; color: #666;`}>
              .tutor-bg-surface-base
            </p>
          </div>

          <div css={css`
            padding: 16px;
            background: #fafafa;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
          `}>
            <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
              Level 1 Surface
            </h4>
            <p css={css`margin: 0; font-size: 12px; color: #666;`}>
              .tutor-bg-surface-l1
            </p>
          </div>

          <div css={css`
            padding: 16px;
            background: #f6f8fe;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
          `}>
            <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
              Brand Light
            </h4>
            <p css={css`margin: 0; font-size: 12px; color: #666;`}>
              .tutor-bg-brand-100
            </p>
          </div>

          <div css={css`
            padding: 16px;
            background: #4979e8;
            color: white;
            border-radius: 6px;
          `}>
            <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
              Brand Primary
            </h4>
            <p css={css`margin: 0; font-size: 12px; opacity: 0.8;`}>
              .tutor-bg-brand-500
            </p>
          </div>

          <div css={css`
            padding: 16px;
            background: #e3fbcc;
            border: 1px solid #a6ef67;
            border-radius: 6px;
          `}>
            <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: #2b5314;`}>
              Success Light
            </h4>
            <p css={css`margin: 0; font-size: 12px; color: #2b5314;`}>
              .tutor-bg-success-100
            </p>
          </div>

          <div css={css`
            padding: 16px;
            background: #fee4e2;
            border: 1px solid #fda29b;
            border-radius: 6px;
          `}>
            <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: #7a271a;`}>
              Error Light
            </h4>
            <p css={css`margin: 0; font-size: 12px; color: #7a271a;`}>
              .tutor-bg-error-100
            </p>
          </div>

        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Background color utilities for surfaces, cards, and semantic backgrounds.',
      },
    },
  },
};

export const SemanticColorExamples: Story = {
  render: () => (
    <div>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600;`}>
        Semantic Color Usage
      </h2>
      
      <div css={css`display: flex; flex-direction: column; gap: 16px;`}>
        
        {/* Success Example */}
        <div css={css`
          padding: 16px;
          background: #e3fbcc;
          border: 1px solid #a6ef67;
          border-radius: 8px;
          border-left: 4px solid #66c61c;
        `}>
          <div css={css`display: flex; align-items: center; margin-bottom: 8px;`}>
            <svg width="20" height="20" viewBox="0 0 20 20" fill="#2b5314" css={css`margin-right: 8px;`}>
              <path d="M10 0a10 10 0 100 20 10 10 0 000-20zm4 7l-5 5-3-3 1.5-1.5L9 9l3.5-3.5L14 7z"/>
            </svg>
            <h3 css={css`margin: 0; font-size: 16px; font-weight: 600; color: #2b5314;`}>
              Success Message
            </h3>
          </div>
          <p css={css`margin: 0; color: #2b5314; font-size: 14px;`}>
            Your changes have been saved successfully. The system is now updated with your preferences.
          </p>
        </div>

        {/* Warning Example */}
        <div css={css`
          padding: 16px;
          background: #fef0c7;
          border: 1px solid #fedf89;
          border-radius: 8px;
          border-left: 4px solid #f79009;
        `}>
          <div css={css`display: flex; align-items: center; margin-bottom: 8px;`}>
            <svg width="20" height="20" viewBox="0 0 20 20" fill="#7a2e0e" css={css`margin-right: 8px;`}>
              <path d="M10 0L0 18h20L10 0zm0 6l1 6H9l1-6zm0 8a1 1 0 100 2 1 1 0 000-2z"/>
            </svg>
            <h3 css={css`margin: 0; font-size: 16px; font-weight: 600; color: #7a2e0e;`}>
              Warning Notice
            </h3>
          </div>
          <p css={css`margin: 0; color: #7a2e0e; font-size: 14px;`}>
            Please review your input before proceeding. Some fields may require additional validation.
          </p>
        </div>

        {/* Error Example */}
        <div css={css`
          padding: 16px;
          background: #fee4e2;
          border: 1px solid #fda29b;
          border-radius: 8px;
          border-left: 4px solid #f04438;
        `}>
          <div css={css`display: flex; align-items: center; margin-bottom: 8px;`}>
            <svg width="20" height="20" viewBox="0 0 20 20" fill="#7a271a" css={css`margin-right: 8px;`}>
              <path d="M10 0a10 10 0 100 20 10 10 0 000-20zM9 5h2v6H9V5zm0 7h2v2H9v-2z"/>
            </svg>
            <h3 css={css`margin: 0; font-size: 16px; font-weight: 600; color: #7a271a;`}>
              Error Occurred
            </h3>
          </div>
          <p css={css`margin: 0; color: #7a271a; font-size: 14px;`}>
            Unable to process your request. Please check your connection and try again.
          </p>
        </div>

        {/* Info Example */}
        <div css={css`
          padding: 16px;
          background: #f6f8fe;
          border: 1px solid #dbe4fa;
          border-radius: 8px;
          border-left: 4px solid #4979e8;
        `}>
          <div css={css`display: flex; align-items: center; margin-bottom: 8px;`}>
            <svg width="20" height="20" viewBox="0 0 20 20" fill="#4979e8" css={css`margin-right: 8px;`}>
              <path d="M10 0a10 10 0 100 20 10 10 0 000-20zm0 6a1 1 0 100-2 1 1 0 000 2zm1 2H9v6h2V8z"/>
            </svg>
            <h3 css={css`margin: 0; font-size: 16px; font-weight: 600; color: #4979e8;`}>
              Information
            </h3>
          </div>
          <p css={css`margin: 0; color: #4979e8; font-size: 14px;`}>
            This feature is currently in beta. Your feedback helps us improve the experience.
          </p>
        </div>

      </div>

      <div css={css`
        margin-top: 24px;
        padding: 16px;
        background: #f0f8ff;
        border-radius: 8px;
        border-left: 4px solid #4979e8;
      `}>
        <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
          Color Usage Guidelines
        </h4>
        <ul css={css`margin: 0; padding-left: 16px; font-size: 12px; color: #666;`}>
          <li>Use semantic colors consistently across your application</li>
          <li>Combine background and text colors from the same semantic family</li>
          <li>Ensure sufficient contrast ratios for accessibility</li>
          <li>Test colors in both light and dark themes</li>
        </ul>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Real-world examples of semantic color usage in messages, alerts, and notifications.',
      },
    },
  },
};