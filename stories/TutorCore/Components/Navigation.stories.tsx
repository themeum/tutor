import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';
import { useState } from 'react';

const meta: Meta = {
  title: 'TutorCore/Components/Navigation',
  parameters: {
    docs: {
      description: {
        component: `
# Navigation Components

TutorCore provides various navigation components including tabs, breadcrumbs, and pagination. All components support RTL layouts and include proper accessibility features.

## Features

- **Tabs**: Horizontal and vertical tab navigation
- **Breadcrumbs**: Hierarchical navigation with separators
- **Pagination**: Page navigation with various styles
- **RTL Support**: Automatic adaptation for RTL layouts
- **Accessibility**: Keyboard navigation and ARIA attributes

## CSS Classes

\`\`\`css
/* Tabs */
.tutor-tabs
.tutor-tabs__nav
.tutor-tab
.tutor-tab--active
.tutor-tabs__content
.tutor-tab-panel

/* Breadcrumbs */
.tutor-breadcrumb
.tutor-breadcrumb__item
.tutor-breadcrumb__link
.tutor-breadcrumb__separator

/* Pagination */
.tutor-pagination
.tutor-pagination__item
.tutor-pagination__link
.tutor-pagination__prev
.tutor-pagination__next
\`\`\`
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;
const navigationStyles = {
  // Tabs
  tabs: css`
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
  `,
  
  tabNav: css`
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
  
  tabContent: css`
    padding: 20px;
    background: white;
  `,
  
  // Breadcrumbs
  breadcrumb: css`
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    padding: 12px 0;
    font-size: 14px;
  `,
  
  breadcrumbItem: css`
    display: flex;
    align-items: center;
  `,
  
  breadcrumbLink: css`
    color: #4979e8;
    text-decoration: none;
    transition: color 0.2s ease;
    
    &:hover {
      color: #3e64de;
      text-decoration: underline;
    }
    
    &:focus {
      outline: 2px solid #4979e8;
      outline-offset: 2px;
      border-radius: 2px;
    }
  `,
  
  breadcrumbCurrent: css`
    color: #333741;
    font-weight: 500;
  `,
  
  breadcrumbSeparator: css`
    margin: 0 8px;
    color: #94969c;
  `,
  
  // Pagination
  pagination: css`
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 16px 0;
  `,
  
  paginationItem: css`
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 8px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    color: #333741;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    
    &:hover {
      background: #f8f9fa;
      border-color: #cecfd2;
    }
    
    &:focus {
      outline: 2px solid #4979e8;
      outline-offset: 2px;
    }
    
    &.active {
      background: #4979e8;
      border-color: #4979e8;
      color: white;
    }
    
    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      
      &:hover {
        background: white;
        border-color: #e0e0e0;
      }
    }
  `,
};

const Tabs = ({ 
  tabs, 
  defaultTab = 0 
}: {
  tabs: Array<{ label: string; content: React.ReactNode }>;
  defaultTab?: number;
}) => {
  const [activeTab, setActiveTab] = useState(defaultTab);

  return (
    <div css={navigationStyles.tabs}>
      <div css={navigationStyles.tabNav} role="tablist">
        {tabs.map((tab, index) => (
          <button
            key={index}
            css={navigationStyles.tab}
            className={activeTab === index ? 'active' : ''}
            onClick={() => setActiveTab(index)}
            role="tab"
            aria-selected={activeTab === index}
            aria-controls={`panel-${index}`}
            id={`tab-${index}`}
          >
            {tab.label}
          </button>
        ))}
      </div>
      <div css={navigationStyles.tabContent}>
        <div
          role="tabpanel"
          aria-labelledby={`tab-${activeTab}`}
          id={`panel-${activeTab}`}
        >
          {tabs[activeTab]?.content}
        </div>
      </div>
    </div>
  );
};

const Breadcrumb = ({ 
  items 
}: {
  items: Array<{ label: string; href?: string }>;
}) => (
  <nav css={navigationStyles.breadcrumb} aria-label="Breadcrumb">
    {items.map((item, index) => (
      <div key={index} css={navigationStyles.breadcrumbItem}>
        {index > 0 && (
          <span css={navigationStyles.breadcrumbSeparator} aria-hidden="true">
            /
          </span>
        )}
        {item.href && index < items.length - 1 ? (
          <a href={item.href} css={navigationStyles.breadcrumbLink}>
            {item.label}
          </a>
        ) : (
          <span css={navigationStyles.breadcrumbCurrent} aria-current="page">
            {item.label}
          </span>
        )}
      </div>
    ))}
  </nav>
);

const Pagination = ({ 
  currentPage = 1, 
  totalPages = 10,
  onPageChange 
}: {
  currentPage?: number;
  totalPages?: number;
  onPageChange?: (page: number) => void;
}) => {
  const getVisiblePages = () => {
    const delta = 2;
    const range = [];
    const rangeWithDots = [];

    for (let i = Math.max(2, currentPage - delta); i <= Math.min(totalPages - 1, currentPage + delta); i++) {
      range.push(i);
    }

    if (currentPage - delta > 2) {
      rangeWithDots.push(1, '...');
    } else {
      rangeWithDots.push(1);
    }

    rangeWithDots.push(...range);

    if (currentPage + delta < totalPages - 1) {
      rangeWithDots.push('...', totalPages);
    } else {
      rangeWithDots.push(totalPages);
    }

    return rangeWithDots;
  };

  return (
    <nav css={navigationStyles.pagination} aria-label="Pagination">
      <button
        css={navigationStyles.paginationItem}
        onClick={() => onPageChange?.(currentPage - 1)}
        disabled={currentPage === 1}
        aria-label="Previous page"
      >
        ←
      </button>
      
      {getVisiblePages().map((page, index) => (
        <span key={index}>
          {page === '...' ? (
            <span css={css`padding: 0 8px; color: #94969c;`}>…</span>
          ) : (
            <button
              css={navigationStyles.paginationItem}
              className={currentPage === page ? 'active' : ''}
              onClick={() => onPageChange?.(page as number)}
              aria-label={`Page ${page}`}
              aria-current={currentPage === page ? 'page' : undefined}
            >
              {page}
            </button>
          )}
        </span>
      ))}
      
      <button
        css={navigationStyles.paginationItem}
        onClick={() => onPageChange?.(currentPage + 1)}
        disabled={currentPage === totalPages}
        aria-label="Next page"
      >
        →
      </button>
    </nav>
  );
};

export const TabNavigation: Story = {
  render: () => (
    <Tabs 
      tabs={[
        {
          label: 'Overview',
          content: (
            <div>
              <h3 css={css`margin: 0 0 12px 0; font-size: 16px; font-weight: 600;`}>
                Project Overview
              </h3>
              <p css={css`margin: 0 0 16px 0; line-height: 1.5;`}>
                This tab contains general information about the project, including key features 
                and objectives.
              </p>
              <div css={css`
                padding: 16px;
                background: #f6f8fe;
                border-radius: 6px;
                border-left: 4px solid #4979e8;
              `}>
                <h4 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
                  Key Features
                </h4>
                <ul css={css`margin: 0; padding-left: 16px; font-size: 14px;`}>
                  <li>Responsive design system</li>
                  <li>RTL language support</li>
                  <li>Accessibility compliance</li>
                  <li>TypeScript integration</li>
                </ul>
              </div>
            </div>
          )
        },
        {
          label: 'Documentation',
          content: (
            <div>
              <h3 css={css`margin: 0 0 12px 0; font-size: 16px; font-weight: 600;`}>
                Documentation
              </h3>
              <p css={css`margin: 0 0 16px 0; line-height: 1.5;`}>
                Comprehensive documentation for developers and designers.
              </p>
              <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;`}>
                {[
                  { title: 'Getting Started', desc: 'Quick setup guide' },
                  { title: 'Components', desc: 'UI component library' },
                  { title: 'Utilities', desc: 'Helper classes' },
                  { title: 'Examples', desc: 'Code examples' }
                ].map((item, index) => (
                  <div key={index} css={css`
                    padding: 12px;
                    background: #f8f9fa;
                    border-radius: 6px;
                    border: 1px solid #e0e0e0;
                  `}>
                    <h4 css={css`margin: 0 0 4px 0; font-size: 14px; font-weight: 600;`}>
                      {item.title}
                    </h4>
                    <p css={css`margin: 0; font-size: 12px; color: #666;`}>
                      {item.desc}
                    </p>
                  </div>
                ))}
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
              <p css={css`margin: 0 0 16px 0; line-height: 1.5;`}>
                Customize the behavior and appearance of components.
              </p>
              <div css={css`display: flex; flex-direction: column; gap: 12px;`}>
                <label css={css`display: flex; align-items: center; cursor: pointer;`}>
                  <input type="checkbox" css={css`margin-right: 8px;`} defaultChecked />
                  <span css={css`font-size: 14px;`}>Enable dark theme</span>
                </label>
                <label css={css`display: flex; align-items: center; cursor: pointer;`}>
                  <input type="checkbox" css={css`margin-right: 8px;`} defaultChecked />
                  <span css={css`font-size: 14px;`}>RTL language support</span>
                </label>
                <label css={css`display: flex; align-items: center; cursor: pointer;`}>
                  <input type="checkbox" css={css`margin-right: 8px;`} />
                  <span css={css`font-size: 14px;`}>Reduced motion</span>
                </label>
              </div>
            </div>
          )
        }
      ]}
      defaultTab={0}
    />
  ),
  parameters: {
    docs: {
      description: {
        story: 'Horizontal tab navigation with three panels showing different content types.',
      },
    },
  },
};

export const BreadcrumbNavigation: Story = {
  render: () => (
    <div css={css`display: flex; flex-direction: column; gap: 16px;`}>
      <div>
        <h3 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
          Simple Breadcrumb
        </h3>
        <Breadcrumb 
          items={[
            { label: 'Home', href: '#' },
            { label: 'Products', href: '#' },
            { label: 'Laptops' }
          ]}
        />
      </div>
      
      <div>
        <h3 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
          Deep Navigation
        </h3>
        <Breadcrumb 
          items={[
            { label: 'Dashboard', href: '#' },
            { label: 'Settings', href: '#' },
            { label: 'User Management', href: '#' },
            { label: 'Permissions', href: '#' },
            { label: 'Role Editor' }
          ]}
        />
      </div>
      
      <div>
        <h3 css={css`margin: 0 0 8px 0; font-size: 14px; font-weight: 600;`}>
          With Icons
        </h3>
        <nav css={navigationStyles.breadcrumb} aria-label="Breadcrumb">
          <div css={navigationStyles.breadcrumbItem}>
            <a href="#" css={[navigationStyles.breadcrumbLink, css`display: flex; align-items: center; gap: 4px;`]}>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M8 1l6 6v8H2V7l6-6z"/>
              </svg>
              Home
            </a>
          </div>
          <span css={navigationStyles.breadcrumbSeparator}>/</span>
          <div css={navigationStyles.breadcrumbItem}>
            <a href="#" css={[navigationStyles.breadcrumbLink, css`display: flex; align-items: center; gap: 4px;`]}>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M2 3h12v10H2V3zm2 2v6h8V5H4z"/>
              </svg>
              Library
            </a>
          </div>
          <span css={navigationStyles.breadcrumbSeparator}>/</span>
          <div css={navigationStyles.breadcrumbItem}>
            <span css={navigationStyles.breadcrumbCurrent}>Components</span>
          </div>
        </nav>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Breadcrumb navigation examples showing simple paths, deep navigation, and icon integration.',
      },
    },
  },
};

export const PaginationNavigation: Story = {
  render: () => {
    const [currentPage, setCurrentPage] = useState(5);

    return (
      <div css={css`display: flex; flex-direction: column; gap: 24px;`}>
        <div>
          <h3 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
            Standard Pagination
          </h3>
          <Pagination 
            currentPage={currentPage}
            totalPages={10}
            onPageChange={setCurrentPage}
          />
          <p css={css`margin: 8px 0 0 0; text-align: center; font-size: 12px; color: #666;`}>
            Page {currentPage} of 10
          </p>
        </div>
        
        <div>
          <h3 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
            Simple Pagination
          </h3>
          <div css={css`display: flex; align-items: center; justify-content: center; gap: 12px;`}>
            <button css={navigationStyles.paginationItem}>
              ← Previous
            </button>
            <span css={css`font-size: 14px; color: #666;`}>
              Page 3 of 15
            </span>
            <button css={navigationStyles.paginationItem}>
              Next →
            </button>
          </div>
        </div>
        
        <div>
          <h3 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
            Compact Pagination
          </h3>
          <div css={css`display: flex; align-items: center; justify-content: center; gap: 4px;`}>
            <button css={[navigationStyles.paginationItem, css`min-width: 32px; height: 32px;`]}>
              ←
            </button>
            {[1, 2, 3, 4, 5].map(page => (
              <button 
                key={page}
                css={[navigationStyles.paginationItem, css`min-width: 32px; height: 32px;`]}
                className={page === 3 ? 'active' : ''}
              >
                {page}
              </button>
            ))}
            <button css={[navigationStyles.paginationItem, css`min-width: 32px; height: 32px;`]}>
              →
            </button>
          </div>
        </div>
      </div>
    );
  },
  parameters: {
    docs: {
      description: {
        story: 'Different pagination styles including standard, simple, and compact variants.',
      },
    },
  },
};