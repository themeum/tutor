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
  icon: string;
}

const iconCache: Record<string, Icon> = {};

const SVGIcon = memo(({ name, width = 16, height = 16, style, isColorIcon = false, ...rest }: SVGIconProps) => {
  const [icon, setIcon] = useState<Icon | null>(iconCache[name] || null);
  const [isLoading, setIsLoading] = useState(!iconCache[name]);

  useEffect(() => {
    if (iconCache[name]) {
      setIcon(iconCache[name]);
      return;
    }

    setIsLoading(true);

    const fileName = name.trim().replace(/[A-Z]/g, (m) => '-' + m.toLowerCase());
    fetch(`${tutorConfig.tutor_url}/assets/icons/${fileName}.svg`)
      .then((res) => res.text())
      .then((svgText) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(svgText, 'image/svg+xml');
        const svgEl = doc.querySelector('svg');
        const viewBox = svgEl?.getAttribute('viewBox') || `0 0 ${width} ${height}`;
        const innerHTML = svgEl?.innerHTML || '';

        const loadedIcon = { viewBox, icon: innerHTML };
        iconCache[name] = loadedIcon;
        setIcon(loadedIcon);
      })
      .catch((err) => {
        // eslint-disable-next-line no-console
        console.error(`Error loading icon "${name}":`, err);
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
      {...additionalAttributes}
      role="presentation"
      aria-hidden={true}
      dangerouslySetInnerHTML={{ __html: icon ? icon.icon : '' }}
    />
  );
});

SVGIcon.displayName = 'SVGIcon';

export default SVGIcon;

const styles = {
  svg: ({ isColorIcon = false }) => css`
    transition: filter 0.3s ease-in-out;

    ${isColorIcon &&
    css`
      filter: grayscale(100%);
    `};
  `,
};
