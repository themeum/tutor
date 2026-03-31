import { css } from '@emotion/react';
import { useIsFetching, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { useLocation, useNavigate } from 'react-router-dom';

import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import FormRadioGroup from '@TutorShared/components/fields/FormRadioGroup';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import SubscriptionPreview from '@TutorShared/components/subscription/SubscriptionPreview';

import { CourseBuilderRouteConfigs } from '@CourseBuilderConfig/route-configs';
import {
  type CourseDetailsResponse,
  type CourseFormData,
  type WcProduct,
  useGetWcProductsQuery,
  useWcProductDetailsQuery,
} from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { tutorConfig } from '@TutorShared/config/config';
import { Addons } from '@TutorShared/config/constants';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { withVisibilityControl } from '@TutorShared/hoc/withVisibilityControl';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { isDefined } from '@TutorShared/utils/types';
import { isAddonEnabled } from '@TutorShared/utils/util';
import { requiredRule } from '@TutorShared/utils/validation';

const courseId = getCourseId();

const CoursePricing = () => {
  const form = useFormContext<CourseFormData>();
  const queryClient = useQueryClient();
  const isCourseDetailsFetching = useIsFetching({
    queryKey: ['CourseDetails', courseId],
  });
  const navigate = useNavigate();
  const { state } = useLocation();

  const coursePriceType = useWatch({
    control: form.control,
    name: 'course_price_type',
  });
  const courseProductId = useWatch({
    control: form.control,
    name: 'course_product_id',
  });
  const selectedPurchaseOption = useWatch({
    control: form.control,
    name: 'course_selling_option',
  });
  const isPublicCourse = useWatch({
    control: form.control,
    name: 'is_public_course',
  });

  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;

  const { tutor_currency } = tutorConfig;
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const isTaxEnabled = !!tutorConfig.settings?.enable_tax;
  const enableIndividualTaxControl = !!tutorConfig.settings?.enable_individual_tax_control;
  const isTaxIncludedInPrice = !!tutorConfig.settings?.is_tax_included_in_price;
  const monetizeBy = tutorConfig.settings?.monetize_by;

  // prettier-ignore
  const taxAlertMessage = __('You have unchecked the Tax Collection option. Please review your pricing, as your tax settings currently indicate that prices are inclusive of tax.', 'tutor');

  const coursePriceOptions = ['wc', 'tutor', 'edd'].includes(monetizeBy || '')
    ? [
        {
          label: __('Free', 'tutor'),
          value: 'free',
        },
        {
          label: __('Paid', 'tutor'),
          value: 'paid',
        },
      ]
    : [
        {
          label: __('Free', 'tutor'),
          value: 'free',
        },
      ];

  const purchaseOptions = [
    {
      label: __('One-time purchase only', 'tutor'),
      value: 'one_time',
    },
    {
      label: __('Subscription only', 'tutor'),
      value: 'subscription',
    },
    {
      label: __('Subscription & one-time purchase', 'tutor'),
      value: 'both',
    },
    {
      label: __('Membership only', 'tutor'),
      value: 'membership',
    },
    {
      label: __('All', 'tutor'),
      value: 'all',
    },
  ];

  const wcProductsQuery = useGetWcProductsQuery(monetizeBy, courseId ? String(courseId) : '');
  const wcProductDetailsQuery = useWcProductDetailsQuery(
    courseProductId,
    String(courseId),
    coursePriceType,
    isTutorPro ? monetizeBy : undefined,
  );

  const wcProductOptions = (data: WcProduct[] | undefined) => {
    if (!data || !data.length) {
      return [];
    }

    const { course_pricing } = courseDetails || {};
    const currentSelectedWcProduct =
      course_pricing?.product_id && course_pricing.product_id !== '0' && course_pricing.product_name
        ? { label: course_pricing.product_name || '', value: String(course_pricing.product_id) }
        : null;

    const convertedCourseProducts =
      data.map(({ post_title: label, ID: value }) => ({
        label,
        value: String(value),
      })) ?? [];

    const combinedProducts = [currentSelectedWcProduct, ...convertedCourseProducts].filter(isDefined);

    const uniqueProducts = Array.from(new Map(combinedProducts.map((item) => [item.value, item])).values());

    return uniqueProducts;
  };

  useEffect(() => {
    if (wcProductsQuery.isSuccess && wcProductsQuery.data) {
      const { course_pricing } = courseDetails || {};

      if (
        monetizeBy === 'wc' &&
        course_pricing?.product_id &&
        course_pricing.product_id !== '0' &&
        !wcProductOptions(wcProductsQuery.data).find(({ value }) => String(value) === String(course_pricing.product_id))
      ) {
        form.setValue('course_product_id', '', {
          shouldValidate: true,
        });
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [wcProductsQuery.data]);

  useEffect(() => {
    if (!tutorConfig.edd_products || !tutorConfig.edd_products.length) {
      return;
    }

    const { course_pricing } = courseDetails || {};

    if (
      monetizeBy === 'edd' &&
      course_pricing?.product_id &&
      course_pricing.product_id !== '0' &&
      !tutorConfig.edd_products.find(({ ID }) => String(ID) === String(course_pricing.product_id))
    ) {
      form.setValue('course_product_id', '', {
        shouldValidate: true,
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [tutorConfig.edd_products]);

  useEffect(() => {
    if (monetizeBy !== 'wc') {
      return;
    }

    if (wcProductDetailsQuery.isSuccess && wcProductDetailsQuery.data) {
      if (state?.isError) {
        navigate(CourseBuilderRouteConfigs.CourseBasics.buildLink(), { state: { isError: false } });
        return;
      }

      form.setValue('course_price', wcProductDetailsQuery.data.regular_price || '0', {
        shouldValidate: true,
      });
      form.setValue('course_sale_price', wcProductDetailsQuery.data.sale_price || '0', {
        shouldValidate: true,
      });

      return;
    }

    const isCoursePriceDirty = form.formState.dirtyFields.course_price;
    const isCourseSalePriceDirty = form.formState.dirtyFields.course_sale_price;

    if (!isCoursePriceDirty) {
      form.setValue('course_price', '0');
    }

    if (!isCourseSalePriceDirty) {
      form.setValue('course_sale_price', '0');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [wcProductDetailsQuery.data]);

  return (
    <>
      <Controller
        name="course_price_type"
        control={form.control}
        rules={{
          validate: (value) => {
            if (value === 'paid' && isPublicCourse) {
              return __('Public courses cannot be paid.', 'tutor');
            }
            return true;
          },
          deps: ['is_public_course'],
        }}
        render={(controllerProps) => (
          <FormRadioGroup
            {...controllerProps}
            label={__('Pricing Model', 'tutor')}
            options={coursePriceOptions}
            wrapperCss={styles.priceRadioGroup}
            onSelect={(option) => {
              if (option.value === 'paid' && isPublicCourse) {
                form.setError('course_price_type', {
                  type: 'validate',
                  message: __('Public courses cannot be paid.', 'tutor'),
                });
                form.setValue('course_price_type', 'free');
              }
            }}
          />
        )}
      />

      <Show when={isAddonEnabled(Addons.SUBSCRIPTION) && monetizeBy === 'tutor' && coursePriceType === 'paid'}>
        <Controller
          name="course_selling_option"
          control={form.control}
          render={(controllerProps) => (
            <FormSelectInput {...controllerProps} label={__('Purchase Options', 'tutor')} options={purchaseOptions} />
          )}
        />
      </Show>

      <Show when={coursePriceType === 'paid' && monetizeBy === 'wc'}>
        <Controller
          name="course_product_id"
          control={form.control}
          render={(controllerProps) => (
            <FormSelectInput
              {...controllerProps}
              label={__('Select product', 'tutor')}
              placeholder={__('Select a product', 'tutor')}
              options={[
                {
                  label: __('Select a product', 'tutor'),
                  value: '-1',
                },
                ...wcProductOptions(wcProductsQuery.data),
              ]}
              helpText={
                isTutorPro
                  ? __(
                      'You can select an existing WooCommerce product, alternatively, a new WooCommerce product will be created for you.',
                      'tutor',
                    )
                  : __('You can select an existing WooCommerce product.', 'tutor')
              }
              isSearchable
              loading={wcProductsQuery.isLoading && !controllerProps.field.value}
              isClearable
            />
          )}
        />
      </Show>

      <Show when={coursePriceType === 'paid' && monetizeBy === 'edd'}>
        <Controller
          name="course_product_id"
          control={form.control}
          rules={{
            ...requiredRule(),
          }}
          render={(controllerProps) => (
            <FormSelectInput
              {...controllerProps}
              label={__('Select product', 'tutor')}
              placeholder={__('Select a product', 'tutor')}
              options={
                tutorConfig.edd_products
                  ? tutorConfig.edd_products.map((product) => ({
                      label: product.post_title,
                      value: String(product.ID),
                    }))
                  : []
              }
              helpText={__('Sell your product, process by EDD', 'tutor')}
              isSearchable
              loading={!!isCourseDetailsFetching && !controllerProps.field.value}
            />
          )}
        />
      </Show>

      <Show
        when={
          coursePriceType === 'paid' &&
          !['subscription', 'membership'].includes(selectedPurchaseOption) &&
          (monetizeBy === 'tutor' || (isTutorPro && monetizeBy === 'wc' && courseProductId !== '-1'))
        }
      >
        <div css={styles.coursePriceWrapper}>
          <Controller
            name="course_price"
            control={form.control}
            rules={{
              ...requiredRule(),
              validate: (value) => {
                if (Number(value) <= 0) {
                  return __('Price must be greater than 0', 'tutor');
                }

                return true;
              },
            }}
            render={(controllerProps) => (
              <FormInputWithContent
                {...controllerProps}
                label={__('Regular Price', 'tutor')}
                content={tutor_currency?.symbol || '$'}
                placeholder={__('0', 'tutor')}
                type="number"
                loading={!!isCourseDetailsFetching && !controllerProps.field.value}
                selectOnFocus
                contentCss={styleUtils.inputCurrencyStyle}
              />
            )}
          />
          <Controller
            name="course_sale_price"
            control={form.control}
            rules={{
              validate: (value) => {
                if (!value) {
                  return true;
                }

                const regularPrice = form.getValues('course_price');
                if (Number(value) >= Number(regularPrice)) {
                  return __('Sale price must be less than regular price', 'tutor');
                }

                return true;
              },
            }}
            render={(controllerProps) => (
              <FormInputWithContent
                {...controllerProps}
                label={__('Sale Price', 'tutor')}
                content={tutor_currency?.symbol || '$'}
                placeholder={__('0', 'tutor')}
                type="number"
                loading={!!isCourseDetailsFetching && !controllerProps.field.value}
                selectOnFocus
                contentCss={styleUtils.inputCurrencyStyle}
              />
            )}
          />
        </div>
      </Show>

      <Show when={isAddonEnabled(Addons.SUBSCRIPTION) && monetizeBy === 'tutor' && coursePriceType === 'paid'}>
        <Show when={!['one_time', 'membership'].includes(selectedPurchaseOption)}>
          <SubscriptionPreview courseId={courseId} />
        </Show>
      </Show>

      <Show
        when={
          coursePriceType === 'paid' &&
          monetizeBy === 'tutor' &&
          isTaxEnabled &&
          enableIndividualTaxControl &&
          selectedPurchaseOption !== 'membership'
        }
      >
        <div css={styles.taxWrapper}>
          <label>{__('Tax Collection', 'tutor')}</label>

          <div css={styles.checkboxWrapper}>
            <Show when={['one_time', 'both', 'all'].includes(selectedPurchaseOption)}>
              <Controller
                name="tax_on_single"
                control={form.control}
                render={(controllerProps) => (
                  <FormCheckbox
                    {...controllerProps}
                    label={__('Charge tax on one-time purchase ', 'tutor')}
                    helpText={isTaxIncludedInPrice && !controllerProps.field.value ? taxAlertMessage : ''}
                  />
                )}
              />
            </Show>
            <Show
              when={
                isAddonEnabled(Addons.SUBSCRIPTION) && ['subscription', 'both', 'all'].includes(selectedPurchaseOption)
              }
            >
              <Controller
                name="tax_on_subscription"
                control={form.control}
                render={(controllerProps) => (
                  <FormCheckbox
                    {...controllerProps}
                    label={__('Charge tax on subscription', 'tutor')}
                    helpText={isTaxIncludedInPrice && !controllerProps.field.value ? taxAlertMessage : ''}
                  />
                )}
              />
            </Show>
          </div>
        </div>
      </Show>
    </>
  );
};

export default withVisibilityControl(CoursePricing);

const styles = {
  priceRadioGroup: css`
    display: flex;
    align-items: center;
    gap: ${spacing[36]};
  `,
  coursePriceWrapper: css`
    display: flex;
    align-items: flex-start;
    gap: ${spacing[16]};
  `,
  taxWrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[4]};

    label {
      ${typography.body()}
      color: ${colorTokens.text.title};
    }
  `,
  checkboxWrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[4]};
  `,
  taxAlert: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
    margin-top: ${spacing[8]};
    padding: ${spacing[12]};
    background-color: ${colorTokens.color.warning[40]};
    border: 1px solid ${colorTokens.color.warning[50]};
    border-radius: ${borderRadius[6]};
  `,
  alertTitle: css`
    ${styleUtils.display.flex()}
    gap: ${spacing[4]};
    align-items: center;
    ${typography.caption('medium')};
    color: ${colorTokens.color.warning[100]};

    svg {
      color: ${colorTokens.design.warning};
      flex-shrink: 0;
    }
  `,
  alertDescription: css`
    ${typography.caption()}
    color: ${colorTokens.color.warning[100]};
  `,
};
