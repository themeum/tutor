import { css } from '@emotion/react';
import { useIsFetching, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';

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

import CourseSettings from '@CourseBuilderComponents/course-basic/CourseSettings';
import ScheduleOptions from '@CourseBuilderComponents/course-basic/ScheduleOptions';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import Navigator from '@CourseBuilderComponents/layouts/Navigator';
import SubscriptionPreview from '@CourseBuilderComponents/subscription/SubscriptionPreview';

import { tutorConfig } from '@Config/config';
import { Addons, TutorRoles } from '@Config/constants';
import { borderRadius, colorTokens, headerHeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import {
  type CourseDetailsResponse,
  type CourseFormData,
  type PricingCategory,
  useGetWcProductsQuery,
  useWcProductDetailsQuery,
} from '@CourseBuilderServices/course';
import { getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { useInstructorListQuery } from '@Services/users';
import { styleUtils } from '@Utils/style-utils';
import { type Option, isDefined } from '@Utils/types';
import { maxValueRule, requiredRule } from '@Utils/validation';

const courseId = getCourseId();

const CourseBasic = () => {
  const form = useFormContext<CourseFormData>();
  const queryClient = useQueryClient();
  const isCourseDetailsFetching = useIsFetching({
    queryKey: ['CourseDetails', courseId],
  });
  const currentUser = tutorConfig.current_user;

  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;

  const isMultiInstructorEnabled = isAddonEnabled(Addons.TUTOR_MULTI_INSTRUCTORS);
  const isTutorProEnabled = !!tutorConfig.tutor_pro_url;
  const isAdministrator = currentUser.roles.includes(TutorRoles.ADMINISTRATOR);

  const isInstructorVisible =
    isTutorProEnabled &&
    isMultiInstructorEnabled &&
    tutorConfig.settings.enable_course_marketplace === 'on' &&
    isAdministrator &&
    String(currentUser.data.id) === String(courseDetails?.post_author.ID || '');

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
    tutorConfig.settings.monetize_by === 'wc' ||
    tutorConfig.settings.monetize_by === 'tutor' ||
    tutorConfig.settings.monetize_by === 'edd'
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

  const instructorListQuery = useInstructorListQuery(String(courseId) ?? '');

  const instructorOptions = () => {
    const convertedCourseInstructors = (courseDetails?.course_instructors || []).map((instructor) => ({
      id: instructor.id,
      name: instructor.display_name,
      email: instructor.user_email,
      avatar_url: instructor.avatar_url,
    }));

    if (convertedCourseInstructors.length && instructorListQuery.data?.length) {
      return [...convertedCourseInstructors, ...instructorListQuery.data];
    }

    if (convertedCourseInstructors.length > 0) {
      return convertedCourseInstructors;
    }

    if (instructorListQuery.data) {
      return instructorListQuery.data;
    }

    return [];
  };

  const wcProductsQuery = useGetWcProductsQuery(tutorConfig.settings.monetize_by, courseId ? String(courseId) : '');
  const wcProductDetailsQuery = useWcProductDetailsQuery(
    courseProductId,
    String(courseId),
    coursePriceType,
    tutorConfig.settings.monetize_by,
  );

  const wcProductOptions = () => {
    const { course_pricing } = courseDetails || {};
    const currentSelectedWcProduct =
      course_pricing?.product_id && course_pricing.product_id !== '0' && course_pricing.product_name
        ? { label: course_pricing.product_name || '', value: course_pricing.product_id }
        : null;

    return wcProductsQuery.isSuccess && wcProductsQuery.data
      ? [
          currentSelectedWcProduct,
          ...wcProductsQuery.data.map(({ post_title: label, ID: value }) => ({ label, value })),
        ].filter(isDefined)
      : [];
  };

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (wcProductDetailsQuery.isSuccess && wcProductDetailsQuery.data) {
      form.setValue('course_price', wcProductDetailsQuery.data.regular_price || '0');
      form.setValue('course_sale_price', wcProductDetailsQuery.data.sale_price || '0');
    } else {
      form.setValue('course_price', '0');
      form.setValue('course_sale_price', '0');
    }
  }, [wcProductDetailsQuery.data]);

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
            render={(controllerProps) => (
              <FormWPEditor
                {...controllerProps}
                label={__('Description', 'tutor')}
                hasCustomEditorSupport
                editorUsed={courseDetails?.editor_used}
                editors={courseDetails?.editors}
                loading={!!isCourseDetailsFetching}
              />
            )}
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
              onChange={() => {
                form.setValue('post_password', '');
              }}
            />
          )}
        />

        {visibilityStatus === 'password_protected' && (
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
              infoText={__('Supported file formats .mp4', 'tutor')}
              supportedFormats={['mp4']}
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
                options={wcProductOptions()}
                helpText={__(
                  'You can select an existing WooCommerce product, alternatively, a new WooCommerce product will be created for you.',
                )}
                isSearchable
              />
            )}
          />
        )}

        {coursePriceType === 'paid' && tutorConfig.settings.monetize_by === 'edd' && (
          <Controller
            name="course_product_id"
            control={form.control}
            render={(controllerProps) => (
              <FormSelectInput
                {...controllerProps}
                label={__('Select product', 'tutor')}
                placeholder={__('Select a product', 'tutor')}
                options={
                  tutorConfig.edd_products
                    ? tutorConfig.edd_products.map((product) => ({
                        label: product.post_title,
                        value: Number(product.ID),
                      }))
                    : []
                }
                helpText={__('Sell your product, process by EDD', 'tutor')}
                isSearchable
              />
            )}
          />
        )}

        {courseCategory === 'regular' &&
          coursePriceType === 'paid' &&
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

        {currentUser.roles.includes(TutorRoles.ADMINISTRATOR) && (
          <Controller
            name="post_author"
            control={form.control}
            render={(controllerProps) => (
              <FormSelectUser
                {...controllerProps}
                label={__('Author', 'tutor')}
                options={
                  instructorOptions().some(
                    (instructor) => String(instructor.id) === String(courseDetails.post_author.ID),
                  )
                    ? instructorOptions()
                    : [
                        ...instructorOptions(),
                        {
                          id: Number(courseDetails?.post_author.ID),
                          name: courseDetails?.post_author.display_name,
                          email: courseDetails?.post_author.user_email,
                          avatar_url: courseDetails?.post_author.tutor_profile_photo_url,
                        },
                      ]
                }
                placeholder={__('Search to add author', 'tutor')}
                isSearchable
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
                options={instructorOptions()}
                placeholder={__('Search to add instructor', 'tutor')}
                isSearchable
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
  editorsButtonWrapper: css`
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    padding-bottom: ${spacing[10]};
    gap: ${spacing[8]};

    * {
      flex-shrink: 0;
      margin-right: ${spacing[8]};
    }
  `,
  descriptionWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[6]};
  `,
  descriptionLabel: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.title};
  `,
  editorWrapper: css`
    position: relative;
  `,
  editorOverlay: css`
    height: 360px;
    ${styleUtils.flexCenter()};
    background-color: ${colorTokens.bg.gray20};
    border-radius: ${borderRadius.card};
  `,
};
