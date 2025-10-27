import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';

const meta: Meta = {
  title: 'TutorCore/Tokens/Spacing',
  parameters: {
    docs: {
      description: {
        component: `
# Spacing Tokens

TutorCore uses a 22-step spacing scale (0-21) that provides consistent spacing throughout the design system. The scale ranges from 0px to 200px with carefully chosen increments.

## Spacing Scale

The spacing system follows a logical progression:
- **0-4**: Fine adjustments (0px, 2px, 4px, 6px, 8px)
- **5-10**: Common spacing (12px, 16px, 20px, 24px, 28px, 32px)
- **11-16**: Larger spacing (36px, 40px, 44px, 48px, 56px, 64px)
- **17-21**: Section spacing (80px, 96px, 112px, 128px, 200px)

## RTL Support

All spacing utilities are RTL-aware and automatically adapt to text direction.
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;

// Spacing scale based on the design tokens
const spacingScale = [
  { step: 0, value: '0px', pixels: 0 },
  { step: 1, value: '2px', pixels: 2 },
  { step: 2, value: '4px', pixels: 4 },
  { step: 3, value: '6px', pixels: 6 },
  { step: 4, value: '8px', pixels: 8 },
  { step: 5, value: '12px', pixels: 12 },
  { step: 6, value: '16px', pixels: 16 },
  { step: 7, value: '20px', pixels: 20 },
  { step: 8, value: '24px', pixels: 24 },
  { step: 9, value: '28px', pixels: 28 },
  { step: 10, value: '32px', pixels: 32 },
  { step: 11, value: '36px', pixels: 36 },
  { step: 12, value: '40px', pixels: 40 },
  { step: 13, value: '44px', pixels: 44 },
  { step: 14, value: '48px', pixels: 48 },
  { step: 15, value: '56px', pixels: 56 },
  { step: 16, value: '64px', pixels: 64 },
  { step: 17, value: '80px', pixels: 80 },
  { step: 18, value: '96px', pixels: 96 },
  { step: 19, value: '112px', pixels: 112 },
  { step: 20, value: '128px', pixels: 128 },
  { step: 21, value: '200px', pixels: 200 },
];

const SpacingExample = ({ step, value, pixels }: { step: number; value: string; pixels: number }) => (
  <div
    css={css`
      display: flex;
      align-items: center;
      margin-bottom: 12px;
      padding: 8px;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
      background: #fafafa;
    `}
  >
    <div
      css={css`
        width: 60px;
        font-size: 12px;
        font-weight: 600;
        color: #333;
      `}
    >
      {step}
    </div>
    <div
      css={css`
        width: 80px;
        font-size: 12px;
        font-family: monospace;
        color: #666;
      `}
    >
      {value}
    </div>
    <div
      css={css`
        flex: 1;
        height: 20px;
        background: linear-gradient(90deg, #4979e8 0%, #4979e8 ${pixels}px, #e0e0e0 ${pixels}px);
        border-radius: 2px;
        position: relative;
        margin-right: 12px;
      `}
    >
      {pixels > 0 && (
        <div
          css={css`
            position: absolute;
            top: 0;
            left: 0;
            width: ${pixels}px;
            height: 100%;
            background: #4979e8;
            border-radius: 2px;
            max-width: 100%;
          `}
        />
      )}
    </div>
    <div
      css={css`
        width: 120px;
        font-size: 11px;
        font-family: monospace;
        color: #888;
        text-align: right;
      `}
    >
      .tutor-m-{step}
    </div>
  </div>
);

const SpacingUtilityExample = ({ type, description }: { type: string; description: string }) => (
  <div
    css={css`
      margin-bottom: 16px;
      padding: 12px;
      border: 1px solid #e0e0e0;
      border-radius: 6px;
      background: #f9f9f9;
    `}
  >
    <div
      css={css`
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 4px;
        color: #333;
      `}
    >
      {type}
    </div>
    <div
      css={css`
        font-size: 12px;
        color: #666;
        margin-bottom: 8px;
      `}
    >
      {description}
    </div>
    <div
      css={css`
        font-size: 11px;
        font-family: monospace;
        color: #888;
        background: #fff;
        padding: 4px 6px;
        border-radius: 3px;
        display: inline-block;
      `}
    >
      .tutor-{type.toLowerCase()}-{'{size}'}
    </div>
  </div>
);

export const SpacingScale: Story = {
  render: () => (
    <div>
      <h2 css={css`margin-bottom: 24px; font-size: 20px; font-weight: 600;`}>
        Spacing Scale (0-21)
      </h2>
      <div css={css`margin-bottom: 16px;`}>
        <div
          css={css`
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            padding: 8px;
            background: #f0f0f0;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            color: #333;
          `}
        >
          <div css={css`width: 60px;`}>Step</div>
          <div css={css`width: 80px;`}>Value</div>
          <div css={css`flex: 1; margin-right: 12px;`}>Visual Scale</div>
          <div css={css`width: 120px; text-align: right;`}>CSS Class</div>
        </div>
        {spacingScale.map((spacing) => (
          <SpacingExample
            key={spacing.step}
            step={spacing.step}
            value={spacing.value}
            pixels={spacing.pixels}
          />
        ))}
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Complete spacing scale showing all 22 steps with visual representation and corresponding CSS classes.',
      },
    },
  },
};

export const MarginUtilities: Story = {
  render: () => (
    <div>
      <h2 css={css`margin-bottom: 24px; font-size: 20px; font-weight: 600;`}>
        Margin Utilities
      </h2>
      <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;`}>
        <SpacingUtilityExample type="M" description="Margin on all sides" />
        <SpacingUtilityExample type="MT" description="Margin top" />
        <SpacingUtilityExample type="ME" description="Margin end (RTL-aware right/left)" />
        <SpacingUtilityExample type="MB" description="Margin bottom" />
        <SpacingUtilityExample type="MS" description="Margin start (RTL-aware left/right)" />
        <SpacingUtilityExample type="MX" description="Horizontal margin (left and right)" />
        <SpacingUtilityExample type="MY" description="Vertical margin (top and bottom)" />
      </div>
      
      <div
        css={css`
          margin-top: 24px;
          padding: 16px;
          background: #f0f8ff;
          border-radius: 8px;
          border-left: 4px solid #4979e8;
        `}
      >
        <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
          RTL Support
        </h4>
        <p css={css`margin: 0; font-size: 12px; color: #666;`}>
          <code>MS</code> (margin-start) and <code>ME</code> (margin-end) automatically adapt to text direction.
          In LTR: MS = left, ME = right. In RTL: MS = right, ME = left.
        </p>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Margin utility classes with RTL-aware directional properties.',
      },
    },
  },
};

export const PaddingUtilities: Story = {
  render: () => (
    <div>
      <h2 css={css`margin-bottom: 24px; font-size: 20px; font-weight: 600;`}>
        Padding Utilities
      </h2>
      <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;`}>
        <SpacingUtilityExample type="P" description="Padding on all sides" />
        <SpacingUtilityExample type="PT" description="Padding top" />
        <SpacingUtilityExample type="PE" description="Padding end (RTL-aware right/left)" />
        <SpacingUtilityExample type="PB" description="Padding bottom" />
        <SpacingUtilityExample type="PS" description="Padding start (RTL-aware left/right)" />
        <SpacingUtilityExample type="PX" description="Horizontal padding (left and right)" />
        <SpacingUtilityExample type="PY" description="Vertical padding (top and bottom)" />
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Padding utility classes with RTL-aware directional properties.',
      },
    },
  },
};

export const SpacingExamples: Story = {
  render: () => (
    <div>
      <h2 css={css`margin-bottom: 24px; font-size: 20px; font-weight: 600;`}>
        Practical Spacing Examples
      </h2>
      
      {/* Card with spacing */}
      <div
        css={css`
          background: white;
          border: 1px solid #e0e0e0;
          border-radius: 8px;
          padding: 24px; /* tutor-p-8 */
          margin-bottom: 32px; /* tutor-mb-10 */
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        `}
      >
        <h3
          css={css`
            margin: 0 0 16px 0; /* tutor-mb-6 */
            font-size: 20px;
            font-weight: 600;
          `}
        >
          Card Example
        </h3>
        <p
          css={css`
            margin: 0 0 12px 0; /* tutor-mb-5 */
            color: #666;
            line-height: 1.5;
          `}
        >
          This card demonstrates practical spacing usage with padding of 24px (tutor-p-8) 
          and margin-bottom of 32px (tutor-mb-10).
        </p>
        <div
          css={css`
            display: flex;
            gap: 12px; /* tutor-gap-5 */
            margin-top: 20px; /* tutor-mt-7 */
          `}
        >
          <button
            css={css`
              padding: 8px 16px; /* tutor-py-4 tutor-px-6 */
              background: #4979e8;
              color: white;
              border: none;
              border-radius: 6px;
              font-size: 14px;
              cursor: pointer;
            `}
          >
            Primary Action
          </button>
          <button
            css={css`
              padding: 8px 16px; /* tutor-py-4 tutor-px-6 */
              background: transparent;
              color: #4979e8;
              border: 1px solid #4979e8;
              border-radius: 6px;
              font-size: 14px;
              cursor: pointer;
            `}
          >
            Secondary Action
          </button>
        </div>
      </div>

      {/* List with spacing */}
      <div
        css={css`
          background: white;
          border: 1px solid #e0e0e0;
          border-radius: 8px;
          padding: 20px; /* tutor-p-7 */
          margin-bottom: 32px; /* tutor-mb-10 */
        `}
      >
        <h3
          css={css`
            margin: 0 0 16px 0; /* tutor-mb-6 */
            font-size: 18px;
            font-weight: 600;
          `}
        >
          List with Consistent Spacing
        </h3>
        <ul
          css={css`
            margin: 0;
            padding: 0;
            list-style: none;
          `}
        >
          {['First item', 'Second item', 'Third item', 'Fourth item'].map((item, index) => (
            <li
              key={item}
              css={css`
                padding: 12px 0; /* tutor-py-5 */
                border-bottom: ${index < 3 ? '1px solid #f0f0f0' : 'none'};
                font-size: 14px;
              `}
            >
              {item}
            </li>
          ))}
        </ul>
      </div>

      {/* Grid with spacing */}
      <div
        css={css`
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 24px; /* tutor-gap-8 */
          margin-bottom: 32px; /* tutor-mb-10 */
        `}
      >
        {[1, 2, 3].map((num) => (
          <div
            key={num}
            css={css`
              background: white;
              border: 1px solid #e0e0e0;
              border-radius: 8px;
              padding: 16px; /* tutor-p-6 */
              text-align: center;
            `}
          >
            <h4
              css={css`
                margin: 0 0 8px 0; /* tutor-mb-4 */
                font-size: 16px;
                font-weight: 600;
              `}
            >
              Grid Item {num}
            </h4>
            <p
              css={css`
                margin: 0;
                font-size: 14px;
                color: #666;
              `}
            >
              Grid with 24px gap (tutor-gap-8)
            </p>
          </div>
        ))}
      </div>

      <div
        css={css`
          background: #f0f8ff;
          border: 1px solid #4979e8;
          border-radius: 8px;
          padding: 16px;
        `}
      >
        <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
          Usage Tips
        </h4>
        <ul css={css`margin: 0; padding-left: 16px; font-size: 12px; color: #666;`}>
          <li>Use smaller spacing (0-4) for fine adjustments</li>
          <li>Use medium spacing (5-10) for component internal spacing</li>
          <li>Use larger spacing (11-16) for component separation</li>
          <li>Use section spacing (17-21) for major layout sections</li>
        </ul>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Real-world examples showing how to use spacing tokens effectively in component layouts.',
      },
    },
  },
};