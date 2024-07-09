import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';

interface AvatarProps {
  image?: string;
  name: string;
}

const generateAcronym = (name: string) => {
	const parts = name.split(/\s+/);
	if (parts.length === 1) {
		return parts[0].charAt(0);
	}

	if (parts.length > 1) {
		return parts.slice(0, 2).map(part => part.charAt(0)).join('');
	}
	
	return '';
}

export function Avatar({ image, name }: AvatarProps) {
  if (!image) {
    return <AvatarFallback name={name} />;
  }

  return (
    <div css={styles.wrapper}>
      <div css={styles.avatar}>
        <img src={image} alt={name} />
      </div>
      <span css={styles.name}>{name}</span>
    </div>
  );
}

export function AvatarFallback({ name }: { name: string }) {
  return (
    <div css={styles.wrapper}>
      <div css={styles.placeholder}>{generateAcronym(name)}</div>
      <span css={styles.name}>{name}</span>
    </div>
  );
}

const styles = {
  avatar: css`
		width: 32px;
		height: 32px;
		position: relative;
		border-radius: ${borderRadius.circle};
		overflow: hidden;
		border: 1px solid ${colorTokens.stroke.border};

		img {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			object-fit: cover;
		}
	`,
  wrapper: css`
		display: flex;
		align-items: center;
		gap: ${spacing[8]};
	`,
  placeholder: css`
		width: 32px;
		height: 32px;
		background-color: ${colorTokens.action.primary.wp};
		display: flex;
		justify-content: center;
		align-items: center;
		border-radius: ${borderRadius.circle};
		border: 1px solid ${colorTokens.stroke.border};
		color: ${colorTokens.text.white};
		text-transform: uppercase;
	`,
  name: css`
		${typography.body()};
		color: ${colorTokens.brand.blue};
	`,
};
