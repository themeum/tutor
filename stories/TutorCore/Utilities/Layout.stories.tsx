import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';

const meta: Meta = {
  title: 'TutorCore/Utilities/Layout',
  parameters: {
    docs: {
      description: {
        component: `
# Layout Utilities

TutorCore provides a comprehensive set of layout utilities for creating flexible and responsive layouts. All utilities are RTL-aware and work seamlessly across different screen sizes.

## Features

- **Flexbox Utilities**: Complete flexbox layout system
- **Grid Utilities**: CSS Grid layout helpers
- **Container System**: Responsive containers with max-widths
- **Display Utilities**: Show/hide elements responsively
- **Position Utilities**: Positioning and alignment helpers
- **RTL Support**: All utilities adapt to text direction

## CSS Classes

\`\`\`css
/* Container */
.tutor-container

/* Flexbox */
.tutor-flex
.tutor-flex-center
.tutor-flex-between
.tutor-flex-around
.tutor-flex-column
.tutor-flex-wrap

/* Grid */
.tutor-grid
.tutor-grid-cols-{n}

/* Display */
.tutor-block
.tutor-inline
.tutor-inline-block
.tutor-hidden

/* Responsive */
.tutor-md-flex
.tutor-lg-hidden
\`\`\`
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;

const demoStyles = {
  box: css`
    background: linear-gradient(135deg, #4979e8, #3e64de);
    color: white;
    padding: 16px;
    border-radius: 8px;
    text-align: center;
    font-size: 14px;
    font-weight: 500;
    min-height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
  `,
  
  container: css`
    background: #f8f9fa;
    border: 2px dashed #e0e0e0;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
  `,
  
  label: css`
    font-size: 12px;
    font-weight: 600;
    color: #666;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  `,
};

export const Container: Story = {
  render: () => (
    <div>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600;`}>
        Container System
      </h2>
      
      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Default Container (.tutor-container)</div>
        <div css={css`
          max-width: 1200px;
          margin: 0 auto;
          padding: 0 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <div css={[demoStyles.box, css`background: #f0f8ff; color: #333;`]}>
            Responsive container with max-width: 1200px
          </div>
        </div>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Small Container (.tutor-container-sm)</div>
        <div css={css`
          max-width: 640px;
          margin: 0 auto;
          padding: 0 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <div css={[demoStyles.box, css`background: #f0f8ff; color: #333;`]}>
            Small container with max-width: 640px
          </div>
        </div>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Large Container (.tutor-container-lg)</div>
        <div css={css`
          max-width: 1440px;
          margin: 0 auto;
          padding: 0 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <div css={[demoStyles.box, css`background: #f0f8ff; color: #333;`]}>
            Large container with max-width: 1440px
          </div>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Responsive container system with different max-widths for various layout needs.',
      },
    },
  },
};

export const FlexboxUtilities: Story = {
  render: () => (
    <div>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600;`}>
        Flexbox Utilities
      </h2>
      
      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Basic Flex (.tutor-flex)</div>
        <div css={css`display: flex; gap: 12px;`}>
          <div css={demoStyles.box}>Item 1</div>
          <div css={demoStyles.box}>Item 2</div>
          <div css={demoStyles.box}>Item 3</div>
        </div>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Flex Center (.tutor-flex-center)</div>
        <div css={css`
          display: flex;
          align-items: center;
          justify-content: center;
          min-height: 120px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <div css={demoStyles.box}>Centered Content</div>
        </div>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Space Between (.tutor-flex-between)</div>
        <div css={css`
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <div css={demoStyles.box}>Left</div>
          <div css={demoStyles.box}>Center</div>
          <div css={demoStyles.box}>Right</div>
        </div>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Space Around (.tutor-flex-around)</div>
        <div css={css`
          display: flex;
          justify-content: space-around;
          align-items: center;
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <div css={demoStyles.box}>Item 1</div>
          <div css={demoStyles.box}>Item 2</div>
          <div css={demoStyles.box}>Item 3</div>
        </div>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Flex Column (.tutor-flex-column)</div>
        <div css={css`
          display: flex;
          flex-direction: column;
          gap: 12px;
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <div css={demoStyles.box}>Top Item</div>
          <div css={demoStyles.box}>Middle Item</div>
          <div css={demoStyles.box}>Bottom Item</div>
        </div>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Flex Wrap (.tutor-flex-wrap)</div>
        <div css={css`
          display: flex;
          flex-wrap: wrap;
          gap: 12px;
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          {Array.from({ length: 8 }, (_, i) => (
            <div key={i} css={[demoStyles.box, css`min-width: 120px;`]}>
              Item {i + 1}
            </div>
          ))}
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Comprehensive flexbox utilities for creating flexible layouts with proper alignment and distribution.',
      },
    },
  },
};

export const GridUtilities: Story = {
  render: () => (
    <div>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600;`}>
        Grid Utilities
      </h2>
      
      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Basic Grid (.tutor-grid)</div>
        <div css={css`
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
          gap: 16px;
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <div css={demoStyles.box}>Grid Item 1</div>
          <div css={demoStyles.box}>Grid Item 2</div>
          <div css={demoStyles.box}>Grid Item 3</div>
          <div css={demoStyles.box}>Grid Item 4</div>
        </div>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>2 Columns (.tutor-grid-cols-2)</div>
        <div css={css`
          display: grid;
          grid-template-columns: repeat(2, 1fr);
          gap: 16px;
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <div css={demoStyles.box}>Column 1</div>
          <div css={demoStyles.box}>Column 2</div>
          <div css={demoStyles.box}>Column 3</div>
          <div css={demoStyles.box}>Column 4</div>
        </div>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>3 Columns (.tutor-grid-cols-3)</div>
        <div css={css`
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          gap: 16px;
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <div css={demoStyles.box}>Col 1</div>
          <div css={demoStyles.box}>Col 2</div>
          <div css={demoStyles.box}>Col 3</div>
          <div css={demoStyles.box}>Col 4</div>
          <div css={demoStyles.box}>Col 5</div>
          <div css={demoStyles.box}>Col 6</div>
        </div>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>4 Columns (.tutor-grid-cols-4)</div>
        <div css={css`
          display: grid;
          grid-template-columns: repeat(4, 1fr);
          gap: 16px;
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          {Array.from({ length: 8 }, (_, i) => (
            <div key={i} css={demoStyles.box}>
              {i + 1}
            </div>
          ))}
        </div>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Grid with Span (.tutor-col-span-2)</div>
        <div css={css`
          display: grid;
          grid-template-columns: repeat(4, 1fr);
          gap: 16px;
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <div css={[demoStyles.box, css`grid-column: span 2;`]}>
            Spans 2 Columns
          </div>
          <div css={demoStyles.box}>Regular</div>
          <div css={demoStyles.box}>Regular</div>
          <div css={demoStyles.box}>Regular</div>
          <div css={[demoStyles.box, css`grid-column: span 3;`]}>
            Spans 3 Columns
          </div>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'CSS Grid utilities for creating structured layouts with columns and spanning capabilities.',
      },
    },
  },
};

export const DisplayUtilities: Story = {
  render: () => (
    <div>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600;`}>
        Display Utilities
      </h2>
      
      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Block Display (.tutor-block)</div>
        <div css={css`
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <span css={css`
            display: block;
            background: #4979e8;
            color: white;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 8px;
          `}>
            Block Element 1
          </span>
          <span css={css`
            display: block;
            background: #4979e8;
            color: white;
            padding: 12px;
            border-radius: 4px;
          `}>
            Block Element 2
          </span>
        </div>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Inline Display (.tutor-inline)</div>
        <div css={css`
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <span css={css`
            display: inline;
            background: #4979e8;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            margin-right: 8px;
          `}>
            Inline 1
          </span>
          <span css={css`
            display: inline;
            background: #4979e8;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            margin-right: 8px;
          `}>
            Inline 2
          </span>
          <span css={css`
            display: inline;
            background: #4979e8;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
          `}>
            Inline 3
          </span>
        </div>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Inline Block (.tutor-inline-block)</div>
        <div css={css`
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <div css={css`
            display: inline-block;
            background: #4979e8;
            color: white;
            padding: 16px;
            border-radius: 4px;
            margin-right: 8px;
            margin-bottom: 8px;
            width: 120px;
            text-align: center;
          `}>
            Inline Block 1
          </div>
          <div css={css`
            display: inline-block;
            background: #4979e8;
            color: white;
            padding: 16px;
            border-radius: 4px;
            margin-right: 8px;
            margin-bottom: 8px;
            width: 120px;
            text-align: center;
          `}>
            Inline Block 2
          </div>
          <div css={css`
            display: inline-block;
            background: #4979e8;
            color: white;
            padding: 16px;
            border-radius: 4px;
            width: 120px;
            text-align: center;
          `}>
            Inline Block 3
          </div>
        </div>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Hidden Element (.tutor-hidden)</div>
        <div css={css`
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <div css={demoStyles.box}>Visible Element</div>
          <div css={css`display: none;`}>
            Hidden Element (not visible)
          </div>
          <div css={[demoStyles.box, css`margin-top: 12px;`]}>
            Another Visible Element
          </div>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Display utilities for controlling element visibility and layout behavior.',
      },
    },
  },
};

export const ResponsiveUtilities: Story = {
  render: () => (
    <div>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600;`}>
        Responsive Utilities
      </h2>
      
      <div css={css`
        background: #f0f8ff;
        border: 1px solid #4979e8;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 24px;
      `}>
        <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
          Breakpoint System
        </h4>
        <ul css={css`margin: 0; padding-left: 16px; font-size: 12px; color: #666;`}>
          <li><strong>Mobile:</strong> Default (no prefix) - 0px and up</li>
          <li><strong>Tablet:</strong> .tutor-md-* - 768px and up</li>
          <li><strong>Desktop:</strong> .tutor-lg-* - 1024px and up</li>
        </ul>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Responsive Flex (.tutor-block .tutor-md-flex)</div>
        <div css={css`
          display: block;
          gap: 12px;
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
          
          @media (min-width: 768px) {
            display: flex;
          }
        `}>
          <div css={[demoStyles.box, css`margin-bottom: 12px; @media (min-width: 768px) { margin-bottom: 0; }`]}>
            Item 1
          </div>
          <div css={[demoStyles.box, css`margin-bottom: 12px; @media (min-width: 768px) { margin-bottom: 0; }`]}>
            Item 2
          </div>
          <div css={demoStyles.box}>
            Item 3
          </div>
        </div>
        <p css={css`font-size: 12px; color: #666; margin: 8px 0 0 0;`}>
          Stacked on mobile, flex on tablet and up
        </p>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Responsive Grid (.tutor-grid-cols-1 .tutor-md-grid-cols-2 .tutor-lg-grid-cols-4)</div>
        <div css={css`
          display: grid;
          grid-template-columns: 1fr;
          gap: 16px;
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
          
          @media (min-width: 768px) {
            grid-template-columns: repeat(2, 1fr);
          }
          
          @media (min-width: 1024px) {
            grid-template-columns: repeat(4, 1fr);
          }
        `}>
          <div css={demoStyles.box}>Item 1</div>
          <div css={demoStyles.box}>Item 2</div>
          <div css={demoStyles.box}>Item 3</div>
          <div css={demoStyles.box}>Item 4</div>
        </div>
        <p css={css`font-size: 12px; color: #666; margin: 8px 0 0 0;`}>
          1 column on mobile, 2 on tablet, 4 on desktop
        </p>
      </div>

      <div css={demoStyles.container}>
        <div css={demoStyles.label}>Responsive Visibility (.tutor-hidden .tutor-md-block)</div>
        <div css={css`
          padding: 16px;
          background: white;
          border-radius: 6px;
          border: 1px solid #e0e0e0;
        `}>
          <div css={demoStyles.box}>Always Visible</div>
          <div css={[
            demoStyles.box, 
            css`
              display: none;
              margin-top: 12px;
              
              @media (min-width: 768px) {
                display: block;
              }
            `
          ]}>
            Hidden on Mobile, Visible on Tablet+
          </div>
          <div css={[
            demoStyles.box, 
            css`
              margin-top: 12px;
              
              @media (min-width: 1024px) {
                display: none;
              }
            `
          ]}>
            Visible on Mobile/Tablet, Hidden on Desktop
          </div>
        </div>
        <p css={css`font-size: 12px; color: #666; margin: 8px 0 0 0;`}>
          Resize window to see responsive visibility changes
        </p>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Responsive utilities that adapt layouts and visibility based on screen size breakpoints.',
      },
    },
  },
};

export const RTLSupport: Story = {
  render: () => (
    <div>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600;`}>
        RTL Layout Support
      </h2>
      
      <div css={css`display: flex; flex-direction: column; gap: 24px;`}>
        
        <div>
          <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
            LTR (Left-to-Right)
          </h4>
          <div css={[demoStyles.container, css`direction: ltr;`]}>
            <div css={css`
              display: flex;
              justify-content: space-between;
              align-items: center;
              padding: 16px;
              background: white;
              border-radius: 6px;
              border: 1px solid #e0e0e0;
            `}>
              <div css={[demoStyles.box, css`background: #66c61c;`]}>Start</div>
              <div css={[demoStyles.box, css`background: #f79009;`]}>Center</div>
              <div css={[demoStyles.box, css`background: #f04438;`]}>End</div>
            </div>
          </div>
        </div>

        <div>
          <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
            RTL (Right-to-Left)
          </h4>
          <div css={[demoStyles.container, css`direction: rtl;`]}>
            <div css={css`
              display: flex;
              justify-content: space-between;
              align-items: center;
              padding: 16px;
              background: white;
              border-radius: 6px;
              border: 1px solid #e0e0e0;
            `}>
              <div css={[demoStyles.box, css`background: #66c61c;`]}>البداية</div>
              <div css={[demoStyles.box, css`background: #f79009;`]}>الوسط</div>
              <div css={[demoStyles.box, css`background: #f04438;`]}>النهاية</div>
            </div>
          </div>
        </div>

        <div>
          <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
            RTL-Aware Grid Layout
          </h4>
          <div css={css`display: flex; gap: 24px;`}>
            <div css={css`flex: 1; direction: ltr;`}>
              <div css={demoStyles.label}>LTR Grid</div>
              <div css={css`
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
                padding: 16px;
                background: white;
                border-radius: 6px;
                border: 1px solid #e0e0e0;
              `}>
                <div css={[demoStyles.box, css`font-size: 12px;`]}>1</div>
                <div css={[demoStyles.box, css`font-size: 12px;`]}>2</div>
                <div css={[demoStyles.box, css`font-size: 12px;`]}>3</div>
                <div css={[demoStyles.box, css`font-size: 12px;`]}>4</div>
                <div css={[demoStyles.box, css`font-size: 12px;`]}>5</div>
                <div css={[demoStyles.box, css`font-size: 12px;`]}>6</div>
              </div>
            </div>
            
            <div css={css`flex: 1; direction: rtl;`}>
              <div css={[demoStyles.label, css`text-align: right;`]}>RTL Grid</div>
              <div css={css`
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
                padding: 16px;
                background: white;
                border-radius: 6px;
                border: 1px solid #e0e0e0;
              `}>
                <div css={[demoStyles.box, css`font-size: 12px;`]}>١</div>
                <div css={[demoStyles.box, css`font-size: 12px;`]}>٢</div>
                <div css={[demoStyles.box, css`font-size: 12px;`]}>٣</div>
                <div css={[demoStyles.box, css`font-size: 12px;`]}>٤</div>
                <div css={[demoStyles.box, css`font-size: 12px;`]}>٥</div>
                <div css={[demoStyles.box, css`font-size: 12px;`]}>٦</div>
              </div>
            </div>
          </div>
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
          RTL Layout Features
        </h4>
        <ul css={css`margin: 0; padding-left: 16px; font-size: 12px; color: #666;`}>
          <li>All layout utilities automatically adapt to text direction</li>
          <li>Flexbox and grid layouts mirror appropriately in RTL</li>
          <li>Spacing and positioning utilities use logical properties</li>
          <li>No separate RTL-specific classes needed</li>
        </ul>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Layout utilities automatically adapt to RTL (right-to-left) text direction without requiring separate classes.',
      },
    },
  },
};