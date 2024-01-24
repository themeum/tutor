import Button, { ButtonVariant } from "@Atoms/Button";
import SVGIcon from "@Atoms/SVGIcon";
import {
  borderRadius,
  colorPalate,
  colorPalateTutor,
  fontSize,
  fontWeight,
  headerHeight,
  spacing,
  zIndex,
} from "@Config/styles";
import { css } from "@emotion/react";
import { styleUtils } from "@Utils/style-utils";
import { __ } from "@wordpress/i18n";

const Header = () => {
  return (
    <div css={styles.wrapper}>
      <div>Logo</div>
      <div css={styles.headerRight}>
        <Button variant={ButtonVariant.plain}>
          {__("Save as Draft", "tutor")}
        </Button>
        <Button variant={ButtonVariant.secondary}>
          {__("Preview", "tutor")}
        </Button>
        <Button variant={ButtonVariant.primary}>
          {__("Publish", "tutor")}
        </Button>
        <button
          type="button"
          css={styles.closeButton}
          onClick={() => {
            window.location.href = `index.php`;
          }}
        >
          <SVGIcon name="timesAlt" />
        </button>
      </div>
    </div>
  );
};

export default Header;

const styles = {
  wrapper: css`
    height: ${headerHeight}px;
    width: 100%;
    background-color: ${colorPalateTutor.background.white};
    border-bottom: 1px solid ${colorPalateTutor.stroke.divider};
    padding: ${spacing[20]} ${spacing[32]} ${spacing[20]} ${spacing[56]};
    display: flex;
    align-items: center;
    justify-content: space-between;
  `,
  headerRight: css`
    display: flex;
    align-items: center;
    gap: ${spacing[12]};
  `,
  closeButton: css`
    ${styleUtils.resetButton};
    cursor: pointer;
  `,
};
