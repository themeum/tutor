/* eslint-disable @typescript-eslint/no-explicit-any */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
import { SerializedStyles } from '@emotion/react';
import { IconCollection } from '@Utils/types';
import { getIcon } from '@Utils/util';

interface SVGIconProps {
  name: IconCollection;
  width?: number;
  height?: number;
  style?: SerializedStyles;
}

const SVGIcon = ({ name, width = 16, height = 16, style }: SVGIconProps) => {
  const icon = getIcon(name);

  return (
    <svg
      css={[style, { width, height }]}
      xmlns="http://www.w3.org/2000/svg"
      viewBox={icon.viewBox}
      dangerouslySetInnerHTML={{ __html: icon.icon }}
    />
  );
};

export default SVGIcon;
