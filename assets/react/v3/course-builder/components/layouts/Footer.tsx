import Button, { ButtonVariant } from "@Atoms/Button";
import { colorPalate, colorPalateTutor, spacing } from "@Config/styles";
import { css } from "@emotion/react";

type FooterProps = {
  completion: number;
}

const Footer = ({ completion }: FooterProps) => {
  return (
    <div css={styles.wrapper(completion)}>
      <div css={styles.buttonWrapper}>
        <Button variant={ButtonVariant.secondary}>Previous</Button>
        <Button variant={ButtonVariant.secondary}>Next</Button>
      </div>
    </div>
  );
};

export default Footer;

const styles = {
  wrapper: (completion: number) => css`
    background-color: ${colorPalateTutor.primary[30]};
    padding: ${spacing[12]} ${spacing[16]};
    position: relative;

    &::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      background-color: ${colorPalateTutor.color.black[10]};
      height: 2px;
      width: 100%;
    }

    &::after {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      background-color: ${colorPalateTutor.primary[80]};
      height: 2px;
      width: ${completion}%;
    }
  `,
  buttonWrapper: css`
    max-width: 1000px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
  `,
};
