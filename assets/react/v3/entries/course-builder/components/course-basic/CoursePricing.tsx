import { css } from '@emotion/react';
import { useIsFetching, useQueryClient } from '@tanstack/react-query';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { useLocation, useNavigate } from 'react-router-dom';

import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormRadioGroup from '@Components/fields/FormRadioGroup';
import FormSelectInput from '@Components/fields/FormSelectInput';
import SubscriptionPreview from '@CourseBuilderComponents/subscription/SubscriptionPreview';

import { tutorConfig } from '@Config/config';
import { Addons } from '@Config/constants';
import { spacing } from '@Config/styles';
import Show from '@Controls/Show';
import {
  type CourseDetailsResponse,
  type CourseFormData,
  type WcProduct,
  useGetWcProductsQuery,
  useWcProductDetailsQuery,
} from '@CourseBuilderServices/course';
import { getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { requiredRule } from '@Utils/validation';

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

  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;

  const { tutor_currency } = tutorConfig;
  const isTutorPro = !!tutorConfig.tutor_pro_url;

  const coursePriceOptions =
    tutorConfig.settings?.monetize_by === 'wc' ||
    tutorConfig.settings?.monetize_by === 'tutor' ||
    tutorConfig.settings?.monetize_by === 'edd'
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

  const wcProductsQuery = useGetWcProductsQuery(tutorConfig.settings?.monetize_by, courseId ? String(courseId) : '');
  const wcProductDetailsQuery = useWcProductDetailsQuery(
    courseProductId,
    String(courseId),
    coursePriceType,
    isTutorPro ? tutorConfig.settings?.monetize_by : undefined,
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

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (wcProductsQuery.isSuccess && wcProductsQuery.data) {
      const { course_pricing } = courseDetails || {};

      if (
        tutorConfig.settings?.monetize_by === 'wc' &&
        course_pricing?.product_id &&
        course_pricing.product_id !== '0' &&
        !wcProductOptions(wcProductsQuery.data).find(({ value }) => String(value) === String(course_pricing.product_id))
      ) {
        form.setValue('course_product_id', '', {
          shouldValidate: true,
        });
      }
    }
  }, [wcProductsQuery.data]);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (!tutorConfig.edd_products || !tutorConfig.edd_products.length) {
      return;
    }

    const { course_pricing } = courseDetails || {};

    if (
      tutorConfig.settings?.monetize_by === 'edd' &&
      course_pricing?.product_id &&
      course_pricing.product_id !== '0' &&
      !tutorConfig.edd_products.find(({ ID }) => String(ID) === String(course_pricing.product_id))
    ) {
      form.setValue('course_product_id', '', {
        shouldValidate: true,
      });
    }
  }, [tutorConfig.edd_products]);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (tutorConfig.settings?.monetize_by !== 'wc') {
      return;
    }

    if (wcProductDetailsQuery.isSuccess && wcProductDetailsQuery.data) {
      if (state?.isError) {
        navigate('/basics', { state: { isError: false } });
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
  }, [wcProductDetailsQuery.data]);

  return (
    <>
      <Controller
        name="course_price_type"
        control={form.control}
        render={(controllerProps) => (
          <FormRadioGroup
            {...controllerProps}
            label={__('Pricing Model', 'tutor')}
            options={coursePriceOptions}
            wrapperCss={styles.priceRadioGroup}
          />
        )}
      />

      <Show when={coursePriceType === 'paid' && tutorConfig.settings?.monetize_by === 'wc'}>
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
              helpText={sprintf(
                __('You can select an existing WooCommerce product%s', 'tutor'),
                isTutorPro ? ', alternatively, a new WooCommerce product will be created for you.' : '.',
              )}
              isSearchable
              loading={wcProductsQuery.isLoading && !controllerProps.field.value}
              isClearable
            />
          )}
        />
      </Show>

      <Show when={coursePriceType === 'paid' && tutorConfig.settings?.monetize_by === 'edd'}>
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
          (tutorConfig.settings?.monetize_by === 'tutor' ||
            (isTutorPro && tutorConfig.settings?.monetize_by === 'wc' && courseProductId !== '-1'))
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

      <Show
        when={
          isAddonEnabled(Addons.SUBSCRIPTION) &&
          tutorConfig.settings?.monetize_by === 'tutor' &&
          coursePriceType === 'paid'
        }
      >
        <SubscriptionPreview courseId={courseId} />

        <Controller
          name="course_selling_option"
          control={form.control}
          render={(controllerProps) => (
            <FormRadioGroup
              {...controllerProps}
              wrapperCss={css`
            > div:not(:last-child) {
              margin-bottom: ${spacing[10]};
            }
          `}
              label={__('Purchase Options', 'tutor')}
              options={[
                {
                  label: __('Subscription only', 'tutor'),
                  value: 'subscription',
                },
                {
                  label: __('One-time purchase only', 'tutor'),
                  value: 'one_time',
                },
                {
                  label: __('Subscription & one-time purchase', 'tutor'),
                  value: 'both',
                },
              ]}
            />
          )}
        />
      </Show>
    </>
  );
};

export default CoursePricing;

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
};
