import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';

const meta: Meta = {
  title: 'TutorCore/Utilities/Typography',
  parameters: {
    docs: {
      description: {
        component: `
# Typography Utilities

TutorCore provides comprehensive typography utility classes for text styling, alignment, and formatting. All utilities support responsive breakpoints and RTL layouts.

## Features

- **Text Sizes**: Utility classes for all typography scale sizes
- **Font Weights**: Light to bold weight utilities
- **Text Alignment**: Left, center, right, justify with RTL support
- **Text Decoration**: Underline, line-through, no decoration
- **Text Transform**: Uppercase, lowercase, capitalize
- **Line Height**: Utilities for different line heights
- **Responsive**: Breakpoint-specific typography utilities

## CSS Classes

\`\`\`css
/* Text sizes */
.tutor-text-h1, .tutor-text-h2, .tutor-text-h3, .tutor-text-h4, .tutor-text-h5
.tutor-text-p1, .tutor-text-p2, .tutor-text-p3
.tutor-text-small, .tutor-text-large

/* Font weights */
.tutor-font-light, .tutor-font-normal, .tutor-font-medium
.tutor-font-semibold, .tutor-font-bold

/* Text alignment */
.tutor-text-left, .tutor-text-center, .tutor-text-right, .tutor-text-justify

/* Text decoration */
.tutor-underline, .tutor-line-through, .tutor-no-underline

/* Text transform */
.tutor-uppercase, .tutor-lowercase, .tutor-capitalize

/* Line height */
.tutor-leading-tight, .tutor-leading-normal, .tutor-leading-relaxed
\`\`\`
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;
const typographyUtilities = {
  textSizes: [
    { name: 'H1 Size', class: 'tutor-text-h1', size: '40px', example: 'Large Heading Text' },
    { name: 'H2 Size', class: 'tutor-text-h2', size: '32px', example: 'Section Heading Text' },
    { name: 'H3 Size', class: 'tutor-text-h3', size: '24px', example: 'Subsection Heading' },
    { name: 'H4 Size', class: 'tutor-text-h4', size: '20px', example: 'Small Heading Text' },
    { name: 'H5 Size', class: 'tutor-text-h5', size: '16px', example: 'Tiny Heading Text' },
    { name: 'P1 Size', class: 'tutor-text-p1', size: '16px', example: 'Regular paragraph text' },
    { name: 'P2 Size', class: 'tutor-text-p2', size: '14px', example: 'Small paragraph text' },
    { name: 'P3 Size', class: 'tutor-text-p3', size: '12px', example: 'Caption or small text' },
  ],
  
  fontWeights: [
    { name: 'Light', class: 'tutor-font-light', weight: '300' },
    { name: 'Normal', class: 'tutor-font-normal', weight: '400' },
    { name: 'Medium', class: 'tutor-font-medium', weight: '500' },
    { name: 'Semibold', class: 'tutor-font-semibold', weight: '600' },
    { name: 'Bold', class: 'tutor-font-bold', weight: '700' },
  ],
  
  alignments: [
    { name: 'Left', class: 'tutor-text-left', description: 'Align text to the left' },
    { name: 'Center', class: 'tutor-text-center', description: 'Center align text' },
    { name: 'Right', class: 'tutor-text-right', description: 'Align text to the right' },
    { name: 'Justify', class: 'tutor-text-justify', description: 'Justify text alignment' },
  ],
};

const TypographyExample = ({ 
  name, 
  className, 
  example, 
  description,
  style = {} 
}: { 
  name: string; 
  className: string; 
  example: string;
  description?: string;
  style?: React.CSSProperties;
}) => (
  <div css={css`
    margin-bottom: 16px;
    padding: 16px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
  `}>
    <div css={css`
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
    `}>
      <span css={css`
        font-size: 14px;
        font-weight: 600;
        color: #333;
      `}>
        {name}
      </span>
      <span css={css`
        font-size: 11px;
        font-family: monospace;
        color: #888;
        background: #f5f5f5;
        padding: 2px 6px;
        border-radius: 3px;
      `}>
        .{className}
      </span>
    </div>
    <div css={css`
      font-size: inherit;
      line-height: inherit;
    `}>
      {example}
    </div>
    {description && (
      <div css={css`
        margin-top: 8px;
        font-size: 12px;
        color: #666;
      `}>
        {description}
      </div>
    )}
  </div>
);

export const TextSizes: Story = {
  render: () => (
    <div>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600;`}>
        Text Size Utilities
      </h2>
      <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 16px;`}>
        {typographyUtilities.textSizes.map((size) => (
          <TypographyExample
            key={size.class}
            name={size.name}
            className={size.class}
            example={size.example}
            description={`Font size: ${size.size}`}
            style={{ fontSize: size.size }}
          />
        ))}
      </div>
      
      <div css={css`
        margin-top: 24px;
        padding: 16px;
        background: #f0f8ff;
        border-radius: 8px;
        border-left: 4px solid #4979e8;
      `}>
        <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
          Usage Example
        </h4>
        <pre css={css`
          margin: 0;
          font-size: 12px;
          color: #333;
          font-family: monospace;
          background: white;
          padding: 8px;
          border-radius: 4px;
        `}>
{`<h1 class="tutor-text-h1">Main Heading</h1>
<p class="tutor-text-p1">Regular paragraph text</p>
<small class="tutor-text-p3">Small caption text</small>`}
        </pre>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Text size utilities based on the typography scale, from H1 (40px) to P3 (12px).',
      },
    },
  },
};

export const FontWeights: Story = {
  render: () => (
    <div>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600;`}>
        Font Weight Utilities
      </h2>
      <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;`}>
        {typographyUtilities.fontWeights.map((weight) => (
          <TypographyExample
            key={weight.class}
            name={weight.name}
            className={weight.class}
            example={`${weight.name} font weight example text`}
            description={`Font weight: ${weight.weight}`}
            style={{ fontWeight: weight.weight }}
          />
        ))}
      </div>
      
      <div css={css`margin-top: 24px;`}>
        <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
          Combined Examples
        </h3>
        <div css={css`
          padding: 20px;
          background: white;
          border: 1px solid #e0e0e0;
          border-radius: 8px;
        `}>
          <h1 css={css`font-size: 32px; font-weight: 700; margin: 0 0 12px 0;`}>
            Bold Heading (.tutor-text-h2 .tutor-font-bold)
          </h1>
          <p css={css`font-size: 16px; font-weight: 400; margin: 0 0 12px 0; line-height: 1.5;`}>
            This is regular paragraph text with normal font weight. 
            <span css={css`font-weight: 500;`}>This part is medium weight</span> and 
            <span css={css`font-weight: 600;`}>this part is semibold</span>.
          </p>
          <p css={css`font-size: 14px; font-weight: 300; margin: 0; color: #666; line-height: 1.5;`}>
            This is smaller text with light font weight, perfect for captions and secondary information.
          </p>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Font weight utilities from light (300) to bold (700) for creating visual hierarchy.',
      },
    },
  },
};

export const TextAlignment: Story = {
  render: () => (
    <div>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600;`}>
        Text Alignment Utilities
      </h2>
      <div css={css`display: flex; flex-direction: column; gap: 16px;`}>
        {typographyUtilities.alignments.map((alignment) => (
          <div key={alignment.class} css={css`
            padding: 16px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
          `}>
            <div css={css`
              display: flex;
              justify-content: space-between;
              align-items: center;
              margin-bottom: 12px;
            `}>
              <span css={css`font-size: 14px; font-weight: 600;`}>
                {alignment.name} Alignment
              </span>
              <span css={css`
                font-size: 11px;
                font-family: monospace;
                color: #888;
                background: #f5f5f5;
                padding: 2px 6px;
                border-radius: 3px;
              `}>
                .{alignment.class}
              </span>
            </div>
            <div css={css`
              text-align: ${alignment.name.toLowerCase()};
              padding: 12px;
              background: #f8f9fa;
              border-radius: 4px;
              border: 1px dashed #e0e0e0;
            `}>
              This text demonstrates {alignment.name.toLowerCase()} alignment. Lorem ipsum dolor sit amet, 
              consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
              Ut enim ad minim veniam, quis nostrud exercitation.
            </div>
            <div css={css`
              margin-top: 8px;
              font-size: 12px;
              color: #666;
            `}>
              {alignment.description}
            </div>
          </div>
        ))}
      </div>
      
      <div css={css`
        margin-top: 24px;
        padding: 16px;
        background: #f0f8ff;
        border-radius: 8px;
        border-left: 4px solid #4979e8;
      `}>
        <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
          RTL Support
        </h4>
        <p css={css`margin: 0; font-size: 12px; color: #666;`}>
          Text alignment utilities automatically adapt to RTL layouts. 
          <code css={css`background: #fff; padding: 2px 4px; border-radius: 2px;`}>.tutor-text-left</code> becomes right-aligned in RTL, 
          and <code css={css`background: #fff; padding: 2px 4px; border-radius: 2px;`}>.tutor-text-right</code> becomes left-aligned.
        </p>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Text alignment utilities that automatically adapt to RTL layouts.',
      },
    },
  },
};

export const TextDecorationAndTransform: Story = {
  render: () => (
    <div>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600;`}>
        Text Decoration & Transform
      </h2>
      
      <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin-bottom: 24px;`}>
        
        {/* Text Decoration */}
        <div css={css`
          padding: 16px;
          background: white;
          border: 1px solid #e0e0e0;
          border-radius: 8px;
        `}>
          <h3 css={css`margin: 0 0 12px 0; font-size: 16px; font-weight: 600;`}>
            Text Decoration
          </h3>
          <div css={css`display: flex; flex-direction: column; gap: 8px;`}>
            <div>
              <span css={css`font-size: 12px; color: #666; margin-right: 8px;`}>.tutor-underline:</span>
              <span css={css`text-decoration: underline;`}>Underlined text</span>
            </div>
            <div>
              <span css={css`font-size: 12px; color: #666; margin-right: 8px;`}>.tutor-line-through:</span>
              <span css={css`text-decoration: line-through;`}>Strikethrough text</span>
            </div>
            <div>
              <span css={css`font-size: 12px; color: #666; margin-right: 8px;`}>.tutor-no-underline:</span>
              <a href="#" css={css`text-decoration: none; color: #4979e8;`}>Link without underline</a>
            </div>
          </div>
        </div>

        {/* Text Transform */}
        <div css={css`
          padding: 16px;
          background: white;
          border: 1px solid #e0e0e0;
          border-radius: 8px;
        `}>
          <h3 css={css`margin: 0 0 12px 0; font-size: 16px; font-weight: 600;`}>
            Text Transform
          </h3>
          <div css={css`display: flex; flex-direction: column; gap: 8px;`}>
            <div>
              <span css={css`font-size: 12px; color: #666; margin-right: 8px;`}>.tutor-uppercase:</span>
              <span css={css`text-transform: uppercase;`}>uppercase text</span>
            </div>
            <div>
              <span css={css`font-size: 12px; color: #666; margin-right: 8px;`}>.tutor-lowercase:</span>
              <span css={css`text-transform: lowercase;`}>LOWERCASE TEXT</span>
            </div>
            <div>
              <span css={css`font-size: 12px; color: #666; margin-right: 8px;`}>.tutor-capitalize:</span>
              <span css={css`text-transform: capitalize;`}>capitalize each word</span>
            </div>
          </div>
        </div>

      </div>

      {/* Line Height */}
      <div css={css`
        padding: 16px;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
      `}>
        <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
          Line Height Utilities
        </h3>
        <div css={css`display: flex; flex-direction: column; gap: 16px;`}>
          
          <div>
            <div css={css`font-size: 12px; color: #666; margin-bottom: 4px;`}>
              .tutor-leading-tight (line-height: 1.2)
            </div>
            <p css={css`
              margin: 0;
              line-height: 1.2;
              padding: 8px;
              background: #f8f9fa;
              border-radius: 4px;
            `}>
              This paragraph has tight line height. Lorem ipsum dolor sit amet, consectetur adipiscing elit, 
              sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
            </p>
          </div>

          <div>
            <div css={css`font-size: 12px; color: #666; margin-bottom: 4px;`}>
              .tutor-leading-normal (line-height: 1.5)
            </div>
            <p css={css`
              margin: 0;
              line-height: 1.5;
              padding: 8px;
              background: #f8f9fa;
              border-radius: 4px;
            `}>
              This paragraph has normal line height. Lorem ipsum dolor sit amet, consectetur adipiscing elit, 
              sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
            </p>
          </div>

          <div>
            <div css={css`font-size: 12px; color: #666; margin-bottom: 4px;`}>
              .tutor-leading-relaxed (line-height: 1.8)
            </div>
            <p css={css`
              margin: 0;
              line-height: 1.8;
              padding: 8px;
              background: #f8f9fa;
              border-radius: 4px;
            `}>
              This paragraph has relaxed line height. Lorem ipsum dolor sit amet, consectetur adipiscing elit, 
              sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
            </p>
          </div>

        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Text decoration, transform, and line height utilities for advanced typography styling.',
      },
    },
  },
};

export const ResponsiveTypography: Story = {
  render: () => (
    <div>
      <h2 css={css`margin: 0 0 24px 0; font-size: 20px; font-weight: 600;`}>
        Responsive Typography
      </h2>
      
      <div css={css`
        padding: 20px;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 24px;
      `}>
        <h3 css={css`
          font-size: 24px;
          font-weight: 600;
          margin: 0 0 16px 0;
          
          @media (min-width: 768px) {
            font-size: 32px;
          }
          
          @media (min-width: 1024px) {
            font-size: 40px;
          }
        `}>
          Responsive Heading
        </h3>
        <p css={css`
          font-size: 14px;
          line-height: 1.4;
          margin: 0 0 16px 0;
          
          @media (min-width: 768px) {
            font-size: 16px;
            line-height: 1.5;
          }
        `}>
          This text scales responsively. On mobile it's 14px, on tablet and up it's 16px. 
          The heading above scales from 24px on mobile, to 32px on tablet, to 40px on desktop.
        </p>
        <div css={css`
          font-size: 12px;
          color: #666;
          background: #f8f9fa;
          padding: 12px;
          border-radius: 4px;
        `}>
          <strong>Resize your browser window</strong> to see the responsive typography in action.
        </div>
      </div>

      <div css={css`
        padding: 16px;
        background: #f0f8ff;
        border-radius: 8px;
        border-left: 4px solid #4979e8;
      `}>
        <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
          Responsive Typography Classes
        </h4>
        <div css={css`font-size: 12px; color: #666;`}>
          <p css={css`margin: 0 0 8px 0;`}>
            Use responsive prefixes for breakpoint-specific typography:
          </p>
          <ul css={css`margin: 0; padding-left: 16px;`}>
            <li><code css={css`background: #fff; padding: 2px 4px; border-radius: 2px;`}>.tutor-md-text-h1</code> - H1 size on tablet and up</li>
            <li><code css={css`background: #fff; padding: 2px 4px; border-radius: 2px;`}>.tutor-lg-font-bold</code> - Bold weight on desktop and up</li>
            <li><code css={css`background: #fff; padding: 2px 4px; border-radius: 2px;`}>.tutor-md-text-center</code> - Center align on tablet and up</li>
          </ul>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Responsive typography that adapts to different screen sizes using breakpoint prefixes.',
      },
    },
  },
};