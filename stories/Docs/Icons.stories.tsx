import { css } from '@emotion/react';
import { IconGallery, IconItem, Meta } from '@storybook/addon-docs/blocks';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import TextInput from '@TutorShared/atoms/TextInput';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { icons, type IconCollection } from '@TutorShared/icons/types';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { copyToClipboard } from '@TutorShared/utils/util';
import { useState } from 'react';
import { type Meta as StorybookMeta, type StoryObj } from 'storybook-react-rsbuild';

const containerStyles = css`
  padding: ${spacing[24]};
  max-width: 1200px;
  margin: 0 auto;
  ${styleUtils.display.flex('column')};
  gap: ${spacing[20]};
`;

const headerStyles = css`
  text-align: center;
  margin-bottom: ${spacing[32]};
`;

const searchContainerStyles = css`
  display: flex;
  justify-content: center;
  margin-bottom: ${spacing[32]};
`;

const resultCountStyles = css`
  ${typography.caption('medium')}
  color: ${colorTokens.text.subdued};
  text-align: center;
  margin-bottom: ${spacing[16]};
`;

const noResultsStyles = css`
  ${typography.body('regular')}
  color: ${colorTokens.text.subdued};
  text-align: center;
  padding: ${spacing[48]};
`;

const iconItemWrapperStyles = css`
  position: relative;
  cursor: pointer;
  padding: ${spacing[12]};
  border-radius: ${borderRadius[8]};
  transition: all 0.2s ease;
  border: 1px solid transparent;
  ${styleUtils.display.flex()};
  align-items: center;
  justify-content: center;

  &:hover {
    background: ${colorTokens.background.hover};
    border-color: ${colorTokens.stroke.hover};
  }

  &:focus {
    outline: 2px solid ${colorTokens.brand.blue};
    outline-offset: 2px;
  }
`;

const copyFeedbackStyles = css`
  position: absolute;
  top: -30px;
  left: 50%;
  transform: translateX(-50%);
  background: ${colorTokens.background.black};
  color: ${colorTokens.text.white};
  padding: ${spacing[4]} ${spacing[8]};
  border-radius: ${borderRadius[4]};
  font-size: 12px;
  white-space: nowrap;
  z-index: 10;
  opacity: 0;
  animation: fadeInOut 2s ease-in-out;

  @keyframes fadeInOut {
    0% {
      opacity: 0;
      transform: translateX(-50%) translateY(-5px);
    }
    15% {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }
    85% {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }
    100% {
      opacity: 0;
      transform: translateX(-50%) translateY(-5px);
    }
  }
`;

const IconGalleryPage = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [copiedIcon, setCopiedIcon] = useState<string | null>(null);

  const handleSearchChange = (value: string) => {
    setSearchQuery(value);
  };

  const handleIconClick = async (iconName: string) => {
    try {
      await copyToClipboard(iconName);
      setCopiedIcon(iconName);

      // Reset copied state after 2 seconds
      setTimeout(() => {
        setCopiedIcon(null);
      }, 2000);
    } catch {
      // Fallback for older browsers or permission issues
      const textArea = document.createElement('textarea');
      textArea.value = iconName;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);

      setCopiedIcon(iconName);
      setTimeout(() => {
        setCopiedIcon(null);
      }, 2000);
    }
  };

  const handleIconKeyDown = (event: React.KeyboardEvent, iconName: string) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      handleIconClick(iconName);
    }
  };

  const filteredIcons = icons.filter((iconName) => iconName.toLowerCase().includes(searchQuery.toLowerCase()));

  return (
    <div css={containerStyles}>
      <Meta title="Design System/Icon Gallery" />

      <header css={headerStyles}>
        <h1 css={typography.heading1('bold')}>🎨 Icon Gallery</h1>
        <p
          css={[
            typography.body('regular'),
            css`
              color: ${colorTokens.text.subdued};
            `,
          ]}
        >
          Explore our complete collection of SVG icons. Click any icon to copy its name to clipboard.
        </p>
      </header>

      <div css={searchContainerStyles}>
        <TextInput variant="search" onChange={handleSearchChange} value={searchQuery} placeholder="Search icons..." />
      </div>

      {filteredIcons.length > 0 && (
        <div css={resultCountStyles}>
          Showing {filteredIcons.length} of {icons.length} icons
          {searchQuery && ` matching "${searchQuery}"`}
        </div>
      )}

      {filteredIcons.length === 0 && searchQuery && (
        <div css={noResultsStyles}>No icons found matching &quot;{searchQuery}&quot;. Try a different search term.</div>
      )}

      {filteredIcons.length > 0 && (
        <IconGallery>
          {filteredIcons.map((iconName) => (
            <IconItem key={iconName} name={copiedIcon === iconName ? `Copied!` : iconName}>
              <div
                css={iconItemWrapperStyles}
                onClick={() => handleIconClick(iconName)}
                onKeyDown={(event) => handleIconKeyDown(event, iconName)}
                tabIndex={0}
                role="button"
                aria-label={`Copy ${iconName} icon name to clipboard`}
                title={`Click to copy "${iconName}" to clipboard`}
              >
                <SVGIcon name={iconName as IconCollection} width={24} height={24} aria-hidden="true" />
                {copiedIcon === iconName && (
                  <div css={copyFeedbackStyles} aria-hidden="true">
                    Copied!
                  </div>
                )}
              </div>
            </IconItem>
          ))}
        </IconGallery>
      )}
    </div>
  );
};

const meta = {
  title: 'Docs/Icon Gallery',
  component: IconGalleryPage,
  parameters: {
    layout: 'fullscreen',
    docs: {
      page: () => <IconGalleryPage />,
    },
    controls: { disable: true },
  },
  tags: ['autodocs'],
} satisfies StorybookMeta<typeof IconGalleryPage>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Docs = {} satisfies Story;
