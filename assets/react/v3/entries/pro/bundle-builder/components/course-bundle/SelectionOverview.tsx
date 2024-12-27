import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import SVGIcon from '@Atoms/SVGIcon';

import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

const SelectionOverview = () => {
  const overviewData = {
    duration: '3',
    quiz: 5,
    video: 10,
    certificate: true,
    attachments: 2,
  };

  const iconMap = {
    duration: 'clock',
    quiz: 'questionCircle',
    video: 'videoCamera',
    attachments: 'download',
    certificate: 'certificate',
  } as const;

  const contentMap = {
    duration: __('Minutes Total Duration', 'tutor'),
    quiz: __('Quiz Papers', 'tutor'),
    video: __('Video Content', 'tutor'),
    attachments: __('Downloadable Resources', 'tutor'),
    certificate: __('Certification of completion', 'tutor'),
  } as const;

  return (
    <div css={styles.wrapper}>
      <div css={styles.title}>{__('Selection Overview', 'tutor')}</div>
      <div css={styles.overview}>
        {Object.entries(overviewData).map(([key, value]) => (
          <Show when={value} key={key}>
            <div css={styles.overviewItem}>
              <SVGIcon name={iconMap[key as keyof typeof iconMap]} width={32} height={32} />
              <span>{value}</span>
              <span>{contentMap[key as keyof typeof contentMap]}</span>
            </div>
          </Show>
        ))}
      </div>
    </div>
  );
};

export default SelectionOverview;

const styles = {
  wrapper: css`
    padding: ${spacing[12]} ${spacing[20]} 0 ${spacing[20]};
  `,
  title: css`
    ${typography.body('medium')};
    padding-bottom: ${spacing[12]};
  `,
  overview: css`
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: ${spacing[4]};
  `,
  overviewItem: css`
    ${styleUtils.display.flex()};
    gap: ${spacing[8]};
    align-items: center;

    svg {
      color: ${colorTokens.icon.default};
      flex-shrink: 0;
    }

    span:first-of-type {
      ${typography.body('semiBold')};
      flex-shrink: 0;
    }
  `,
};
