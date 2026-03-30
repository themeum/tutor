import { type SerializedStyles, css } from '@emotion/react';
import { memo, useEffect, useState } from 'react';

import { tutorConfig } from '@TutorShared/config/config';
import { type IconCollection } from '@TutorShared/icons/types';
import { type LearningMode } from '@TutorShared/utils/types';

interface SVGIconProps {
  name: IconCollection;
  width?: number;
  height?: number;
  style?: SerializedStyles;
  isColorIcon?: boolean;
  learningMode?: LearningMode;
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

const getResolvedLearningMode = (learningMode?: LearningMode): LearningMode => {
  return learningMode ?? tutorConfig.settings?.learning_mode ?? 'classic';
};

const getFileName = (name: string) => {
  return name.trim().replace(/[A-Z0-9]/g, (m) => '-' + m.toLowerCase());
};

const getIconCacheKey = (name: string, learningMode: LearningMode) => {
  return `${learningMode}:${name}`;
};

const SVGIcon = ({
  name,
  width = 16,
  height = 16,
  style,
  isColorIcon = false,
  learningMode,
  ...rest
}: SVGIconProps) => {
  const resolvedLearningMode = getResolvedLearningMode(learningMode);
  const cacheKey = getIconCacheKey(name, resolvedLearningMode);
  const [icon, setIcon] = useState<Icon | null>(iconCache[cacheKey]?.icon || null);
  const [isLoading, setIsLoading] = useState(!iconCache[cacheKey]?.icon);

  useEffect(() => {
    if (iconCache[cacheKey]?.icon) {
      setIcon(iconCache[cacheKey]!.icon!);
      setIsLoading(false);
      return;
    }

    setIsLoading(true);

    fetchIcon(name, width, height, resolvedLearningMode)
      .then((loadedIcon) => {
        setIcon(loadedIcon);
      })
      .catch(() => {
        setIcon(null);
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, [cacheKey, name, resolvedLearningMode, width, height]);

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

function fetchIcon(name: string, width: number, height: number, learningMode: LearningMode): Promise<Icon> {
  const cacheKey = getIconCacheKey(name, learningMode);

  if (iconCache[cacheKey]?.icon) {
    // Icon already loaded
    return Promise.resolve(iconCache[cacheKey]!.icon!);
  }

  if (iconCache[cacheKey]?.promise) {
    // Fetch already in progress, return existing promise
    return iconCache[cacheKey]!.promise!;
  }

  const fileName = getFileName(name);
  const iconUrls =
    learningMode === 'kids'
      ? [
          `${tutorConfig.tutor_url}/assets/icons/kids/${fileName}.svg`,
          `${tutorConfig.tutor_url}/assets/icons/${fileName}.svg`,
        ]
      : [`${tutorConfig.tutor_url}/assets/icons/${fileName}.svg`];

  const promise = (async () => {
    for (const url of iconUrls) {
      const res = await fetch(url);

      if (!res.ok) {
        continue;
      }

      const svgText = await res.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(svgText, 'image/svg+xml');
      const svgEl = doc.querySelector('svg');
      const viewBox = svgEl?.getAttribute('viewBox') || `0 0 ${width} ${height}`;
      const fill = svgEl?.getAttribute('fill') || 'none';
      const innerHTML = svgEl?.innerHTML || '';

      const loadedIcon = { viewBox, fill, icon: innerHTML };
      iconCache[cacheKey] = { icon: loadedIcon };
      return loadedIcon;
    }

    throw new Error(`Failed to load icon: ${name}`);
  })().catch((err) => {
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
    prev.learningMode === next.learningMode &&
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
