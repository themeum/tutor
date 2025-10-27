import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';

const meta: Meta = {
  title: 'TutorCore/Tokens/Typography',
  parameters: {
    docs: {
      description: {
        component: `
# Typography Tokens

TutorCore uses a systematic approach to typography with predefined scales for headings and paragraphs. All typography is designed to be accessible and readable across different devices and screen sizes.

## Typography Scale

The system includes:
- **Headings**: H1 (40px) to H5 (16px) with corresponding line heights
- **Paragraphs**: P1 (16px), P2 (14px), P3 (12px) with optimal line heights
- **Font Weights**: Light (300) to Bold (700) with semantic naming

## Usage

Typography tokens can be used as CSS classes or SASS variables in your components.
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;

const typographyTokens = {
  headings: [
    { name: 'H1', size: '40px', lineHeight: '48px', weight: '700', class: 'tutor-h1' },
    { name: 'H2', size: '32px', lineHeight: '40px', weight: '600', class: 'tutor-h2' },
    { name: 'H3', size: '24px', lineHeight: '32px', weight: '600', class: 'tutor-h3' },
    { name: 'H4', size: '20px', lineHeight: '28px', weight: '600', class: 'tutor-h4' },
    { name: 'H5', size: '16px', lineHeight: '24px', weight: '600', class: 'tutor-h5' },
  ],
  paragraphs: [
    { name: 'P1', size: '16px', lineHeight: '24px', weight: '400', class: 'tutor-p1' },
    { name: 'P2', size: '14px', lineHeight: '20px', weight: '400', class: 'tutor-p2' },
    { name: 'P3', size: '12px', lineHeight: '16px', weight: '400', class: 'tutor-p3' },
  ],
  weights: [
    { name: 'Light', weight: '300', class: 'tutor-font-light' },
    { name: 'Regular', weight: '400', class: 'tutor-font-normal' },
    { name: 'Medium', weight: '500', class: 'tutor-font-medium' },
    { name: 'Semi Bold', weight: '600', class: 'tutor-font-semibold' },
    { name: 'Bold', weight: '700', class: 'tutor-font-bold' },
  ],
};

const TypographyExample = ({ 
  name, 
  size, 
  lineHeight, 
  weight, 
  className, 
  text = "The quick brown fox jumps over the lazy dog" 
}: { 
  name: string; 
  size: string; 
  lineHeight: string; 
  weight: string; 
  className: string;
  text?: string;
}) => (
  <div
    css={css`
      margin-bottom: 24px;
      padding: 16px;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      background: #fafafa;
    `}
  >
    <div
      css={css`
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        font-size: 12px;
        color: #666;
      `}
    >
      <span css={css`font-weight: 600;`}>{name}</span>
      <span css={css`font-family: monospace;`}>
        {size} / {lineHeight} / {weight}
      </span>
    </div>
    <div
      css={css`
        font-size: ${size};
        line-height: ${lineHeight};
        font-weight: ${weight};
        color: #333;
        margin-bottom: 8px;
      `}
    >
      {text}
    </div>
    <div
      css={css`
        font-size: 11px;
        color: #888;
        font-family: monospace;
        background: #fff;
        padding: 4px 8px;
        border-radius: 4px;
        display: inline-block;
      `}
    >
      .{className}
    </div>
  </div>
);

export const Headings: Story = {
  render: () => (
    <div>
      <h2 css={css`margin-bottom: 24px; font-size: 20px; font-weight: 600;`}>
        Heading Styles
      </h2>
      {typographyTokens.headings.map((heading) => (
        <TypographyExample
          key={heading.name}
          name={heading.name}
          size={heading.size}
          lineHeight={heading.lineHeight}
          weight={heading.weight}
          className={heading.class}
          text={`${heading.name} Heading Example`}
        />
      ))}
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Heading styles from H1 to H5 with appropriate font sizes, line heights, and weights.',
      },
    },
  },
};

export const Paragraphs: Story = {
  render: () => (
    <div>
      <h2 css={css`margin-bottom: 24px; font-size: 20px; font-weight: 600;`}>
        Paragraph Styles
      </h2>
      {typographyTokens.paragraphs.map((paragraph) => (
        <TypographyExample
          key={paragraph.name}
          name={paragraph.name}
          size={paragraph.size}
          lineHeight={paragraph.lineHeight}
          weight={paragraph.weight}
          className={paragraph.class}
          text="This is a paragraph example showing the typography style. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."
        />
      ))}
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Paragraph styles with different sizes optimized for readability and hierarchy.',
      },
    },
  },
};

export const FontWeights: Story = {
  render: () => (
    <div>
      <h2 css={css`margin-bottom: 24px; font-size: 20px; font-weight: 600;`}>
        Font Weights
      </h2>
      {typographyTokens.weights.map((weight) => (
        <TypographyExample
          key={weight.name}
          name={weight.name}
          size="16px"
          lineHeight="24px"
          weight={weight.weight}
          className={weight.class}
          text={`${weight.name} font weight example`}
        />
      ))}
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Available font weights from light to bold for creating visual hierarchy.',
      },
    },
  },
};

export const FontScaling: Story = {
  render: () => (
    <div>
      <h2 css={css`margin-bottom: 24px; font-size: 20px; font-weight: 600;`}>
        Font Scaling for Accessibility
      </h2>
      <p css={css`margin-bottom: 24px; color: #666;`}>
        TutorCore supports font scaling for accessibility. Available scales: 80%, 90%, 100% (default), 110%, 120%
      </p>
      
      {[80, 90, 100, 110, 120].map((scale) => (
        <div
          key={scale}
          css={css`
            margin-bottom: 24px;
            padding: 16px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #fafafa;
            font-size: ${scale}%;
          `}
        >
          <div
            css={css`
              font-size: 12px;
              color: #666;
              margin-bottom: 8px;
              font-weight: 600;
            `}
          >
            {scale}% Scale (.tutor-font-scale-{scale})
          </div>
          <h3 css={css`font-size: 1.5rem; margin-bottom: 8px;`}>
            Heading at {scale}% scale
          </h3>
          <p css={css`font-size: 1rem; margin: 0;`}>
            This paragraph demonstrates how text appears at {scale}% font scaling for accessibility.
          </p>
        </div>
      ))}
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Font scaling options for accessibility compliance, allowing users to adjust text size.',
      },
    },
  },
};

export const TypographyShowcase: Story = {
  render: () => (
    <div>
      <div css={css`margin-bottom: 32px;`}>
        <h1 css={css`font-size: 40px; line-height: 48px; font-weight: 700; margin-bottom: 16px;`}>
          Typography Showcase
        </h1>
        <p css={css`font-size: 16px; line-height: 24px; color: #666; margin-bottom: 24px;`}>
          A comprehensive example showing how different typography styles work together to create visual hierarchy.
        </p>
      </div>

      <article css={css`max-width: 600px;`}>
        <h2 css={css`font-size: 32px; line-height: 40px; font-weight: 600; margin-bottom: 16px;`}>
          The Importance of Typography
        </h2>
        
        <p css={css`font-size: 16px; line-height: 24px; margin-bottom: 16px;`}>
          Typography is the art and technique of arranging type to make written language legible, 
          readable, and appealing when displayed. The arrangement of type involves selecting typefaces, 
          point sizes, line lengths, line-spacing, and letter-spacing.
        </p>

        <h3 css={css`font-size: 24px; line-height: 32px; font-weight: 600; margin-bottom: 12px;`}>
          Design Principles
        </h3>

        <p css={css`font-size: 16px; line-height: 24px; margin-bottom: 16px;`}>
          Good typography establishes a strong visual hierarchy, provides a graphic balance to the website, 
          and sets the product's overall tone. Typography should guide and inform your users, optimize 
          readability and accessibility, and ensure an excellent user experience.
        </p>

        <h4 css={css`font-size: 20px; line-height: 28px; font-weight: 600; margin-bottom: 12px;`}>
          Key Considerations
        </h4>

        <ul css={css`font-size: 16px; line-height: 24px; margin-bottom: 16px; padding-left: 20px;`}>
          <li>Readability and legibility</li>
          <li>Visual hierarchy</li>
          <li>Accessibility compliance</li>
          <li>Brand consistency</li>
        </ul>

        <h5 css={css`font-size: 16px; line-height: 24px; font-weight: 600; margin-bottom: 8px;`}>
          Implementation Notes
        </h5>

        <p css={css`font-size: 14px; line-height: 20px; color: #666; margin-bottom: 12px;`}>
          All typography tokens are available as both CSS classes and SASS variables. 
          Use CSS classes for quick implementation or SASS variables for custom components.
        </p>

        <p css={css`font-size: 12px; line-height: 16px; color: #888;`}>
          This example demonstrates the complete typography scale working together in a real-world context.
        </p>
      </article>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'A complete example showing how all typography styles work together to create effective visual hierarchy.',
      },
    },
  },
};