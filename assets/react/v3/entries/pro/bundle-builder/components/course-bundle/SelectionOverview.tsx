import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useFormContext, useWatch } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';
import { type BundleFormData } from '@BundleBuilderServices/bundle';
import { colorTokens, fontWeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

const SelectionOverview = () => {
  const form = useFormContext<BundleFormData>();
  const overview = useWatch({
    control: form.control,
    name: 'overview',
  });

  const iconMap = {
    total_duration: 'clock',
    total_quizzes: 'questionCircle',
    total_video_contents: 'videoCamera',
    total_resources: 'download',
    certificate: 'certificate',
  } as const;

  const contentMap = {
    total_duration: __('Minutes Total Duration', 'tutor'),
    total_quizzes: __('Quiz Papers', 'tutor'),
    total_video_contents: __('Video Content', 'tutor'),
    total_resources: __('Downloadable Resources', 'tutor'),
    certificate: __('Certification of completion', 'tutor'),
  } as const;

  return (
    <div css={styles.wrapper}>
      <div css={styles.title}>{__('Selection Overview', 'tutor')}</div>
      <div css={styles.overview}>
        {Object.keys(iconMap).map((key) => {
          const value = overview[key as keyof typeof overview];
          return (
            <Show when={value} key={key}>
              <div css={styles.overviewItem}>
                <SVGIcon name={iconMap[key as keyof typeof iconMap]} width={32} height={32} />
                <Show when={value && typeof value !== 'boolean'}>
                  <span>{key === 'total_duration' ? String(value).replace(/:\d{2}$/, '') : value}</span>
                </Show>
                <span>{contentMap[key as keyof typeof contentMap]}</span>
              </div>
            </Show>
          );
        })}

        {/* @TODO: need an efficient way */}
        {/* {Object.entries(overview).map(([key, value]) => (
          <Show when={isDefined(value)} key={key}>
            <div css={styles.overviewItem}>
              <SVGIcon name={iconMap[key as keyof typeof iconMap]} width={32} height={32} />
              <Show when={value && typeof value !== 'boolean'}>
                <span>{value}</span>
              </Show>
              <span>{contentMap[key as keyof typeof contentMap]}</span>
            </div>
          </Show>
        ))} */}
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
    ${typography.caption()};

    svg {
      color: ${colorTokens.icon.default};
      flex-shrink: 0;
    }

    span:first-of-type:not(:only-of-type) {
      font-weight: ${fontWeight.semiBold};
      flex-shrink: 0;
    }
  `,
};
