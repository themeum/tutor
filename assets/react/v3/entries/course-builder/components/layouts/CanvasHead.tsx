import { css } from '@emotion/react';
import type { ReactElement } from 'react';
import { useNavigate } from 'react-router-dom';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';

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
          <Button variant="text" buttonCss={styles.button} onClick={handleBackClick}>
            <SVGIcon name="back" width={32} height={32} />
          </Button>
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
  button: css`
    padding: 0;
    border-radius: ${borderRadius[2]};
  `,
  title: css`
    ${typography.heading6('medium')};
  `,
};
