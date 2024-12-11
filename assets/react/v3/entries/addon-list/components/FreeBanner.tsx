import Button from '@/v3/shared/atoms/Button';
import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import { borderRadius, Breakpoint, colorTokens, lineHeight, spacing } from '@/v3/shared/config/styles';
import { typography } from '@/v3/shared/config/typography';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import bannerImage from '@Images/free-addons-banner.webp';
import config from '@/v3/shared/config/config';

function FreeBanner() {
  return (
    <div css={styles.wrapper}>
      <div css={styles.image}>
        <img src={bannerImage} alt={__('Get all addons banner', 'tutor')} />
      </div>
      <div css={styles.content}>
        <h6 css={styles.title}>{__('Get All of Add-Ons for a Single Price', 'tutor')}</h6>
        <p css={styles.paragraph}>
          {
            // prettier-ignore
            __( 'Unlock all add-ons with one payment! Easily enable them and customize for enhanced functionality and usability. Tailor your experience effortlessly.', 'tutor' )
          }
        </p>
        <Button
          icon={<SVGIcon name="crown" width={24} height={24} />}
          onClick={() => {
            window.open(config.TUTOR_PRICING_PAGE, '_blank', 'noopener');
          }}
        >
          {__('Get Tutor LMS Pro', 'tutor')}
        </Button>
      </div>
    </div>
  );
}

export default FreeBanner;

const styles = {
  wrapper: css`
    background-color: ${colorTokens.background.white};
    border-radius: ${spacing[6]};
    padding: ${spacing[32]};
    margin-bottom: ${spacing[32]};

    display: flex;
    align-items: center;
    gap: ${spacing[32]};

    ${Breakpoint.mobile} {
      flex-direction: column;
      padding: ${spacing[24]};
    }
  `,
  image: css`
    img {
      width: 100%;
      max-width: 235px;
      border-radius: ${borderRadius[6]};

      ${Breakpoint.mobile} {
        max-width: 100%;
      }
    }
  `,
  content: css`
    max-width: 510px;
  `,
  title: css`
    ${typography.heading6('semiBold')};
    line-height: ${lineHeight[28]};
    margin-bottom: ${spacing[8]};
  `,
  paragraph: css`
    ${typography.caption('regular')};
    line-height: ${lineHeight[22]};
    color: ${colorTokens.text.subdued};
    margin-bottom: ${spacing[20]};
  `,
};
