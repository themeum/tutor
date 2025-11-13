import { tutorConfig } from '@TutorShared/config/config';
import { type IconCollection } from '@TutorShared/icons/types';
import { type SerializedStyles, css } from '@emotion/react';
import { memo, useEffect, useState } from 'react';

interface SVGIconProps {
  name: IconCollection;
  width?: number;
  height?: number;
  style?: SerializedStyles;
  isColorIcon?: boolean;
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

const SVGIcon = ({ name, width = 16, height = 16, style, isColorIcon = false, ...rest }: SVGIconProps) => {
  const [icon, setIcon] = useState<Icon | null>(iconCache[name]?.icon || null);
  const [isLoading, setIsLoading] = useState(!iconCache[name]?.icon);

  useEffect(() => {
    if (iconCache[name]?.icon) {
      setIcon(iconCache[name]!.icon!);
      setIsLoading(false);
      return;
    }

    setIsLoading(true);

    fetchIcon(name, width, height)
      .then((loadedIcon) => {
        setIcon(loadedIcon);
      })
      .catch(() => {
        setIcon(null);
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, [name, width, height]);

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

function fetchIcon(name: string, width: number, height: number): Promise<Icon> {
  if (iconCache[name]?.icon) {
    // Icon already loaded
    return Promise.resolve(iconCache[name]!.icon!);
  }

  if (iconCache[name]?.promise) {
    // Fetch already in progress, return existing promise
    return iconCache[name]!.promise!;
  }

  const fileName = name.trim().replace(/[A-Z0-9]/g, (m) => '-' + m.toLowerCase());
  const url = `${tutorConfig.tutor_url}/assets/icons/${fileName}.svg`;

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
      iconCache[name] = { icon: loadedIcon };
      return loadedIcon;
    })
    .catch((err) => {
      iconCache[name] = { error: err };
      throw err;
    });

  iconCache[name] = { loading: true, promise };
  return promise;
}

SVGIcon.displayName = 'SVGIcon';

export default memo(SVGIcon, (prev, next) => {
  return (
    prev.name === next.name &&
    prev.height === next.height &&
    prev.width === next.height &&
    prev.isColorIcon === next.isColorIcon &&
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
