import Container from "@Components/Container";
import { colorTokens, spacing } from "@Config/styles";
import { css } from "@emotion/react";
import Card from "@Molecules/Card";
import Topbar, { TOPBAR_HEIGHT } from "./Topbar";

function Layout() {
	return <div css={styles.wrapper}>
		<Topbar />
		<Container>
			<div css={styles.content}>
				<div css={styles.left}>
					<Card title="Order Summary">
						Lorem ipsum dolor sit amet consectetur adipisicing elit. Odio, adipisci modi et unde quaerat reprehenderit voluptate neque dicta esse laborum vero rem quam ullam soluta aut id non corrupti harum.
					</Card>
				</div>
				<div css={styles.right}>
					<Card title="Student">
						Right side
					</Card>
				</div>
			</div>
		</Container>
	</div>;
}

export default Layout;

const styles = {
	wrapper: css`
		background-color: ${colorTokens.background.default};
	`,
	content: css`
		min-height: calc(100vh - ${TOPBAR_HEIGHT}px);
		width: 100%;
		display: flex;
		gap: ${spacing[24]};
		margin-top: ${spacing[32]};
	`,
	left: css`
		max-width: 736px;
		width: 100%;
		flex-shrink: 0;
	`,
	right: css`
		width: 100%;
	`
}