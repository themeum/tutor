import { IconCollection } from '@TutorShared/icons/types';
import { type SerializedStyles, css } from '@emotion/react';
import { useEffect, useState } from 'react';

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

const SVGIcon = ({ name, width = 16, height = 16, style, isColorIcon = false, ...rest }: SVGIconProps) => {
  const [icon, setIcon] = useState<Icon | null>(null);

  useEffect(() => {
    // Dynamically import the icon based on the name
    import(`@TutorShared/icons/icon-list/${name}`)
      .then((iconModule) => {
        setIcon(iconModule.default);
      })
      .catch((err) => {
        console.error(`Error loading icon "${name}":`, err);
      });
  }, [name]);

  if (!icon) {
    return null; // Optionally render a loading state or fallback icon
  }

  const additionalAttributes = {
    ...(isColorIcon && { 'data-colorize': true }),
    ...rest,
  };

  return (
    <svg
      css={[style, { width, height }, styles.svg({ isColorIcon })]}
      xmlns="http://www.w3.org/2000/svg"
      viewBox={icon.viewBox}
      dangerouslySetInnerHTML={{ __html: icon.icon }}
      {...additionalAttributes}
    />
  );
};

export default SVGIcon;

const styles = {
  svg: ({ isColorIcon = false }) => css`
    transition: filter 0.3s ease-in-out;
    ${isColorIcon &&
    css`
      filter: grayscale(100%);
    `}
  `,
};
