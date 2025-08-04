import { css } from '@emotion/react';
import { useState } from 'react';

import Alert from '@TutorShared/atoms/Alert';
import { Avatar } from '@TutorShared/atoms/Avatar';
import Button from '@TutorShared/atoms/Button';
import Chip from '@TutorShared/atoms/Chip';
import LoadingSpinner from '@TutorShared/atoms/LoadingSpinner';
import MagicButton from '@TutorShared/atoms/MagicButton';
import ProBadge from '@TutorShared/atoms/ProBadge';
import Skeleton from '@TutorShared/atoms/Skeleton';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Switch from '@TutorShared/atoms/Switch';
import TextInput from '@TutorShared/atoms/TextInput';
import { TutorBadge } from '@TutorShared/atoms/TutorBadge';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Card from '@TutorShared/molecules/Card';
import EmptyState from '@TutorShared/molecules/EmptyState';
import ImageCard from '@TutorShared/molecules/ImageCard';
import Table from '@TutorShared/molecules/Table';
import Tabs from '@TutorShared/molecules/Tabs';
import ThreeDots from '@TutorShared/molecules/ThreeDots';

export default {};
interface ComponentItem {
  name: string;
  category: 'atoms' | 'molecules';
  description: string;
  preview: React.ReactNode;
  storyPath: string;
}

const previewContainerStyles = css`
  display: flex;
  align-items: center;
  justify-content: center;
  gap: ${spacing[8]};
  flex-wrap: wrap;
  min-height: 60px;
`;

const componentLibrary: ComponentItem[] = [
  // Atoms
  {
    name: 'Alert',
    category: 'atoms',
    description: 'Informative alert component with multiple severity levels',
    preview: (
      <div css={previewContainerStyles}>
        <Alert type="danger" icon="warning">
          This is an error alert
        </Alert>
      </div>
    ),
    storyPath: '?path=/docs/atoms-alert--docs',
  },
  {
    name: 'Button',
    category: 'atoms',
    description: 'Versatile button component with multiple variants and states',
    preview: (
      <div css={previewContainerStyles}>
        <Button variant="primary" size="small">
          Primary
        </Button>
        <Button variant="secondary" size="small">
          Secondary
        </Button>
        <Button variant="danger" size="small">
          Danger
        </Button>
      </div>
    ),
    storyPath: '?path=/docs/atoms-button--docs',
  },
  {
    name: 'MagicButton',
    category: 'atoms',
    description: 'Visually rich button with gradient effects and animations',
    preview: (
      <div css={previewContainerStyles}>
        <MagicButton size="sm">Magic AI</MagicButton>
      </div>
    ),
    storyPath: '?path=/docs/atoms-magicbutton--docs',
  },
  {
    name: 'TextInput',
    category: 'atoms',
    description: 'Flexible input component with validation and styling options',
    preview: (
      <div css={previewContainerStyles}>
        <TextInput onChange={() => {}} placeholder="Enter text..." size="small" />
      </div>
    ),
    storyPath: '?path=/docs/atoms-textinput--docs',
  },
  {
    name: 'Switch',
    category: 'atoms',
    description: 'Toggle switch for boolean states',
    preview: (
      <div css={previewContainerStyles}>
        <Switch checked={true} />
        <Switch checked={false} />
      </div>
    ),
    storyPath: '?path=/docs/atoms-switch--docs',
  },
  {
    name: 'Avatar',
    category: 'atoms',
    description: 'User avatar with image fallback to initials',
    preview: (
      <div css={previewContainerStyles}>
        <Avatar name="John Doe" />
        <Avatar name="Jane Smith" image="https://placehold.co/40x40" />
      </div>
    ),
    storyPath: '?path=/docs/atoms-avatar--docs',
  },
  {
    name: 'LoadingSpinner',
    category: 'atoms',
    description: 'Animated loading indicator with multiple variants',
    preview: (
      <div css={previewContainerStyles}>
        <LoadingSpinner size={24} />
      </div>
    ),
    storyPath: '?path=/docs/atoms-loadingspinner--docs',
  },
  {
    name: 'Chip',
    category: 'atoms',
    description: 'Compact element for tags and filters',
    preview: (
      <div css={previewContainerStyles}>
        <Chip label="JavaScript" />
        <Chip label="React" />
      </div>
    ),
    storyPath: '?path=/docs/atoms-chip--docs',
  },
  {
    name: 'TutorBadge',
    category: 'atoms',
    description: 'Status badges with multiple variants',
    preview: (
      <div css={previewContainerStyles}>
        <TutorBadge variant="success">Active</TutorBadge>
        <TutorBadge variant="warning">Pending</TutorBadge>
      </div>
    ),
    storyPath: '?path=/docs/atoms-tutorbadge--docs',
  },
  {
    name: 'ProBadge',
    category: 'atoms',
    description: 'Premium feature indicator badge',
    preview: (
      <div css={previewContainerStyles}>
        <ProBadge size="small" content="Pro" />
      </div>
    ),
    storyPath: '?path=/docs/atoms-probadge--docs',
  },
  {
    name: 'Skeleton',
    category: 'atoms',
    description: 'Loading placeholder with animation',
    preview: (
      <div css={previewContainerStyles}>
        <Skeleton width={80} height={12} animation />
        <Skeleton width={60} height={12} animation />
      </div>
    ),
    storyPath: '?path=/docs/atoms-skeleton--docs',
  },
  {
    name: 'SVGIcon',
    category: 'atoms',
    description: 'Scalable vector icons with dynamic loading',
    preview: (
      <div css={previewContainerStyles}>
        <SVGIcon name="plus" width={20} height={20} />
        <SVGIcon name="edit" width={20} height={20} />
        <SVGIcon name="delete" width={20} height={20} />
      </div>
    ),
    storyPath: '?path=/docs/atoms-svgicon--docs',
  },
  // Molecules
  {
    name: 'Card',
    category: 'molecules',
    description: 'Flexible container with title, content, and actions',
    preview: (
      <div css={previewContainerStyles}>
        <Card title="Card Title" subtitle="Subtitle" hasBorder>
          <div
            css={css`
              font-size: 12px;
              color: ${colorTokens.text.subdued};
            `}
          >
            Card content
          </div>
        </Card>
      </div>
    ),
    storyPath: '?path=/docs/molecules-card--docs',
  },
  {
    name: 'Table',
    category: 'molecules',
    description: 'Data table with sorting and custom cell rendering',
    preview: (
      <div css={previewContainerStyles}>
        <Table
          data={[
            { id: 1, name: 'Item 1' },
            { id: 2, name: 'Item 2' },
          ]}
          columns={[
            { Header: 'ID', Cell: (row) => row.id },
            { Header: 'Name', Cell: (row) => row.name },
          ]}
          itemsPerPage={2}
          loading={true}
        />
      </div>
    ),
    storyPath: '?path=/docs/molecules-table--docs',
  },
  {
    name: 'Tabs',
    category: 'molecules',
    description: 'Tabbed navigation with counts and icons',
    preview: (
      <div css={previewContainerStyles}>
        <Tabs
          activeTab={'all'}
          onChange={() => {}}
          tabList={[
            { label: 'All', value: 'all' },
            { label: 'Active', value: 'active', count: 5, activeBadge: true },
          ]}
          wrapperCss={css`
            width: 300px;
          `}
        />
      </div>
    ),
    storyPath: '?path=/docs/molecules-tabs--docs',
  },
  {
    name: 'Popover',
    category: 'molecules',
    description: 'Contextual overlay with rich content support',
    preview: (
      <div css={previewContainerStyles}>
        <div
          css={css`
            position: relative;
            background: ${colorTokens.background.white};
            border: 1px solid ${colorTokens.stroke.default};
            border-radius: ${borderRadius[8]};
            padding: ${spacing[8]};
            font-size: 10px;
            box-shadow: 0 2px 8px ${colorTokens.stroke.default}33;
          `}
        >
          Popover Content
        </div>
      </div>
    ),
    storyPath: '?path=/docs/molecules-popover--docs',
  },
  {
    name: 'ThreeDots',
    category: 'molecules',
    description: 'Action menu with customizable options',
    preview: (
      <div css={previewContainerStyles}>
        <ThreeDots isOpen={false} closePopover={() => {}} onClick={() => {}}>
          <ThreeDots.Option text="Edit" />
        </ThreeDots>
      </div>
    ),
    storyPath: '?path=/docs/molecules-threedots--docs',
  },
  {
    name: 'EmptyState',
    category: 'molecules',
    description: 'Placeholder for empty content areas',
    preview: (
      <div css={previewContainerStyles}>
        <EmptyState
          size="small"
          title="No Data"
          description="There is no data to display here."
          emptyStateImage="https://placehold.co/200x80"
          removeBorder={false}
        />
      </div>
    ),
    storyPath: '?path=/docs/molecules-emptystate--docs',
  },
  {
    name: 'ImageCard',
    category: 'molecules',
    description: 'Image display with fallback placeholder',
    preview: (
      <div css={previewContainerStyles}>
        <ImageCard name="Sample Image" path="https://placehold.co/200x100" />
      </div>
    ),
    storyPath: '?path=/docs/molecules-imagecard--docs',
  },
];

export const ComponentsOverview = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<'all' | 'atoms' | 'molecules'>('all');

  const filteredComponents = componentLibrary.filter((component) => {
    const matchesSearch =
      component.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      component.description.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesCategory = selectedCategory === 'all' || component.category === selectedCategory;
    return matchesSearch && matchesCategory;
  });

  const atomsCount = componentLibrary.filter((c) => c.category === 'atoms').length;
  const moleculesCount = componentLibrary.filter((c) => c.category === 'molecules').length;

  const handleSearchChange = (value: string) => {
    setSearchTerm(value);
  };

  const handleCategoryChange = (category: 'all' | 'atoms' | 'molecules') => {
    setSelectedCategory(category);
  };

  const handleComponentClick = (storyPath: string) => {
    if (window.parent && window.parent !== window) {
      try {
        window.parent.history.pushState({}, '', storyPath);
        window.parent.location.reload();
      } catch {
        window.location.href = storyPath;
      }
    } else {
      window.location.href = storyPath;
    }
  };

  const handleKeyDown = (event: React.KeyboardEvent, storyPath: string) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      handleComponentClick(storyPath);
    }
  };

  return (
    <div css={overviewStyles.container}>
      <header css={overviewStyles.header}>
        <h1 css={overviewStyles.title}>Components Overview</h1>
        <p css={overviewStyles.subtitle}>
          Tutor provides plenty of UI components to enrich your web applications, and we will improve components
          experience consistently. We also recommend some great{' '}
          <a href="#" css={overviewStyles.link}>
            Third-Party Libraries
          </a>{' '}
          additionally.
        </p>
      </header>

      <div css={overviewStyles.controls}>
        <div css={overviewStyles.searchContainer}>
          <TextInput
            placeholder="Search in components"
            value={searchTerm}
            onChange={(value) => handleSearchChange(value)}
            aria-label="Search components"
            variant="search"
          />
        </div>

        <div>
          <Tabs
            activeTab={selectedCategory}
            onChange={(tab) => handleCategoryChange(tab as 'all' | 'atoms' | 'molecules')}
            tabList={[
              { label: 'All', value: 'all' },
              { label: 'Atoms', value: 'atoms', count: atomsCount },
              { label: 'Molecules', value: 'molecules', count: moleculesCount },
            ]}
          />
        </div>
      </div>

      {selectedCategory !== 'molecules' && (
        <section css={overviewStyles.section}>
          <h2 css={overviewStyles.sectionTitle}>
            Atoms <span css={overviewStyles.sectionCount}>{atomsCount}</span>
          </h2>
          <div css={overviewStyles.grid}>
            {filteredComponents
              .filter((component) => component.category === 'atoms')
              .map((component) => (
                <div
                  key={component.name}
                  css={overviewStyles.componentCard}
                  onClick={() => handleComponentClick(component.storyPath)}
                  onKeyDown={(event) => handleKeyDown(event, component.storyPath)}
                  tabIndex={0}
                  role="button"
                  aria-label={`Open ${component.name} component story`}
                >
                  <div css={overviewStyles.componentPreview}>{component.preview}</div>
                  <div css={overviewStyles.componentInfo}>
                    <h3 css={overviewStyles.componentName}>{component.name}</h3>
                    <p css={overviewStyles.componentDescription}>{component.description}</p>
                  </div>
                </div>
              ))}
          </div>
        </section>
      )}

      {selectedCategory !== 'atoms' && (
        <section css={overviewStyles.section}>
          <h2 css={overviewStyles.sectionTitle}>
            Molecules <span css={overviewStyles.sectionCount}>{moleculesCount}</span>
          </h2>
          <div css={overviewStyles.grid}>
            {filteredComponents
              .filter((component) => component.category === 'molecules')
              .map((component) => (
                <div
                  key={component.name}
                  css={overviewStyles.componentCard}
                  onClick={() => handleComponentClick(component.storyPath)}
                  onKeyDown={(event) => handleKeyDown(event, component.storyPath)}
                  tabIndex={0}
                  role="button"
                  aria-label={`Open ${component.name} component story`}
                >
                  <div css={overviewStyles.componentPreview}>{component.preview}</div>
                  <div css={overviewStyles.componentInfo}>
                    <h3 css={overviewStyles.componentName}>{component.name}</h3>
                    <p css={overviewStyles.componentDescription}>{component.description}</p>
                  </div>
                </div>
              ))}
          </div>
        </section>
      )}

      {filteredComponents.length === 0 && (
        <div css={overviewStyles.noResults}>
          <EmptyState
            title="No components found"
            description={`No components match "${searchTerm}". Try adjusting your search terms.`}
            size="medium"
          />
        </div>
      )}
    </div>
  );
};

const overviewStyles = {
  container: css`
    max-width: none;
    margin: 0;
    padding: 0;
    background: ${colorTokens.background.white};
    min-height: 100vh;
  `,
  header: css`
    margin-bottom: ${spacing[40]};
    padding: ${spacing[32]} ${spacing[24]} 0;
  `,
  title: css`
    ${typography.heading5('medium')}
    color: ${colorTokens.text.title};
    margin-bottom: ${spacing[16]};
  `,
  subtitle: css`
    ${typography.body()}
    color: ${colorTokens.text.subdued};
    max-width: 600px;
    line-height: 1.6;
  `,
  link: css`
    color: ${colorTokens.text.primary};
    text-decoration: none;

    &:hover {
      text-decoration: underline;
    }
  `,
  controls: css`
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: ${spacing[32]};
    gap: ${spacing[16]};
    flex-wrap: wrap;
    padding: 0 ${spacing[24]};

    @media (max-width: 768px) {
      flex-direction: column;
      align-items: stretch;
    }
  `,
  searchContainer: css`
    position: relative;
    flex: 1;
    max-width: 400px;
  `,
  section: css`
    margin-bottom: ${spacing[48]};
    padding: 0 ${spacing[24]};
  `,
  sectionTitle: css`
    ${typography.heading6('medium')}
    color: ${colorTokens.text.title};
    margin-bottom: ${spacing[24]};
    display: flex;
    align-items: center;
    gap: ${spacing[12]};
  `,
  sectionCount: css`
    ${typography.caption()}
    background: ${colorTokens.background.default};
    color: ${colorTokens.text.subdued};
    padding: ${spacing[4]} ${spacing[8]};
    border-radius: ${borderRadius[4]};
    font-weight: 500;
  `,
  grid: css`
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: ${spacing[16]};

    @media (max-width: 768px) {
      grid-template-columns: 1fr;
    }
  `,
  componentCard: css`
    background: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    padding: ${spacing[16]};
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover {
      border-color: ${colorTokens.stroke.default};
      box-shadow: 0 4px 12px ${colorTokens.stroke.default}33;
      transform: translateY(-2px);
    }

    &:focus {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 2px;
    }
  `,
  componentPreview: css`
    background: ${colorTokens.background.default};
    border-radius: ${borderRadius[4]};
    padding: ${spacing[16]};
    margin-bottom: ${spacing[12]};
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  `,
  componentInfo: css`
    text-align: left;
  `,
  componentName: css`
    ${typography.body('medium')}
    color: ${colorTokens.text.title};
    margin-bottom: ${spacing[4]};
  `,
  componentDescription: css`
    ${typography.small()}
    color: ${colorTokens.text.subdued};
    line-height: 1.4;
  `,
  noResults: css`
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 300px;
    padding: 0 ${spacing[24]};
  `,
};
