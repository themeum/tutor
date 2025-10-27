import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';

const meta: Meta = {
  title: 'TutorCore/Tokens/BorderRadius',
  parameters: {
    docs: {
      description: {
        component: `
# Border Radius Tokens

TutorCore provides a comprehensive set of border radius tokens for creating consistent rounded corners throughout the design system. The scale ranges from sharp corners to fully rounded elements.

## Border Radius Scale

The system includes semantic naming and pixel values:
- **None**: 0px - Sharp corners
- **Small**: 2px, 4px - Subtle rounding
- **Medium**: 6px, 8px - Standard rounding
- **Large**: 12px, 16px - Prominent rounding
- **Extra Large**: 20px, 24px - Bold rounding
- **Full**: 1000px - Fully rounded (pills, circles)

## Usage

Border radius tokens can be used as CSS classes or SASS variables in your components.
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;

// Border radius tokens based on the design system
const borderRadiusTokens = [
  { name: 'None', value: '0px', class: 'tutor-rounded-none', description: 'Sharp corners, no rounding' },
  { name: 'XS', value: '2px', class: 'tutor-rounded-xs', description: 'Very subtle rounding' },
  { name: 'SM', value: '4px', class: 'tutor-rounded-sm', description: 'Small rounding for buttons, inputs' },
  { name: 'Base', value: '6px', class: 'tutor-rounded', description: 'Default rounding for most elements' },
  { name: 'MD', value: '8px', class: 'tutor-rounded-md', description: 'Medium rounding for cards, modals' },
  { name: 'LG', value: '12px', class: 'tutor-rounded-lg', description: 'Large rounding for prominent elements' },
  { name: 'XL', value: '16px', class: 'tutor-rounded-xl', description: 'Extra large rounding' },
  { name: '2XL', value: '20px', class: 'tutor-rounded-2xl', description: 'Very large rounding' },
  { name: '3XL', value: '24px', class: 'tutor-rounded-3xl', description: 'Maximum standard rounding' },
  { name: 'Full', value: '1000px', class: 'tutor-rounded-full', description: 'Fully rounded (pills, circles)' },
];

const BorderRadiusExample = ({ 
  name, 
  value, 
  className, 
  description 
}: { 
  name: string; 
  value: string; 
  className: string; 
  description: string;
}) => (
  <div
    css={css`
      display: flex;
      align-items: center;
      margin-bottom: 16px;
      padding: 16px;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      background: #fafafa;
    `}
  >
    <div
      css={css`
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #4979e8, #3e64de);
        border-radius: ${value};
        margin-right: 20px;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(73, 121, 232, 0.2);
      `}
    />
    <div css={css`flex: 1;`}>
      <div
        css={css`
          font-size: 16px;
          font-weight: 600;
          margin-bottom: 4px;
          color: #333;
        `}
      >
        {name}
      </div>
      <div
        css={css`
          font-size: 14px;
          color: #666;
          margin-bottom: 8px;
        `}
      >
        {description}
      </div>
      <div
        css={css`
          display: flex;
          gap: 12px;
          font-size: 12px;
          font-family: monospace;
        `}
      >
        <span
          css={css`
            background: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            color: #666;
          `}
        >
          {value}
        </span>
        <span
          css={css`
            background: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            color: #888;
          `}
        >
          .{className}
        </span>
      </div>
    </div>
  </div>
);

export const BorderRadiusScale: Story = {
  render: () => (
    <div>
      <h2 css={css`margin-bottom: 24px; font-size: 20px; font-weight: 600;`}>
        Border Radius Scale
      </h2>
      {borderRadiusTokens.map((token) => (
        <BorderRadiusExample
          key={token.name}
          name={token.name}
          value={token.value}
          className={token.class}
          description={token.description}
        />
      ))}
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Complete border radius scale showing all available tokens with visual examples.',
      },
    },
  },
};

export const ComponentExamples: Story = {
  render: () => (
    <div>
      <h2 css={css`margin-bottom: 24px; font-size: 20px; font-weight: 600;`}>
        Component Examples
      </h2>
      
      <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;`}>
        
        {/* Buttons */}
        <div
          css={css`
            padding: 20px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
          `}
        >
          <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
            Buttons
          </h3>
          <div css={css`display: flex; flex-direction: column; gap: 12px;`}>
            <button
              css={css`
                padding: 8px 16px;
                background: #4979e8;
                color: white;
                border: none;
                border-radius: 4px; /* tutor-rounded-sm */
                font-size: 14px;
                cursor: pointer;
              `}
            >
              Small Radius (4px)
            </button>
            <button
              css={css`
                padding: 10px 20px;
                background: #4979e8;
                color: white;
                border: none;
                border-radius: 8px; /* tutor-rounded-md */
                font-size: 14px;
                cursor: pointer;
              `}
            >
              Medium Radius (8px)
            </button>
            <button
              css={css`
                padding: 12px 24px;
                background: #4979e8;
                color: white;
                border: none;
                border-radius: 1000px; /* tutor-rounded-full */
                font-size: 14px;
                cursor: pointer;
              `}
            >
              Pill Button (Full)
            </button>
          </div>
        </div>

        {/* Cards */}
        <div
          css={css`
            padding: 20px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
          `}
        >
          <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
            Cards
          </h3>
          <div css={css`display: flex; flex-direction: column; gap: 12px;`}>
            <div
              css={css`
                padding: 16px;
                background: #f8f9fa;
                border: 1px solid #e0e0e0;
                border-radius: 6px; /* tutor-rounded */
                font-size: 14px;
              `}
            >
              Default Card (6px)
            </div>
            <div
              css={css`
                padding: 16px;
                background: #f8f9fa;
                border: 1px solid #e0e0e0;
                border-radius: 12px; /* tutor-rounded-lg */
                font-size: 14px;
              `}
            >
              Large Card (12px)
            </div>
            <div
              css={css`
                padding: 16px;
                background: #f8f9fa;
                border: 1px solid #e0e0e0;
                border-radius: 20px; /* tutor-rounded-2xl */
                font-size: 14px;
              `}
            >
              Extra Large Card (20px)
            </div>
          </div>
        </div>

        {/* Form Elements */}
        <div
          css={css`
            padding: 20px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
          `}
        >
          <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
            Form Elements
          </h3>
          <div css={css`display: flex; flex-direction: column; gap: 12px;`}>
            <input
              type="text"
              placeholder="Small radius input"
              css={css`
                padding: 8px 12px;
                border: 1px solid #ccc;
                border-radius: 4px; /* tutor-rounded-sm */
                font-size: 14px;
                width: 100%;
              `}
            />
            <input
              type="text"
              placeholder="Medium radius input"
              css={css`
                padding: 10px 14px;
                border: 1px solid #ccc;
                border-radius: 8px; /* tutor-rounded-md */
                font-size: 14px;
                width: 100%;
              `}
            />
            <textarea
              placeholder="Large radius textarea"
              css={css`
                padding: 12px;
                border: 1px solid #ccc;
                border-radius: 12px; /* tutor-rounded-lg */
                font-size: 14px;
                width: 100%;
                height: 80px;
                resize: vertical;
              `}
            />
          </div>
        </div>

        {/* Badges and Tags */}
        <div
          css={css`
            padding: 20px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
          `}
        >
          <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
            Badges & Tags
          </h3>
          <div css={css`display: flex; flex-wrap: wrap; gap: 8px;`}>
            <span
              css={css`
                padding: 4px 8px;
                background: #e3fbcc;
                color: #2b5314;
                border-radius: 2px; /* tutor-rounded-xs */
                font-size: 12px;
                font-weight: 500;
              `}
            >
              Sharp Badge
            </span>
            <span
              css={css`
                padding: 4px 12px;
                background: #fee4e2;
                color: #7a271a;
                border-radius: 6px; /* tutor-rounded */
                font-size: 12px;
                font-weight: 500;
              `}
            >
              Default Badge
            </span>
            <span
              css={css`
                padding: 6px 12px;
                background: #f0f8ff;
                color: #1c234f;
                border-radius: 1000px; /* tutor-rounded-full */
                font-size: 12px;
                font-weight: 500;
              `}
            >
              Pill Badge
            </span>
          </div>
        </div>

      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Practical examples showing how border radius tokens are used in different UI components.',
      },
    },
  },
};

export const ResponsiveRadius: Story = {
  render: () => (
    <div>
      <h2 css={css`margin-bottom: 24px; font-size: 20px; font-weight: 600;`}>
        Responsive Border Radius
      </h2>
      
      <p css={css`margin-bottom: 24px; color: #666; font-size: 14px;`}>
        Border radius can be adjusted for different screen sizes to maintain visual balance.
      </p>

      <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;`}>
        
        <div
          css={css`
            padding: 20px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
          `}
        >
          <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
            Mobile
          </h4>
          <div
            css={css`
              width: 100px;
              height: 100px;
              background: linear-gradient(135deg, #4979e8, #3e64de);
              border-radius: 8px; /* Smaller radius for mobile */
              margin: 0 auto 12px auto;
            `}
          />
          <p css={css`margin: 0; font-size: 12px; color: #666;`}>
            8px radius for mobile screens
          </p>
        </div>

        <div
          css={css`
            padding: 20px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
          `}
        >
          <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
            Tablet
          </h4>
          <div
            css={css`
              width: 100px;
              height: 100px;
              background: linear-gradient(135deg, #4979e8, #3e64de);
              border-radius: 12px; /* Medium radius for tablet */
              margin: 0 auto 12px auto;
            `}
          />
          <p css={css`margin: 0; font-size: 12px; color: #666;`}>
            12px radius for tablet screens
          </p>
        </div>

        <div
          css={css`
            padding: 20px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
          `}
        >
          <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
            Desktop
          </h4>
          <div
            css={css`
              width: 100px;
              height: 100px;
              background: linear-gradient(135deg, #4979e8, #3e64de);
              border-radius: 16px; /* Larger radius for desktop */
              margin: 0 auto 12px auto;
            `}
          />
          <p css={css`margin: 0; font-size: 12px; color: #666;`}>
            16px radius for desktop screens
          </p>
        </div>

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
          Best Practices
        </h4>
        <ul css={css`margin: 0; padding-left: 16px; font-size: 12px; color: #666;`}>
          <li>Use smaller radius (2-6px) for small elements like buttons and inputs</li>
          <li>Use medium radius (8-12px) for cards and containers</li>
          <li>Use large radius (16-24px) for prominent elements and hero sections</li>
          <li>Use full radius (1000px) for pills, badges, and circular elements</li>
          <li>Consider reducing radius on smaller screens for better touch targets</li>
        </ul>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Examples of how border radius can be adapted for different screen sizes and use cases.',
      },
    },
  },
};