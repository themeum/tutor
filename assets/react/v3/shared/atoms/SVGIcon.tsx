import { type IconCollection } from '@TutorShared/icons/types';
import { type SerializedStyles, css } from '@emotion/react';
import { memo, useEffect, useState } from 'react';

interface SVGIconProps {
  name: IconCollection;
  width?: number;
  height?: number;
  style?: SerializedStyles;
  isColorIcon?: boolean;
  title?: string;
}

interface Icon {
  viewBox: string;
  icon: string;
}

const iconCache: Record<string, Icon> = {};

const SVGIcon = memo(({ name, width = 16, height = 16, style, isColorIcon = false, title, ...rest }: SVGIconProps) => {
  const [icon, setIcon] = useState<Icon | null>(iconCache[name] || null);
  const [isLoading, setIsLoading] = useState(!iconCache[name]);

  useEffect(() => {
    if (iconCache[name]) {
      setIcon(iconCache[name]);
      return;
    }

    setIsLoading(true);

    // Dynamically import the icon based on the name
    import(`@TutorShared/icons/icon-list/${name}`)
      .then((iconModule) => {
        const loadedIcon = iconModule.default;
        // Store in cache for future use
        iconCache[name] = loadedIcon;
        setIcon(loadedIcon);
      })
      .catch((err) => {
        console.error(`Error loading icon "${name}":`, err);
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, [name]);

  const additionalAttributes = {
    ...(isColorIcon && { 'data-colorize': true }),
    ...rest,
  };

  const viewBox = icon ? icon.viewBox : `0 0 ${width} ${height}`;

  return (
    <svg
      css={[style, { width, height }, styles.svg({ isColorIcon, isLoading })]}
      xmlns="http://www.w3.org/2000/svg"
      viewBox={viewBox}
      {...additionalAttributes}
      role={title ? 'img' : 'presentation'}
      aria-hidden={!title}
    >
      {title && <title>{title}</title>}
      {icon ? (
        <g dangerouslySetInnerHTML={{ __html: icon.icon }} />
      ) : (
        <rect width={width} height={height} fill="transparent" />
      )}
    </svg>
  );
});

SVGIcon.displayName = 'SVGIcon';

export default SVGIcon;

const styles = {
  svg: ({ isColorIcon = false, isLoading = false }) => css`
    transition:
      filter 0.3s ease-in-out,
      opacity 0.2s ease-in-out;
    ${isColorIcon &&
    css`
      filter: grayscale(100%);
    `}
    ${isLoading &&
    css`
      opacity: 0.6;
    `}
  `,
};
