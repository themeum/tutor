import { css } from '@emotion/react';
import { useIsFetching } from '@tanstack/react-query';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { Controller, useFormContext } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';
import FormCategoriesInput from '@Components/fields/FormCategoriesInput';
import FormImageInput from '@Components/fields/FormImageInput';
import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';

import BundlePricing from '@BundleBuilderComponents/course-bundle/BundlePricing';
import ScheduleOptions from '@BundleBuilderComponents/course-bundle/ScheduleOptions';
import { type BundleFormData } from '@BundleBuilderServices/bundle';
import { getBundleId } from '@BundleBuilderUtils/utils';
import { tutorConfig } from '@Config/config';
import { DateFormats, visibilityStatusOptions } from '@Config/constants';
import { borderRadius, Breakpoint, colorTokens, headerHeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

const bundleId = getBundleId();
const isTutorPro = !!tutorConfig.tutor_pro_url;
const isOpenAiEnabled = tutorConfig.settings?.chatgpt_enable === 'on';

const ribbonOptions = [
  {
    label: __('Show Discount % off', 'tutor'),
    value: 'in_percentage',
  },
  {
    label: sprintf(__('Show discount amount (%s)', 'tutor'), tutorConfig.tutor_currency.symbol),
    value: 'in_amount',
  },
  {
    label: __('Show None', 'tutor'),
    value: 'none',
  },
];

const BundleSidebar = () => {
  const form = useFormContext<BundleFormData>();
  const isBundleDetailsQueryFetching = useIsFetching({
    queryKey: ['CourseBundle', bundleId],
  });

  const postModified = form.watch('post_modified');
  const visibilityStatus = form.watch('visibility');

  return (
    <div css={styles.sidebar}>
      <div css={styles.statusAndDate}>
        <Controller
          name="visibility"
          control={form.control}
          render={(controllerProps) => (
            <FormSelectInput
              {...controllerProps}
              label={__('Visibility', 'tutor')}
              placeholder={__('Select visibility status', 'tutor')}
              options={visibilityStatusOptions}
              leftIcon={<SVGIcon name="eye" width={32} height={32} />}
              loading={!!isBundleDetailsQueryFetching && !controllerProps.field.value}
              onChange={() => {
                form.setValue('post_password', '');
              }}
            />
          )}
        />

        <Show when={postModified}>
          {(date) => (
            <div css={styles.updatedOn}>
              {sprintf(__('Last updated on %s', 'tutor'), format(new Date(date), DateFormats.dayMonthYear) || '')}
            </div>
          )}
        </Show>
      </div>

      <Show when={visibilityStatus === 'password_protected'}>
        <Controller
          name="post_password"
          control={form.control}
          rules={{
            required: __('Password is required', 'tutor'),
          }}
          render={(controllerProps) => (
            <FormInput
              {...controllerProps}
              label={__('Password', 'tutor')}
              placeholder={__('Enter password', 'tutor')}
              type="password"
              isPassword
              selectOnFocus
              loading={!!isBundleDetailsQueryFetching && !controllerProps.field.value}
            />
          )}
        />
      </Show>

      <ScheduleOptions />

      <Controller
        name="thumbnail"
        control={form.control}
        render={(controllerProps) => (
          <FormImageInput
            {...controllerProps}
            label={__('Featured Image', 'tutor')}
            buttonText={__('Upload Thumbnail', 'tutor')}
            infoText={sprintf(__('JPEG, PNG, GIF, and WebP formats, up to %s', 'tutor'), tutorConfig.max_upload_size)}
            generateWithAi={!isTutorPro || isOpenAiEnabled}
            loading={!!isBundleDetailsQueryFetching && !controllerProps.field.value}
          />
        )}
      />

      <BundlePricing />

      <Controller
        name="ribbon_type"
        control={form.control}
        render={(controllerProps) => (
          <FormSelectInput
            {...controllerProps}
            label={__('Select ribbon to display', 'tutor')}
            placeholder={__('Select ribbon', 'tutor')}
            options={ribbonOptions}
            loading={!!isBundleDetailsQueryFetching && !controllerProps.field.value}
          />
        )}
      />

      <Controller
        name="categories"
        control={form.control}
        render={(controllerProps) => (
          <FormCategoriesInput
            {...controllerProps}
            label={__('Categories', 'tutor')}
            disabled
            loading={!!isBundleDetailsQueryFetching && !controllerProps.field.value}
            optionsWrapperStyle={css`
              input {
                &:disabled {
                  svg {
                    color: ${colorTokens.icon.brand};
                  }
                }
              }
            `}
          />
        )}
      />

      <div css={styles.labelWithContent}>
        <label>{__('Instructors')}</label>
        <div css={styles.instructorsWrapper}>
          <For each={form.getValues('instructors')}>
            {(instructor) => (
              <div key={instructor.user_id} css={styles.instructor}>
                <img src={instructor.avatar_url} alt={instructor.display_name} />
                <div>
                  <div data-name="instructor-name">{instructor.display_name}</div>
                  <div data-name="instructor-email">{instructor.user_email}</div>
                </div>
              </div>
            )}
          </For>
        </div>
      </div>
    </div>
  );
};

export default BundleSidebar;

const styles = {
  sidebar: css`
    border-left: 1px solid ${colorTokens.stroke.divider};
    min-height: calc(100vh - ${headerHeight}px);
    padding-left: ${spacing[32]};
    padding-block: ${spacing[24]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};

    ${Breakpoint.smallTablet} {
      border-left: none;
      border-top: 1px solid ${colorTokens.stroke.divider};
      padding-block: ${spacing[16]};
      padding-left: 0;
    }
  `,
  statusAndDate: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  updatedOn: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
  `,
  priceRadioGroup: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[36]};
  `,
  coursePriceWrapper: css`
    ${styleUtils.display.flex()};
    align-items: flex-start;
    gap: ${spacing[16]};
  `,
  labelWithContent: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};

    label {
      ${typography.caption()};
      color: ${colorTokens.text.title};
    }
  `,
  categoriesWrapper: css`
    ${styleUtils.display.flex()};
    gap: ${spacing[8]};
  `,
  category: css`
    padding: ${spacing[4]} ${spacing[8]};
    border-radius: ${borderRadius[24]};
    background-color: ${colorTokens.surface.wordpress};
    ${typography.small()};
    color: ${colorTokens.text.title};
  `,
  instructorsWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
  `,
  instructor: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[10]};
    padding: ${spacing[8]} ${spacing[12]};
    border-radius: ${borderRadius[4]};
    background-color: ${colorTokens.background.white};

    img {
      width: 40px;
      height: 40px;
      border-radius: ${borderRadius.circle};
    }

    [data-name='instructor-name'] {
      ${typography.caption('medium')};
    }

    [data-name='instructor-email'] {
      ${typography.small()};
      color: ${colorTokens.text.subdued};
    }
  `,
};
