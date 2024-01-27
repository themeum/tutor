import { borderRadius, colorPalate, fontSize, lineHeight, spacing } from '@Config/styles';
import { css } from '@emotion/react';

interface StatusStyle {
  backgroundColor: string;
  color: string;
}

type StatusValue = 'active' | 'inactive' | 'expired';

interface StatusChipProps {
  status: StatusValue;
}

const StatusChip = ({ status }: StatusChipProps) => {
  const statusVariant = statusVariants(status);

  return <div css={styles.wrapper(statusVariant)}>{status}</div>;
};

export default StatusChip;

const styles = {
  wrapper: (statusVariants: StatusStyle) => css`
    font-size: ${fontSize[16]};
    line-height: ${lineHeight[20]};
    padding: 0 ${spacing[8]};
    background-color: ${statusVariants.backgroundColor};
    color: ${statusVariants.color};
    text-transform: capitalize;
    border-radius: ${borderRadius[50]};
    min-width: 60px;
  `,
};

const statusVariants = (status: StatusValue) => {
  switch (status) {
    case 'active':
      return {
        backgroundColor: colorPalate.surface.success.default,
        color: colorPalate.text.success,
      };
    case 'inactive':
      return {
        backgroundColor: colorPalate.surface.depressed,
        color: colorPalate.text.default,
      };
    case 'expired':
      return {
        backgroundColor: colorPalate.surface.critical.default,
        color: colorPalate.text.critical,
      };
    default:
      return {
        backgroundColor: colorPalate.surface.depressed,
        color: colorPalate.text.default,
      };
  }
};
