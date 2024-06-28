import Button from "@Atoms/Button";
import SVGIcon from "@Atoms/SVGIcon";
import Container from "@Components/Container";
import { borderRadius, colorTokens, spacing } from "@Config/styles";
import { typography } from "@Config/typography";
import { css } from "@emotion/react";
import { styleUtils } from "@Utils/style-utils";

export const TOPBAR_HEIGHT = 96;

function Topbar() {
	return <div css={styles.wrapper}>
		<Container>
			<div css={styles.innerWrapper}>
				<div css={styles.left}>
					<button type="button" css={styles.backButton}>
						<SVGIcon name="arrowLeft" width={26} height={26} />
					</button>
					<div>
						<div css={css`
								display: flex;
								align-items: center;
								gap: ${spacing[16]};
							`}>
							<h4 css={typography.heading5('medium')}>Order #45</h4>
							<span>Paid</span>
							<span>Partially Refunded</span>
							<span>Fully Refunded</span>
						</div>
						<p css={styles.updateMessage}>Updated by Jhon Doe Today at 12:24 pm</p>
					</div>
				</div>
				<Button variant="tertiary">Cancel Order</Button>
			</div>
		</Container>
	</div>;
}

export default Topbar;

const styles = {
	wrapper: css`
		height: ${TOPBAR_HEIGHT}px;
		background: ${colorTokens.background.white};
	`,
	innerWrapper: css`
		display: flex;
		align-items: center;
		justify-content: space-between;
		height: 100%;
	`,
	left: css`
		display: flex;
		gap: ${spacing[16]};
	`,
	updateMessage: css`
		${typography.tiny()};
		color: ${colorTokens.text.subdued};
	`,
	backButton: css`
		${styleUtils.resetButton};
		background-color: transparent;
		width: 32px;
		height: 32px;
		display: flex;
		align-items: center;
		justify-content: center;
		border: 1px solid ${colorTokens.border.neutral};
		border-radius: ${borderRadius[4]};
		color: ${colorTokens.icon.default};
		transition: color .3s ease-in-out;

		:hover {
			color: ${colorTokens.icon.hover};
		}
	`
}