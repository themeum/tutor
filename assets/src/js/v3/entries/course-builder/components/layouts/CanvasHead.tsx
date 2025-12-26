import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import type { ReactElement } from 'react';
import { useNavigate } from 'react-router-dom';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { isRTL } from '@TutorShared/config/constants';
import { borderRadius, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';

type CanvasHeadProps = {
  title: string;
  backUrl?: string;
  rightButton?: ReactElement;
  isExternalUrl?: boolean;
};

const CanvasHead = ({ title, backUrl, rightButton, isExternalUrl }: CanvasHeadProps) => {
  const navigate = useNavigate();

  const handleBackClick = () => {
    if (backUrl) {
      if (isExternalUrl) {
        window.location.href = backUrl;
        return;
      }
      navigate(backUrl);
    } else {
      navigate(-1);
    }
  };

  return (
    <div css={styles.wrapper}>
      <div css={styles.left}>
        <Show when={backUrl}>
          <Button
            isIconOnly
            size="small"
            variant="text"
            aria-label={__('Back', 'tutor')}
            buttonCss={styles.button({ isRTL: isRTL })}
            onClick={handleBackClick}
            icon={<SVGIcon name="back" width={32} height={32} />}
          />
        </Show>
        <h6 css={styles.title}>{title}</h6>
      </div>
      {rightButton}
    </div>
  );
};

export default CanvasHead;

const styles = {
  wrapper: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
  `,
  left: css`
    display: flex;
    align-items: center;
    gap: ${spacing[16]};
  `,
  button: ({ isRTL }: { isRTL: boolean }) => css`
    padding: 0;
    border-radius: ${borderRadius[2]};

    ${isRTL &&
    css`
      transform: rotate(180deg);
    `}
  `,
  title: css`
    ${typography.heading6('medium')};
  `,
};
