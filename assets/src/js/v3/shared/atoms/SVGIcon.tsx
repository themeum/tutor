import { tutorConfig } from '@TutorShared/config/config';
import { useSVGIconConfig } from '@TutorShared/contexts/SVGIconConfigContext';
import { type IconCollection } from '@TutorShared/icons/types';
import { type SerializedStyles, css } from '@emotion/react';
import { memo, useEffect, useState } from 'react';

interface SVGIconProps {
  name: IconCollection;
  width?: number;
  height?: number;
  style?: SerializedStyles;
  isColorIcon?: boolean;
  ignoreKids?: boolean;
}

interface Icon {
  viewBox: string;
  fill: string;
  icon: string;
}

interface IconCacheEntry {
  icon?: Icon;
  loading?: boolean;
  promise?: Promise<Icon>;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  error?: any;
}

const iconCache: Record<string, IconCacheEntry> = {};

const SVGIcon = ({ name, width = 16, height = 16, style, isColorIcon = false, ignoreKids, ...rest }: SVGIconProps) => {
  const { disableKidsIcons } = useSVGIconConfig();
  const shouldIgnoreKids = ignoreKids ?? disableKidsIcons;
  const cacheKey = shouldIgnoreKids ? `${name}-ignoreKids` : name;
  const [icon, setIcon] = useState<Icon | null>(iconCache[cacheKey]?.icon || null);
  const [isLoading, setIsLoading] = useState(!iconCache[cacheKey]?.icon);

  useEffect(() => {
    if (iconCache[cacheKey]?.icon) {
      setIcon(iconCache[cacheKey]!.icon!);
      setIsLoading(false);
      return;
    }

    setIsLoading(true);

    fetchIcon(name, cacheKey, width, height, shouldIgnoreKids)
      .then((loadedIcon) => {
        setIcon(loadedIcon);
      })
      .catch(() => {
        setIcon(null);
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, [name, width, height, shouldIgnoreKids, cacheKey]);

  const additionalAttributes = {
    ...(isColorIcon && { 'data-colorize': true }),
    ...rest,
  };

  const viewBox = icon ? icon.viewBox : `0 0 ${width} ${height}`;
  const fill = icon ? icon.fill : 'none';

  if (!icon && !isLoading) {
    return (
      <svg viewBox={viewBox}>
        <rect width={width} height={height} fill="transparent" />
      </svg>
    );
  }

  return (
    <svg
      css={[style, { width, height }, styles.svg({ isColorIcon })]}
      xmlns="http://www.w3.org/2000/svg"
      viewBox={viewBox}
      fill={fill}
      {...additionalAttributes}
      role="presentation"
      aria-hidden={true}
      dangerouslySetInnerHTML={{ __html: icon ? icon.icon : '' }}
    />
  );
};

function fetchIcon(name: string, cacheKey: string, width: number, height: number, ignoreKids: boolean): Promise<Icon> {
  if (iconCache[cacheKey]?.icon) {
    // Icon already loaded
    return Promise.resolve(iconCache[cacheKey]!.icon!);
  }

  if (iconCache[cacheKey]?.promise) {
    // Fetch already in progress, return existing promise
    return iconCache[cacheKey]!.promise!;
  }

  const fileName = name
    .trim()
    .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
    .replace(/([a-zA-Z])(\d+)/g, '$1-$2')
    .toLowerCase();
  const hasKidsVariant = !ignoreKids && tutorConfig.is_kids_mode && tutorConfig.kids_icons_registry?.includes(fileName);

  const basePath = hasKidsVariant ? 'assets/icons/kids/' : 'assets/icons/';
  const url = `${tutorConfig.tutor_url}${basePath}${fileName}.svg`;

  const promise = fetch(url)
    .then((res) => {
      if (!res.ok) {
        throw new Error(`Failed to load icon: ${name}`);
      }
      return res.text();
    })
    .then((svgText) => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(svgText, 'image/svg+xml');
      const svgEl = doc.querySelector('svg');
      const viewBox = svgEl?.getAttribute('viewBox') || `0 0 ${width} ${height}`;
      const fill = svgEl?.getAttribute('fill') || 'none';
      const innerHTML = svgEl?.innerHTML || '';

      const loadedIcon = { viewBox, fill, icon: innerHTML };
      iconCache[cacheKey] = { icon: loadedIcon };
      return loadedIcon;
    })
    .catch((err) => {
      iconCache[cacheKey] = { error: err };
      throw err;
    });

  iconCache[cacheKey] = { loading: true, promise };
  return promise;
}

SVGIcon.displayName = 'SVGIcon';

export default memo(SVGIcon, (prev, next) => {
  return (
    prev.name === next.name &&
    prev.height === next.height &&
    prev.width === next.width &&
    prev.isColorIcon === next.isColorIcon &&
    prev.ignoreKids === next.ignoreKids &&
    prev.style?.name === next.style?.name
  );
});

const styles = {
  svg: ({ isColorIcon = false }) => css`
    transition: filter 0.3s ease-in-out;

    ${isColorIcon &&
    css`
      filter: grayscale(100%);
    `};
  `,
};
