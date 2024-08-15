import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import Container from '@Components/Container';
import { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { Enrollment } from '@EnrollmentServices/enrollment';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

export const TOPBAR_HEIGHT = 80;

function Topbar() {
  const form = useFormContext();

  async function handleSubmit<Enrollment>(data: Enrollment) {
    console.log(data);
  }

  function handleGoBack() {
    window.location.href = `${tutorConfig.home_url}/wp-admin/admin.php?page=enrollments`;
  }

  return (
    <div css={styles.wrapper}>
      <Container>
        <div css={styles.innerWrapper}>
          <div css={styles.left}>
            <button type="button" css={styles.backButton} onClick={handleGoBack}>
              <SVGIcon name="arrowLeft" width={26} height={26} />
            </button>
            <div>
              <div css={styles.headerContent}>
                <h4 css={typography.heading5('medium')}>{__('Manual Enrolment', 'tutor')}</h4>
              </div>
            </div>
          </div>
          <div css={styles.right}>
            <Button variant="primary" onClick={form.handleSubmit(handleSubmit)}>
              {__('Enroll Students', 'tutor')}
            </Button>
          </div>
        </div>
      </Container>
    </div>
  );
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
  headerContent: css`
    display: flex;
    align-items: center;
    gap: ${spacing[16]};
  `,
  left: css`
    display: flex;
    gap: ${spacing[16]};
  `,
  right: css`
    display: flex;
    gap: ${spacing[12]};
  `,
  updateMessage: css`
    ${typography.body()};
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
    transition: color 0.3s ease-in-out;

    :hover {
      color: ${colorTokens.icon.hover};
    }
  `,
};
