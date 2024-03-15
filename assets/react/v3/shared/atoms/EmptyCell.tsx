import { colorPalate } from '@Config/styles';
import { css } from '@emotion/react';

const EmptyCell = ({ comment }: { comment: string }) => {
	return (
		<div css={styles.wrapper}>
			<span css={styles.srOnly}>{comment}</span>
			<span css={styles.indicator} />
		</div>
	);
};

export default EmptyCell;

const styles = {
	wrapper: css`
    display: inline-block;
  `,
	srOnly: css`
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
  `,
	indicator: css`
    display: inline-block;
    width: 12px;
    height: 1px;
    background-color: ${colorPalate.icon.neutral};
  `,
};
