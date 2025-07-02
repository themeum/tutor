import bannerImage from '@SharedImages/free-addons-banner.png';
import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import config from '@TutorShared/config/config';
import { colorTokens, lineHeight, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

function FreeBanner() {
  return (
    <div css={styles.wrapper}>
      <div css={styles.content}>
        <h6 css={styles.title}>{__('Get All of Add-Ons for a Single Price', 'tutor')}</h6>
        <p css={styles.paragraph}>
          {
            // prettier-ignore
            __( 'Unlock all add-ons with one payment! Easily enable them and customize for enhanced functionality and usability. Tailor your experience effortlessly.', 'tutor' )
          }
        </p>
        <Button
          variant="secondary"
          size="large"
          buttonCss={styles.button}
          icon={<SVGIcon name="crown" width={24} height={24} />}
          onClick={() => {
            window.open(config.TUTOR_PRICING_PAGE, '_blank', 'noopener');
          }}
        >
          {__('Upgrade to Pro', 'tutor')}
        </Button>
      </div>
    </div>
  );
}

export default FreeBanner;

const styles = {
  wrapper: css`
    background-color: ${colorTokens.background.brand};
    background-image: url(${bannerImage});
    background-size: cover;
    background-position: center;
    border-radius: ${spacing[12]};
    padding: 82px ${spacing[32]};
    margin-bottom: ${spacing[32]};
  `,
  content: css`
    max-width: 550px;
    margin: 0 auto;
    text-align: center;
  `,
  title: css`
    ${typography.heading4('bold')};
    color: ${colorTokens.text.white};
    margin-bottom: ${spacing[12]};
  `,
  paragraph: css`
    ${typography.body('regular')};
    line-height: ${lineHeight[24]};
    color: ${colorTokens.text.white};
    margin-bottom: ${spacing[48]};
  `,
  button: css`
    width: 394px;
    max-width: 100%;
    height: 56px;
    color: ${colorTokens.color.black.main};
  `,
};
