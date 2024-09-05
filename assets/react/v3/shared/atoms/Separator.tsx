import { colorTokens } from '@Config/styles';
import { type VariantProps, createVariation } from '@Utils/create-variation';
import { css } from '@emotion/react';
import React from 'react';

interface SeparatorProps extends React.HTMLAttributes<HTMLDivElement>, VariantProps<typeof separatorVariant> {}

const Separator = React.forwardRef<HTMLDivElement, SeparatorProps>(({ className, variant }, ref) => {
  return <div className={className} ref={ref} css={separatorVariant({ variant })} />;
});

Separator.displayName = 'Separator';

export { Separator };

const styles = {
  horizontal: css`
		height: 1px;
		width: 100%;
	`,
  vertical: css`
		height: 100%;
		width: 1px;
	`,
  base: css`
		flex-shrink: 0;
		background-color: ${colorTokens.stroke.divider};
	`,
};

const separatorVariant = createVariation(
  {
    variants: {
      variant: {
        horizontal: styles.horizontal,
        vertical: styles.vertical,
      },
    },
    defaultVariants: {
      variant: 'horizontal',
    },
  },
  styles.base,
);
