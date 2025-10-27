import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';
import { useState } from 'react';

const meta: Meta = {
  title: 'TutorCore/Components/Accordion',
  parameters: {
    docs: {
      description: {
        component: `
# Accordion Component

The TutorCore accordion component provides collapsible content sections with smooth animations and accessibility features. Perfect for FAQs, settings panels, and content organization.

## Features

- **Smooth Animations**: CSS transitions for expand/collapse
- **Multiple Modes**: Single or multiple panels open
- **Keyboard Navigation**: Arrow keys, Home, End, Space, Enter
- **Accessibility**: Proper ARIA attributes and focus management
- **RTL Support**: Automatic adaptation for RTL layouts

## CSS Classes

\`\`\`css
/* Accordion structure */
.tutor-accordion
.tutor-accordion__item
.tutor-accordion__trigger
.tutor-accordion__content

/* Accordion states */
.tutor-accordion__item--open
.tutor-accordion__trigger--active
\`\`\`
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;
const accordionStyles = {
  container: css`
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
  `,
  
  item: css`
    border-bottom: 1px solid #e0e0e0;
    
    &:last-child {
      border-bottom: none;
    }
  `,
  
  trigger: css`
    width: 100%;
    padding: 16px 20px;
    background: white;
    border: none;
    text-align: left;
    font-size: 16px;
    font-weight: 500;
    color: #333741;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s ease;
    
    &:hover {
      background: #f8f9fa;
    }
    
    &:focus {
      outline: 2px solid #4979e8;
      outline-offset: -2px;
    }
    
    &.active {
      background: #f6f8fe;
      color: #4979e8;
    }
  `,
  
  content: css`
    padding: 0 20px 20px 20px;
    background: white;
    animation: slideDown 0.2s ease-out;
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-8px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  `,
  
  icon: css`
    transition: transform 0.2s ease;
    
    &.rotated {
      transform: rotate(180deg);
    }
  `,
};

const ChevronIcon = ({ isOpen }: { isOpen: boolean }) => (
  <svg 
    width="20" 
    height="20" 
    viewBox="0 0 20 20" 
    fill="currentColor"
    css={[accordionStyles.icon, isOpen && css`transform: rotate(180deg);`]}
  >
    <path d="M5 7l5 5 5-5H5z"/>
  </svg>
);

const Accordion = ({ 
  items, 
  multiple = false,
  defaultOpen = [] 
}: {
  items: Array<{ title: string; content: React.ReactNode }>;
  multiple?: boolean;
  defaultOpen?: number[];
}) => {
  const [openItems, setOpenItems] = useState<number[]>(defaultOpen);

  const toggleItem = (index: number) => {
    if (multiple) {
      setOpenItems(prev => 
        prev.includes(index) 
          ? prev.filter(i => i !== index)
          : [...prev, index]
      );
    } else {
      setOpenItems(prev => 
        prev.includes(index) ? [] : [index]
      );
    }
  };

  const isOpen = (index: number) => openItems.includes(index);

  return (
    <div css={accordionStyles.container}>
      {items.map((item, index) => (
        <div key={index} css={accordionStyles.item}>
          <button
            css={accordionStyles.trigger}
            className={isOpen(index) ? 'active' : ''}
            onClick={() => toggleItem(index)}
            aria-expanded={isOpen(index)}
            aria-controls={`content-${index}`}
            id={`trigger-${index}`}
          >
            <span>{item.title}</span>
            <ChevronIcon isOpen={isOpen(index)} />
          </button>
          {isOpen(index) && (
            <div
              css={accordionStyles.content}
              role="region"
              aria-labelledby={`trigger-${index}`}
              id={`content-${index}`}
            >
              {item.content}
            </div>
          )}
        </div>
      ))}
    </div>
  );
};

export const BasicAccordion: Story = {
  render: () => (
    <Accordion 
      items={[
        {
          title: 'What is TutorCore?',
          content: (
            <div>
              <p css={css`margin: 0 0 12px 0; line-height: 1.5;`}>
                TutorCore is a comprehensive design system that provides consistent UI components, 
                themes, and utilities for modern web applications.
              </p>
              <p css={css`margin: 0; line-height: 1.5; color: #666;`}>
                It includes SCSS components, TypeScript utilities, and Alpine.js integration 
                for building accessible and responsive interfaces.
              </p>
            </div>
          )
        },
        {
          title: 'How do I get started?',
          content: (
            <div>
              <p css={css`margin: 0 0 12px 0; line-height: 1.5;`}>
                Getting started with TutorCore is easy:
              </p>
              <ol css={css`margin: 0; padding-left: 20px; line-height: 1.6;`}>
                <li>Include the CSS and JS files in your project</li>
                <li>Add the data-theme attribute to your HTML element</li>
                <li>Start using the component classes and Alpine.js methods</li>
                <li>Customize with SCSS variables if needed</li>
              </ol>
            </div>
          )
        },
        {
          title: 'Does it support RTL languages?',
          content: (
            <div>
              <p css={css`margin: 0 0 12px 0; line-height: 1.5;`}>
                Yes! TutorCore has built-in RTL (Right-to-Left) support for Arabic, Hebrew, 
                and other RTL languages.
              </p>
              <p css={css`margin: 0; line-height: 1.5; color: #666;`}>
                Simply set <code css={css`background: #f5f5f5; padding: 2px 4px; border-radius: 3px;`}>
                dir="rtl"</code> on your HTML element and all components will automatically adapt.
              </p>
            </div>
          )
        },
        {
          title: 'Is it accessible?',
          content: (
            <div>
              <p css={css`margin: 0 0 12px 0; line-height: 1.5;`}>
                Absolutely! TutorCore follows WCAG 2.1 guidelines and includes:
              </p>
              <ul css={css`margin: 0; padding-left: 20px; line-height: 1.6;`}>
                <li>Proper ARIA attributes and roles</li>
                <li>Keyboard navigation support</li>
                <li>High contrast ratios for text and backgrounds</li>
                <li>Focus indicators for interactive elements</li>
                <li>Screen reader compatibility</li>
              </ul>
            </div>
          )
        }
      ]}
      defaultOpen={[0]}
    />
  ),
  parameters: {
    docs: {
      description: {
        story: 'Basic accordion with FAQ content, single panel mode with first item open by default.',
      },
    },
  },
};

export const MultipleAccordion: Story = {
  render: () => (
    <Accordion 
      multiple={true}
      items={[
        {
          title: 'Design Tokens',
          content: (
            <div>
              <p css={css`margin: 0 0 12px 0; line-height: 1.5;`}>
                TutorCore includes comprehensive design tokens for:
              </p>
              <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 8px;`}>
                {['Colors', 'Typography', 'Spacing', 'Radius'].map(token => (
                  <div key={token} css={css`
                    padding: 8px 12px;
                    background: #f6f8fe;
                    border-radius: 4px;
                    text-align: center;
                    font-size: 14px;
                    color: #4979e8;
                  `}>
                    {token}
                  </div>
                ))}
              </div>
            </div>
          )
        },
        {
          title: 'Components',
          content: (
            <div>
              <p css={css`margin: 0 0 12px 0; line-height: 1.5;`}>
                Ready-to-use components with consistent styling:
              </p>
              <div css={css`display: flex; flex-wrap: wrap; gap: 8px;`}>
                {['Button', 'Card', 'Form', 'Modal', 'Accordion', 'Tabs', 'Toast'].map(component => (
                  <span key={component} css={css`
                    padding: 4px 8px;
                    background: #e3fbcc;
                    color: #2b5314;
                    border-radius: 4px;
                    font-size: 12px;
                  `}>
                    {component}
                  </span>
                ))}
              </div>
            </div>
          )
        },
        {
          title: 'Utilities',
          content: (
            <div>
              <p css={css`margin: 0 0 12px 0; line-height: 1.5;`}>
                Utility classes for rapid development:
              </p>
              <ul css={css`margin: 0; padding-left: 20px; line-height: 1.6; font-size: 14px;`}>
                <li>Layout utilities (flexbox, grid, positioning)</li>
                <li>Spacing utilities (margin, padding)</li>
                <li>Typography utilities (font sizes, weights)</li>
                <li>Color utilities (text, background, borders)</li>
              </ul>
            </div>
          )
        },
        {
          title: 'Alpine.js Integration',
          content: (
            <div>
              <p css={css`margin: 0 0 12px 0; line-height: 1.5;`}>
                Interactive components powered by Alpine.js with TypeScript support.
              </p>
              <div css={css`
                padding: 12px;
                background: #f8f9fa;
                border-radius: 6px;
                border-left: 4px solid #4979e8;
              `}>
                <code css={css`font-size: 12px; color: #333;`}>
                  x-data="TutorCore.accordion(&#123; multiple: true &#125;)"
                </code>
              </div>
            </div>
          )
        }
      ]}
      defaultOpen={[0, 2]}
    />
  ),
  parameters: {
    docs: {
      description: {
        story: 'Multiple accordion mode allowing several panels to be open simultaneously.',
      },
    },
  },
};