import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';
import { useState } from 'react';

const meta: Meta = {
  title: 'TutorCore/Alpine/Tabs',
  parameters: {
    docs: {
      description: {
        component: `
# Alpine.js Tabs Component

The TutorCore Alpine.js tabs component provides accessible tab navigation with keyboard support, ARIA attributes, and smooth transitions. Perfect for organizing content into sections.

## Features

- **Keyboard Navigation**: Arrow keys, Home, End, Tab support
- **Accessibility**: Proper ARIA attributes and roles
- **Smooth Transitions**: CSS transitions between tab panels
- **RTL Support**: Automatic adaptation for RTL layouts
- **TypeScript**: Full type definitions and configuration

## Configuration

\`\`\`typescript
interface TabsConfig {
  defaultTab?: number;           // Initial active tab (default: 0)
  orientation?: 'horizontal' | 'vertical';
  keyboard?: boolean;            // Enable keyboard navigation
  lazy?: boolean;               // Lazy load tab content
}
\`\`\`

## Usage

\`\`\`html
<div x-data="TutorCore.tabs({ defaultTab: 0 })" class="tutor-tabs">
  <div class="tutor-tabs__nav">
    <button @click="setTab(0)" :class="{'active': isActive(0)}" class="tutor-tab">
      Overview
    </button>
    <button @click="setTab(1)" :class="{'active': isActive(1)}" class="tutor-tab">
      Details
    </button>
  </div>
  <div class="tutor-tabs__content">
    <div x-show="isActive(0)" class="tutor-tab-panel">
      Overview content...
    </div>
    <div x-show="isActive(1)" class="tutor-tab-panel">
      Details content...
    </div>
  </div>
</div>
\`\`\`
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;
// Mock Alpine.js tabs functionality
const useTabs = (config: { defaultTab?: number } = {}) => {
  const [activeTab, setActiveTab] = useState(config.defaultTab || 0);

  const setTab = (index: number) => setActiveTab(index);
  const isActive = (index: number) => activeTab === index;

  return { activeTab, setTab, isActive };
};

const tabStyles = {
  container: css`
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
  `,
  
  nav: css`
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
  `,
  
  tab: css`
    flex: 1;
    padding: 12px 16px;
    background: none;
    border: none;
    font-size: 14px;
    font-weight: 500;
    color: #61646c;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 2px solid transparent;
    
    &:hover {
      background: #f0f1f1;
      color: #333741;
    }
    
    &:focus {
      outline: 2px solid #4979e8;
      outline-offset: -2px;
    }
    
    &.active {
      color: #4979e8;
      background: white;
      border-bottom-color: #4979e8;
    }
  `,
  
  content: css`
    padding: 20px;
  `,
  
  panel: css`
    animation: fadeIn 0.2s ease-out;
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(4px); }
      to { opacity: 1; transform: translateY(0); }
    }
  `,
};

const AlpineTabs = ({ 
  tabs, 
  config = {} 
}: {
  tabs: Array<{ label: string; content: React.ReactNode }>;
  config?: { defaultTab?: number };
}) => {
  const { activeTab, setTab, isActive } = useTabs(config);

  return (
    <div css={tabStyles.container}>
      <div css={tabStyles.nav} role="tablist">
        {tabs.map((tab, index) => (
          <button
            key={index}
            css={tabStyles.tab}
            className={isActive(index) ? 'active' : ''}
            onClick={() => setTab(index)}
            role="tab"
            aria-selected={isActive(index)}
            aria-controls={`panel-${index}`}
            id={`tab-${index}`}
          >
            {tab.label}
          </button>
        ))}
      </div>
      <div css={tabStyles.content}>
        {tabs.map((tab, index) => (
          isActive(index) && (
            <div
              key={index}
              css={tabStyles.panel}
              role="tabpanel"
              aria-labelledby={`tab-${index}`}
              id={`panel-${index}`}
            >
              {tab.content}
            </div>
          )
        ))}
      </div>
    </div>
  );
};

export const BasicTabs: Story = {
  render: () => (
    <AlpineTabs 
      tabs={[
        {
          label: 'Overview',
          content: (
            <div>
              <h3 css={css`margin: 0 0 12px 0; font-size: 16px; font-weight: 600;`}>
                Project Overview
              </h3>
              <p css={css`margin: 0 0 16px 0; line-height: 1.5; color: #333;`}>
                This is the overview tab containing general information about the project. 
                It provides a high-level summary of key features and objectives.
              </p>
              <ul css={css`margin: 0; padding-left: 20px; color: #666;`}>
                <li>Modern design system with TypeScript support</li>
                <li>Comprehensive component library</li>
                <li>RTL language support</li>
                <li>Accessibility-first approach</li>
              </ul>
            </div>
          )
        },
        {
          label: 'Details',
          content: (
            <div>
              <h3 css={css`margin: 0 0 12px 0; font-size: 16px; font-weight: 600;`}>
                Technical Details
              </h3>
              <p css={css`margin: 0 0 16px 0; line-height: 1.5; color: #333;`}>
                Detailed technical specifications and implementation details for developers.
              </p>
              <div css={css`
                background: #f8f9fa;
                padding: 16px;
                border-radius: 6px;
                border: 1px solid #e0e0e0;
              `}>
                <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
                  Technologies Used
                </h4>
                <div css={css`display: flex; gap: 8px; flex-wrap: wrap;`}>
                  {['TypeScript', 'Alpine.js', 'SCSS', 'Storybook'].map(tech => (
                    <span key={tech} css={css`
                      padding: 4px 8px;
                      background: #4979e8;
                      color: white;
                      border-radius: 4px;
                      font-size: 12px;
                    `}>
                      {tech}
                    </span>
                  ))}
                </div>
              </div>
            </div>
          )
        },
        {
          label: 'Settings',
          content: (
            <div>
              <h3 css={css`margin: 0 0 12px 0; font-size: 16px; font-weight: 600;`}>
                Configuration Settings
              </h3>
              <p css={css`margin: 0 0 16px 0; line-height: 1.5; color: #333;`}>
                Customize the behavior and appearance of the tabs component.
              </p>
              <div css={css`display: flex; flex-direction: column; gap: 12px;`}>
                <label css={css`display: flex; align-items: center; cursor: pointer;`}>
                  <input type="checkbox" css={css`margin-right: 8px;`} defaultChecked />
                  <span css={css`font-size: 14px;`}>Enable keyboard navigation</span>
                </label>
                <label css={css`display: flex; align-items: center; cursor: pointer;`}>
                  <input type="checkbox" css={css`margin-right: 8px;`} />
                  <span css={css`font-size: 14px;`}>Lazy load tab content</span>
                </label>
                <label css={css`display: flex; align-items: center; cursor: pointer;`}>
                  <input type="checkbox" css={css`margin-right: 8px;`} defaultChecked />
                  <span css={css`font-size: 14px;`}>Show transition animations</span>
                </label>
              </div>
            </div>
          )
        }
      ]}
      config={{ defaultTab: 0 }}
    />
  ),
  parameters: {
    docs: {
      description: {
        story: 'Basic tabs component with three panels showing different types of content.',
      },
    },
  },
};

export const VerticalTabs: Story = {
  render: () => (
    <div css={css`display: flex; max-width: 600px; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;`}>
      <div css={css`
        display: flex;
        flex-direction: column;
        background: #f8f9fa;
        border-right: 1px solid #e0e0e0;
        min-width: 150px;
      `}>
        {['Profile', 'Security', 'Notifications', 'Billing'].map((label, index) => (
          <button
            key={index}
            css={css`
              padding: 12px 16px;
              background: none;
              border: none;
              font-size: 14px;
              font-weight: 500;
              color: #61646c;
              cursor: pointer;
              text-align: left;
              transition: all 0.2s ease;
              border-right: 2px solid transparent;
              
              &:hover {
                background: #f0f1f1;
                color: #333741;
              }
              
              &:focus {
                outline: 2px solid #4979e8;
                outline-offset: -2px;
              }
              
              &.active {
                color: #4979e8;
                background: white;
                border-right-color: #4979e8;
              }
            `}
            className={index === 0 ? 'active' : ''}
          >
            {label}
          </button>
        ))}
      </div>
      <div css={css`flex: 1; padding: 20px; background: white;`}>
        <div css={tabStyles.panel}>
          <h3 css={css`margin: 0 0 12px 0; font-size: 16px; font-weight: 600;`}>
            Profile Settings
          </h3>
          <p css={css`margin: 0 0 16px 0; line-height: 1.5; color: #333;`}>
            Manage your profile information and preferences.
          </p>
          <div css={css`display: flex; flex-direction: column; gap: 12px;`}>
            <input 
              type="text" 
              placeholder="Full Name"
              css={css`
                padding: 8px 12px;
                border: 1px solid #cecfd2;
                border-radius: 4px;
                font-size: 14px;
              `}
            />
            <input 
              type="email" 
              placeholder="Email Address"
              css={css`
                padding: 8px 12px;
                border: 1px solid #cecfd2;
                border-radius: 4px;
                font-size: 14px;
              `}
            />
          </div>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Vertical tabs layout suitable for settings pages and navigation sidebars.',
      },
    },
  },
};

export const TabsWithIcons: Story = {
  render: () => (
    <AlpineTabs 
      tabs={[
        {
          label: (
            <div css={css`display: flex; align-items: center; gap: 8px;`}>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M8 2a6 6 0 100 12A6 6 0 008 2zM7 5h2v2H7V5zm0 3h2v4H7V8z"/>
              </svg>
              Dashboard
            </div>
          ) as any,
          content: (
            <div>
              <h3 css={css`margin: 0 0 12px 0; font-size: 16px; font-weight: 600;`}>
                Dashboard Overview
              </h3>
              <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;`}>
                {[
                  { label: 'Total Users', value: '1,234', color: '#4979e8' },
                  { label: 'Revenue', value: '$45.2K', color: '#66c61c' },
                  { label: 'Orders', value: '567', color: '#f79009' },
                ].map((stat, index) => (
                  <div key={index} css={css`
                    padding: 16px;
                    background: #f8f9fa;
                    border-radius: 6px;
                    text-align: center;
                  `}>
                    <div css={css`
                      font-size: 24px;
                      font-weight: 700;
                      color: ${stat.color};
                      margin-bottom: 4px;
                    `}>
                      {stat.value}
                    </div>
                    <div css={css`font-size: 12px; color: #666;`}>
                      {stat.label}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )
        },
        {
          label: (
            <div css={css`display: flex; align-items: center; gap: 8px;`}>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M3 3h10v10H3V3zm2 2v6h6V5H5z"/>
              </svg>
              Analytics
            </div>
          ) as any,
          content: (
            <div>
              <h3 css={css`margin: 0 0 12px 0; font-size: 16px; font-weight: 600;`}>
                Analytics Report
              </h3>
              <p css={css`margin: 0 0 16px 0; line-height: 1.5; color: #333;`}>
                Detailed analytics and performance metrics for your application.
              </p>
              <div css={css`
                height: 200px;
                background: linear-gradient(135deg, #f8f9fa, #e9ecef);
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #666;
                font-size: 14px;
              `}>
                📊 Chart Placeholder
              </div>
            </div>
          )
        },
        {
          label: (
            <div css={css`display: flex; align-items: center; gap: 8px;`}>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M8 2a6 6 0 100 12A6 6 0 008 2zm0 9a1 1 0 100-2 1 1 0 000 2zm1-3V6a1 1 0 10-2 0v2a1 1 0 102 0z"/>
              </svg>
              Reports
            </div>
          ) as any,
          content: (
            <div>
              <h3 css={css`margin: 0 0 12px 0; font-size: 16px; font-weight: 600;`}>
                Generated Reports
              </h3>
              <div css={css`display: flex; flex-direction: column; gap: 8px;`}>
                {[
                  'Monthly Sales Report - October 2024',
                  'User Activity Summary - Q3 2024',
                  'Performance Metrics - Last 30 Days'
                ].map((report, index) => (
                  <div key={index} css={css`
                    padding: 12px;
                    background: #f8f9fa;
                    border-radius: 4px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                  `}>
                    <span css={css`font-size: 14px;`}>{report}</span>
                    <button css={css`
                      padding: 4px 8px;
                      background: #4979e8;
                      color: white;
                      border: none;
                      border-radius: 4px;
                      font-size: 12px;
                      cursor: pointer;
                    `}>
                      Download
                    </button>
                  </div>
                ))}
              </div>
            </div>
          )
        }
      ]}
      config={{ defaultTab: 0 }}
    />
  ),
  parameters: {
    docs: {
      description: {
        story: 'Tabs with icons in the labels, perfect for dashboard and admin interfaces.',
      },
    },
  },
};