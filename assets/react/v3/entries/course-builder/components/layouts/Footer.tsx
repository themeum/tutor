import Button, { ButtonSize, ButtonVariant } from '@Atoms/Button';
import { colorPalate, colorTokens, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

type FooterProps = {
  completion: number;
  onNextClick: () => void;
  onPrevClick: () => void;
};

const Footer = ({ completion, onNextClick, onPrevClick }: FooterProps) => {
  return (
    <div css={styles.wrapper(completion)}>
      <div css={styles.buttonWrapper}>
        <Button variant="secondary" size="small" onClick={onPrevClick}>
          {__('Previous', 'tutor')}
        </Button>
        <Button variant="secondary" size="small" onClick={onNextClick}>
          {__('Next', 'tutor')}
        </Button>
      </div>
    </div>
  );
};

export default Footer;

const styles = {
  wrapper: (completion: number) => css`
    background-color: ${colorTokens.primary[30]};
    padding: ${spacing[12]} ${spacing[16]};
    position: relative;

    &::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      background-color: ${colorTokens.color.black[10]};
      height: 2px;
      width: 100%;
    }

    &::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      background-color: ${colorTokens.primary[80]};
      height: 2px;
      width: ${completion}%;
      transition: 0.35s ease-in-out;
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
