import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';

interface EmptyImageCardProps {
  placeholder?: string;
}

const EmptyImageCard = ({ placeholder }: EmptyImageCardProps) => {
  return (
    <span css={styles.container}>
      <SVGIcon name="storeImage" width={26} height={20} />
      {placeholder && <span css={styles.text}>{placeholder}</span>}
    </span>
  );
};

const styles = {
  container: css`
    background: ${colorTokens.background.default};
    border: 1px dashed ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[8]};
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 64px;
    width: 64px;
  `,
  text: css`
    color: ${colorTokens.text.subdued};
    ${typography.body()}
    margin-top: ${spacing[12]};
  `,
};

export default EmptyImageCard;
