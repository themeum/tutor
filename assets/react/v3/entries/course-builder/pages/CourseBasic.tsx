import SVGIcon from '@Atoms/SVGIcon';
import FormCategoriesInput from '@Components/fields/FormCategoriesInput';
import FormEditableAlias from '@Components/fields/FormEditableAlias';
import FormImageInput from '@Components/fields/FormImageInput';
import FormInput from '@Components/fields/FormInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormRadioGroup from '@Components/fields/FormRadioGroup';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSelectUser from '@Components/fields/FormSelectUser';
import FormTagsInput from '@Components/fields/FormTagsInput';
import FormVideoInput from '@Components/fields/FormVideoInput';
import FormWPEditor from '@Components/fields/FormWPEditor';
import { useModal } from '@Components/modals/Modal';
import { tutorConfig } from '@Config/config';
import { Addons, TutorRoles } from '@Config/constants';
import { colorTokens, headerHeight, spacing } from '@Config/styles';
import Show from '@Controls/Show';
import CourseSettings from '@CourseBuilderComponents/course-basic/CourseSettings';
import ScheduleOptions from '@CourseBuilderComponents/course-basic/ScheduleOptions';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import Navigator from '@CourseBuilderComponents/layouts/Navigator';
import SubscriptionPreview from '@CourseBuilderComponents/subscription/SubscriptionPreview';
import {
  type CourseFormData,
  type PricingCategory,
  useCourseDetailsQuery,
  useGetProductsQuery,
  useProductDetailsQuery,
} from '@CourseBuilderServices/course';
import { getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { useInstructorListQuery } from '@Services/users';
import type { Option } from '@Utils/types';
import { maxValueRule, requiredRule } from '@Utils/validation';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';

const courseId = getCourseId();

const CourseBasic = () => {
  const form = useFormContext<CourseFormData>();
  const { showModal } = useModal();

  const author = form.watch('post_author');

  const [instructorSearchText, setInstructorSearchText] = useState('');

  const isMultiInstructorEnabled = isAddonEnabled(Addons.TUTOR_MULTI_INSTRUCTORS);
  const isTutorProEnabled = !!tutorConfig.tutor_pro_url;
  const isAdministrator = tutorConfig.current_user.roles.includes(TutorRoles.ADMINISTRATOR);

  const isInstructorVisible =
    isTutorProEnabled &&
    isMultiInstructorEnabled &&
    tutorConfig.settings.enable_course_marketplace === 'on' &&
    isAdministrator &&
    String(tutorConfig.current_user.data.id) === String(author?.id || '');

  const isAuthorEditable = isTutorProEnabled && isMultiInstructorEnabled && isAdministrator;

  const visibilityStatus = useWatch({
    control: form.control,
    name: 'visibility',
  });
  const coursePriceType = useWatch({
    control: form.control,
    name: 'course_price_type',
  });
  const courseProductId = useWatch({
    control: form.control,
    name: 'course_product_id',
  });
  const courseCategory = useWatch({ control: form.control, name: 'course_pricing_category' });

  const visibilityStatusOptions = [
    {
      label: __('Public', 'tutor'),
      value: 'publish',
    },
    {
      label: __('Password Protected', 'tutor'),
      value: 'password_protected',
    },
    {
      label: __('Private', 'tutor'),
      value: 'private',
    },
  ];

  const coursePriceOptions =
    tutorConfig.settings.monetize_by === 'wc' || tutorConfig.settings.monetize_by === 'tutor'
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

  const coursePricingCategoryOptions: Option<PricingCategory>[] = [
    {
      label: __('Subscription', 'tutor'),
      value: 'subscription',
    },
    {
      label: __('Regular', 'tutor'),
      value: 'regular',
    },
  ];

  const courseDetailsQuery = useCourseDetailsQuery(courseId);
  const instructorListQuery = useInstructorListQuery(String(courseId) ?? '');

  const instructorOptions = instructorListQuery.data ?? [];

  const productsQuery = useGetProductsQuery(courseId ? String(courseId) : '');
  const productDetailsQuery = useProductDetailsQuery(courseProductId, String(courseId), coursePriceType);

  const productOptions = () => {
    const currentSelectedProduct = courseDetailsQuery.data?.course_pricing.product_id
      ? {
          label: courseDetailsQuery.data?.course_pricing.product_name || '',
          value: courseDetailsQuery.data?.course_pricing.product_id || '',
        }
      : null;

    if (productsQuery.isSuccess && productsQuery.data && currentSelectedProduct) {
      return [
        currentSelectedProduct,
        ...productsQuery.data.map((product) => ({
          label: product.post_title,
          value: product.ID,
        })),
      ];
    }
    return [];
  };

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (productDetailsQuery.isSuccess && productDetailsQuery.data) {
      form.setValue('course_price', productDetailsQuery.data.regular_price || '0');
      form.setValue('course_sale_price', productDetailsQuery.data.sale_price || '0');
    } else {
      form.setValue('course_price', '0');
      form.setValue('course_sale_price', '0');
    }
  }, [productDetailsQuery.data]);

  return (
    <div css={styles.wrapper}>
      <div css={styles.mainForm}>
        <CanvasHead title={__('Course Basic', 'tutor')} />

        <div css={styles.fieldsWrapper}>
          <div css={styles.titleAndSlug}>
            <Controller
              name="post_title"
              control={form.control}
              rules={{ ...requiredRule(), ...maxValueRule({ maxValue: 255 }) }}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  label={__('Course Title', 'tutor')}
                  maxLimit={255}
                  placeholder={__('ex. Learn Photoshop CS6 from scratch', 'tutor')}
                  isClearable
                  selectOnFocus
                />
              )}
            />

            <Controller
              name="post_name"
              control={form.control}
              render={(controllerProps) => (
                <FormEditableAlias
                  {...controllerProps}
                  label={__('Course URL', 'tutor')}
                  baseURL={`${tutorConfig.home_url}/${tutorConfig.settings.course_permalink_base}`}
                />
              )}
            />
          </div>

          <Controller
            name="post_content"
            control={form.control}
            render={(controllerProps) => <FormWPEditor {...controllerProps} label={__('Description', 'tutor')} />}
          />

          <CourseSettings />
        </div>
        <Navigator styleModifier={styles.navigator} />
      </div>
      <div css={styles.sidebar}>
        <Controller
          name="visibility"
          control={form.control}
          render={(controllerProps) => (
            <FormSelectInput
              {...controllerProps}
              label={__('Visibility Status', 'tutor')}
              placeholder="Select visibility status"
              options={visibilityStatusOptions}
              leftIcon={<SVGIcon name="eye" width={32} height={32} />}
            />
          )}
        />

        {visibilityStatus === 'password_protected' && (
          <Controller
            name="post_password"
            control={form.control}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                label={__('Password', 'tutor')}
                type="password"
                isPassword
                selectOnFocus
              />
            )}
          />
        )}

        <ScheduleOptions />

        <Controller
          name="thumbnail"
          control={form.control}
          render={(controllerProps) => (
            <FormImageInput
              {...controllerProps}
              label={__('Featured Image', 'tutor')}
              buttonText={__('Upload Course Thumbnail', 'tutor')}
              infoText={__('Size: 700x430 pixels', 'tutor')}
            />
          )}
        />

        <Controller
          name="video"
          control={form.control}
          render={(controllerProps) => (
            <FormVideoInput
              {...controllerProps}
              label={__('Intro Video', 'tutor')}
              buttonText={__('Upload Video', 'tutor')}
              infoText={__('Supported file formats .mp4 ', 'tutor')}
            />
          )}
        />

        <Controller
          name="course_pricing_category"
          control={form.control}
          render={(controllerProps) => (
            <FormRadioGroup
              {...controllerProps}
              label={__('Pricing type', 'tutor')}
              options={coursePricingCategoryOptions}
              wrapperCss={styles.priceRadioGroup}
            />
          )}
        />

        <Show when={courseCategory === 'regular'} fallback={<SubscriptionPreview courseId={courseId} />}>
          <Controller
            name="course_price_type"
            control={form.control}
            render={(controllerProps) => (
              <FormRadioGroup
                {...controllerProps}
                label={__('Price', 'tutor')}
                options={coursePriceOptions}
                wrapperCss={styles.priceRadioGroup}
              />
            )}
          />
        </Show>

        {coursePriceType === 'paid' && tutorConfig.settings.monetize_by === 'wc' && (
          <Controller
            name="course_product_id"
            control={form.control}
            render={(controllerProps) => (
              <FormSelectInput
                {...controllerProps}
                label={__('Select product', 'tutor')}
                placeholder={__('Select a product', 'tutor')}
                options={productOptions()}
                helpText={__(
                  'You can select an existing WooCommerce product, alternatively, a new WooCommerce product will be created for you.',
                )}
                isSearchable
              />
            )}
          />
        )}

        {coursePriceType === 'paid' &&
          (tutorConfig.settings.monetize_by === 'tutor' || tutorConfig.settings.monetize_by === 'wc') && (
            <div css={styles.coursePriceWrapper}>
              <Controller
                name="course_price"
                control={form.control}
                render={(controllerProps) => (
                  <FormInputWithContent
                    {...controllerProps}
                    label={__('Regular Price', 'tutor')}
                    content={<SVGIcon name="currency" width={24} height={24} />}
                    placeholder={__('0', 'tutor')}
                    type="number"
                  />
                )}
              />
              <Controller
                name="course_sale_price"
                control={form.control}
                render={(controllerProps) => (
                  <FormInputWithContent
                    {...controllerProps}
                    label={__('Discount Price', 'tutor')}
                    content={<SVGIcon name="currency" width={24} height={24} />}
                    placeholder={__('0', 'tutor')}
                    type="number"
                  />
                )}
              />
            </div>
          )}

        <Controller
          name="course_categories"
          control={form.control}
          defaultValue={[]}
          render={(controllerProps) => <FormCategoriesInput {...controllerProps} label={__('Categories', 'tutor')} />}
        />

        <Controller
          name="course_tags"
          control={form.control}
          render={(controllerProps) => (
            <FormTagsInput {...controllerProps} label={__('Tags', 'tutor')} placeholder="Add tags" />
          )}
        />

        {tutorConfig.current_user.roles.includes(TutorRoles.ADMINISTRATOR) && (
          <Controller
            name="post_author"
            control={form.control}
            render={(controllerProps) => (
              <FormSelectUser
                {...controllerProps}
                label={__('Author', 'tutor')}
                options={instructorOptions}
                placeholder={__('Search to add author', 'tutor')}
                isSearchable
                handleSearchOnChange={setInstructorSearchText}
                disabled={!isAuthorEditable}
              />
            )}
          />
        )}

        {isInstructorVisible && (
          <Controller
            name="course_instructors"
            control={form.control}
            render={(controllerProps) => (
              <FormSelectUser
                {...controllerProps}
                label={__('Instructors', 'tutor')}
                options={instructorOptions}
                placeholder={__('Search to add instructor', 'tutor')}
                isSearchable
                handleSearchOnChange={setInstructorSearchText}
                isMultiSelect
              />
            )}
          />
        )}
      </div>
    </div>
  );
};

export default CourseBasic;

const styles = {
  wrapper: css`
    display: grid;
    grid-template-columns: 1fr 338px;
    gap: ${spacing[32]};
  `,
  mainForm: css`
    padding-block: ${spacing[24]};
    align-self: start;
  `,

  fieldsWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
    margin-top: ${spacing[40]};
  `,
  titleAndSlug: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
  sidebar: css`
    border-left: 1px solid ${colorTokens.stroke.divider};
    min-height: calc(100vh - ${headerHeight}px);
    padding-left: ${spacing[32]};
    padding-block: ${spacing[24]};

    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
  `,
  priceRadioGroup: css`
    display: flex;
    align-items: center;
    gap: ${spacing[36]};
  `,
  coursePriceWrapper: css`
    display: flex;
    align-items: center;
    gap: ${spacing[16]};
  `,
  navigator: css`
    margin-top: ${spacing[40]};
  `,
};
