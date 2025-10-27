import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';

const meta: Meta = {
  title: 'TutorCore/Components/Card',
  parameters: {
    docs: {
      description: {
        component: `
# Card Component

The TutorCore card component provides flexible containers for grouping related content. Cards support multiple layouts, elevation levels, and interactive states.

## Features

- **Flexible Layout**: Header, body, and footer sections
- **Elevation Levels**: Multiple shadow depths for visual hierarchy
- **Interactive States**: Hover effects and clickable variants
- **RTL Support**: Automatic adaptation for RTL layouts
- **Responsive**: Adapts to different screen sizes

## CSS Classes

\`\`\`css
/* Base card class */
.tutor-card

/* Card sections */
.tutor-card__header
.tutor-card__body
.tutor-card__footer
.tutor-card__title
.tutor-card__subtitle

/* Card variants */
.tutor-card--elevated
.tutor-card--outlined
.tutor-card--interactive
\`\`\`
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;

const cardStyles = {
  base: css`
    background: white;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.2s ease;
  `,
  
  outlined: css`
    border: 1px solid #e0e0e0;
  `,
  
  elevated: css`
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  `,
  
  interactive: css`
    cursor: pointer;
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }
  `,
  
  header: css`
    padding: 20px 20px 0 20px;
  `,
  
  body: css`
    padding: 20px;
  `,
  
  footer: css`
    padding: 0 20px 20px 20px;
  `,
  
  title: css`
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: #333741;
  `,
  
  subtitle: css`
    margin: 0;
    font-size: 14px;
    color: #61646c;
  `,
};

const Card = ({ 
  children, 
  variant = 'outlined',
  interactive = false,
  ...props 
}: {
  children: React.ReactNode;
  variant?: 'outlined' | 'elevated';
  interactive?: boolean;
}) => (
  <div
    css={[
      cardStyles.base,
      cardStyles[variant],
      interactive && cardStyles.interactive,
    ]}
    {...props}
  >
    {children}
  </div>
);

export const BasicCard: Story = {
  render: () => (
    <Card>
      <div css={cardStyles.header}>
        <h3 css={cardStyles.title}>Card Title</h3>
        <p css={cardStyles.subtitle}>Card subtitle or description</p>
      </div>
      <div css={cardStyles.body}>
        <p css={css`margin: 0; color: #333; line-height: 1.5;`}>
          This is the main content area of the card. It can contain any type of content 
          including text, images, buttons, and other components.
        </p>
      </div>
      <div css={cardStyles.footer}>
        <button css={css`
          padding: 8px 16px;
          background: #4979e8;
          color: white;
          border: none;
          border-radius: 6px;
          font-size: 14px;
          cursor: pointer;
          margin-right: 8px;
        `}>
          Primary Action
        </button>
        <button css={css`
          padding: 8px 16px;
          background: transparent;
          color: #4979e8;
          border: 1px solid #4979e8;
          border-radius: 6px;
          font-size: 14px;
          cursor: pointer;
        `}>
          Secondary
        </button>
      </div>
    </Card>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Basic card with header, body, and footer sections containing title, content, and actions.',
      },
    },
  },
};

export const CardVariants: Story = {
  render: () => (
    <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;`}>
      <Card variant="outlined">
        <div css={cardStyles.body}>
          <h3 css={cardStyles.title}>Outlined Card</h3>
          <p css={css`margin: 0; color: #666; line-height: 1.5;`}>
            This card uses a border for definition. Good for layouts with colored backgrounds.
          </p>
        </div>
      </Card>
      
      <Card variant="elevated">
        <div css={cardStyles.body}>
          <h3 css={cardStyles.title}>Elevated Card</h3>
          <p css={css`margin: 0; color: #666; line-height: 1.5;`}>
            This card uses shadow for elevation. Creates depth and visual hierarchy.
          </p>
        </div>
      </Card>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Two card variants: outlined (with border) and elevated (with shadow).',
      },
    },
  },
};

export const InteractiveCards: Story = {
  render: () => (
    <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;`}>
      <Card variant="elevated" interactive>
        <div css={cardStyles.body}>
          <div css={css`
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #4979e8, #3e64de);
            border-radius: 8px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
          `}>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
          </div>
          <h3 css={cardStyles.title}>Feature Card</h3>
          <p css={css`margin: 0; color: #666; line-height: 1.5; font-size: 14px;`}>
            Hover over this card to see the interactive effect. Perfect for feature highlights.
          </p>
        </div>
      </Card>
      
      <Card variant="elevated" interactive>
        <div css={cardStyles.body}>
          <div css={css`
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #66c61c, #4ca30d);
            border-radius: 8px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
          `}>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h3 css={cardStyles.title}>Success Card</h3>
          <p css={css`margin: 0; color: #666; line-height: 1.5; font-size: 14px;`}>
            Interactive cards can represent different states and provide visual feedback.
          </p>
        </div>
      </Card>
      
      <Card variant="elevated" interactive>
        <div css={cardStyles.body}>
          <div css={css`
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #f79009, #dc6803);
            border-radius: 8px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
          `}>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
              <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <h3 css={cardStyles.title}>Warning Card</h3>
          <p css={css`margin: 0; color: #666; line-height: 1.5; font-size: 14px;`}>
            Cards can use semantic colors to convey meaning and importance.
          </p>
        </div>
      </Card>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Interactive cards with hover effects and semantic color coding for different states.',
      },
    },
  },
};

export const CardWithMedia: Story = {
  render: () => (
    <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;`}>
      <Card variant="elevated">
        <div css={css`
          height: 200px;
          background: linear-gradient(135deg, #4979e8, #3e64de);
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-size: 18px;
          font-weight: 600;
        `}>
          Image Placeholder
        </div>
        <div css={cardStyles.body}>
          <h3 css={cardStyles.title}>Card with Image</h3>
          <p css={css`margin: 0; color: #666; line-height: 1.5;`}>
            Cards can include media content like images or videos at the top.
          </p>
        </div>
      </Card>
      
      <Card variant="elevated">
        <div css={cardStyles.body}>
          <div css={css`display: flex; align-items: center; margin-bottom: 16px;`}>
            <div css={css`
              width: 48px;
              height: 48px;
              background: linear-gradient(135deg, #4979e8, #3e64de);
              border-radius: 50%;
              margin-right: 12px;
              display: flex;
              align-items: center;
              justify-content: center;
              color: white;
              font-weight: 600;
            `}>
              JD
            </div>
            <div>
              <h4 css={css`margin: 0; font-size: 16px; font-weight: 600;`}>John Doe</h4>
              <p css={css`margin: 0; font-size: 14px; color: #666;`}>Product Manager</p>
            </div>
          </div>
          <p css={css`margin: 0; color: #666; line-height: 1.5; font-style: italic;`}>
            "This design system has significantly improved our development workflow and consistency."
          </p>
        </div>
      </Card>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Cards with media content including images and profile information layouts.',
      },
    },
  },
};

export const CardLayouts: Story = {
  render: () => (
    <div css={css`display: flex; flex-direction: column; gap: 24px;`}>
      {/* Horizontal Card */}
      <Card variant="outlined">
        <div css={css`display: flex; align-items: center;`}>
          <div css={css`
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #4979e8, #3e64de);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
          `}>
            Image
          </div>
          <div css={css`flex: 1; padding: 20px;`}>
            <h3 css={cardStyles.title}>Horizontal Card Layout</h3>
            <p css={css`margin: 0 0 16px 0; color: #666; line-height: 1.5;`}>
              This card uses a horizontal layout with image on the left and content on the right.
            </p>
            <button css={css`
              padding: 8px 16px;
              background: #4979e8;
              color: white;
              border: none;
              border-radius: 6px;
              font-size: 14px;
              cursor: pointer;
            `}>
              Learn More
            </button>
          </div>
        </div>
      </Card>
      
      {/* Compact Cards Grid */}
      <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;`}>
        {[
          { title: 'Analytics', value: '2,847', change: '+12%', color: '#4979e8' },
          { title: 'Revenue', value: '$45.2K', change: '+8%', color: '#66c61c' },
          { title: 'Users', value: '1,234', change: '-3%', color: '#f79009' },
          { title: 'Orders', value: '567', change: '+15%', color: '#f04438' },
        ].map((stat, index) => (
          <Card key={index} variant="outlined">
            <div css={cardStyles.body}>
              <div css={css`display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;`}>
                <span css={css`font-size: 14px; color: #666;`}>{stat.title}</span>
                <div css={css`
                  width: 8px;
                  height: 8px;
                  background: ${stat.color};
                  border-radius: 50%;
                `} />
              </div>
              <div css={css`font-size: 24px; font-weight: 700; color: #333; margin-bottom: 4px;`}>
                {stat.value}
              </div>
              <div css={css`
                font-size: 12px;
                color: ${stat.change.startsWith('+') ? '#66c61c' : '#f04438'};
                font-weight: 500;
              `}>
                {stat.change} from last month
              </div>
            </div>
          </Card>
        ))}
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Different card layouts including horizontal cards and compact stat cards for dashboards.',
      },
    },
  },
};

export const RTLSupport: Story = {
  render: () => (
    <div css={css`display: flex; flex-direction: column; gap: 24px;`}>
      <div>
        <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
          LTR (Left-to-Right)
        </h4>
        <div css={css`direction: ltr;`}>
          <Card variant="outlined">
            <div css={css`display: flex; align-items: center;`}>
              <div css={css`
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #4979e8, #3e64de);
                margin: 20px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 600;
              `}>
                IMG
              </div>
              <div css={css`flex: 1; padding: 20px 20px 20px 0;`}>
                <h3 css={cardStyles.title}>Card Title</h3>
                <p css={css`margin: 0; color: #666; line-height: 1.5;`}>
                  This card demonstrates LTR layout with image on the left and content flowing to the right.
                </p>
              </div>
            </div>
          </Card>
        </div>
      </div>
      
      <div>
        <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
          RTL (Right-to-Left)
        </h4>
        <div css={css`direction: rtl;`}>
          <Card variant="outlined">
            <div css={css`display: flex; align-items: center;`}>
              <div css={css`
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #4979e8, #3e64de);
                margin: 20px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 600;
              `}>
                صورة
              </div>
              <div css={css`flex: 1; padding: 20px 0 20px 20px;`}>
                <h3 css={[cardStyles.title, css`text-align: right;`]}>عنوان البطاقة</h3>
                <p css={css`margin: 0; color: #666; line-height: 1.5; text-align: right;`}>
                  هذه البطاقة تُظهر التخطيط من اليمين إلى اليسار مع الصورة على اليمين والمحتوى يتدفق إلى اليسار.
                </p>
              </div>
            </div>
          </Card>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Cards automatically adapt to RTL layouts with proper content flow and alignment.',
      },
    },
  },
};