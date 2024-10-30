import { css } from '@emotion/react';
import { useIsFetching, useQueryClient } from '@tanstack/react-query';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useEffect, useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { useLocation, useNavigate } from 'react-router-dom';

import SVGIcon from '@Atoms/SVGIcon';
import FormCategoriesInput from '@Components/fields/FormCategoriesInput';
import FormEditableAlias from '@Components/fields/FormEditableAlias';
import FormImageInput from '@Components/fields/FormImageInput';
import FormInput from '@Components/fields/FormInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormRadioGroup from '@Components/fields/FormRadioGroup';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSelectUser, { type UserOption } from '@Components/fields/FormSelectUser';
import FormTagsInput from '@Components/fields/FormTagsInput';
import FormVideoInput from '@Components/fields/FormVideoInput';
import FormWPEditor from '@Components/fields/FormWPEditor';
import CourseSettings from '@CourseBuilderComponents/course-basic/CourseSettings';
import ScheduleOptions from '@CourseBuilderComponents/course-basic/ScheduleOptions';
import Navigator from '@CourseBuilderComponents/layouts/Navigator';
import SubscriptionPreview from '@CourseBuilderComponents/subscription/SubscriptionPreview';

import { tutorConfig } from '@Config/config';
import { Addons, DateFormats, TutorRoles } from '@Config/constants';
import { borderRadius, colorTokens, headerHeight, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import {
  type CourseDetailsResponse,
  type CourseFormData,
  type WcProduct,
  convertCourseDataToPayload,
  useGetWcProductsQuery,
  useUpdateCourseMutation,
  useWcProductDetailsQuery,
} from '@CourseBuilderServices/course';
import { convertToSlug, determinePostStatus, getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { useInstructorListQuery, useUserListQuery } from '@Services/users';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { maxLimitRule, requiredRule } from '@Utils/validation';

const courseId = getCourseId();

const CourseBasic = () => {
  const form = useFormContext<CourseFormData>();
  const queryClient = useQueryClient();
  const isCourseDetailsFetching = useIsFetching({
    queryKey: ['CourseDetails', courseId],
  });
  const updateCourseMutation = useUpdateCourseMutation();
  const navigate = useNavigate();
  const { state } = useLocation();

  const [userSearchText, setUserSearchText] = useState('');
  const [isWpEditorFullScreen, setIsWpEditorFullScreen] = useState(false);

  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;

  const currentUser = tutorConfig.current_user;
  const { tutor_currency } = tutorConfig;
  const isMultiInstructorEnabled = isAddonEnabled(Addons.TUTOR_MULTI_INSTRUCTORS);
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const isOpenAiEnabled = tutorConfig.settings?.chatgpt_enable === 'on';
  const isAdministrator = currentUser.roles.includes(TutorRoles.ADMINISTRATOR);
  const isInstructor = (courseDetails?.course_instructors || []).find(
    (instructor) => String(instructor.id) === String(currentUser.data.id),
  );

  const currentAuthor = form.watch('post_author');
  const postStatus = form.watch('post_status');
  const isPostNameDirty = form.formState.dirtyFields.post_name;

  const isInstructorVisible =
    isTutorPro &&
    isMultiInstructorEnabled &&
    tutorConfig.settings?.enable_course_marketplace === 'on' &&
    (isAdministrator || String(currentUser.data.id) === String(courseDetails?.post_author.ID || '') || isInstructor);

  const isAuthorEditable =
    isAdministrator || String(currentUser.data.id) === String(courseDetails?.post_author.ID || '');

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

  const userList = useUserListQuery(userSearchText);

  const instructorListQuery = useInstructorListQuery(String(courseId) ?? '', isMultiInstructorEnabled);

  const convertedCourseInstructors = (courseDetails?.course_instructors || []).map((instructor) => ({
    id: instructor.id,
    name: instructor.display_name,
    email: instructor.user_email,
    avatar_url: instructor.avatar_url,
  }));

  const instructorOptions = [...convertedCourseInstructors, ...(instructorListQuery.data || [])].filter(
    (instructor) => String(instructor.id) !== String(currentAuthor?.id),
  );

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

    return (
      data?.find(({ ID }) => String(ID) !== String(currentSelectedWcProduct?.value))
        ? [currentSelectedWcProduct, ...convertedCourseProducts]
        : convertedCourseProducts
    ).filter(isDefined);
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
    <div css={styles.wrapper}>
      <div css={styles.mainForm({ isWpEditorFullScreen })}>
        <div css={styles.fieldsWrapper}>
          <div css={styles.titleAndSlug}>
            <Controller
              name="post_title"
              control={form.control}
              rules={{ ...requiredRule(), ...maxLimitRule(255) }}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  label={__('Title', 'tutor')}
                  placeholder={__('ex. Learn Photoshop CS6 from scratch', 'tutor')}
                  isClearable
                  selectOnFocus
                  generateWithAi={!isTutorPro || isOpenAiEnabled}
                  loading={!!isCourseDetailsFetching && !controllerProps.field.value}
                  onChange={(value) => {
                    if (postStatus === 'draft' && !isPostNameDirty) {
                      form.setValue('post_name', convertToSlug(String(value)), {
                        shouldValidate: true,
                      });
                    }
                  }}
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
                  baseURL={`${tutorConfig.home_url}/${tutorConfig.settings?.course_permalink_base}`}
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
                isMagicAi
                generateWithAi={!isTutorPro || isOpenAiEnabled}
                hasCustomEditorSupport
                editorUsed={courseDetails?.editor_used}
                editors={courseDetails?.editors}
                onCustomEditorButtonClick={async () => {
                  form.handleSubmit(async (data) => {
                    const payload = convertCourseDataToPayload(data);

                    await updateCourseMutation.mutateAsync({
                      course_id: courseId,
                      ...payload,
                      post_status: determinePostStatus(
                        form.getValues('post_status') as 'trash' | 'future' | 'draft',
                        form.getValues('visibility') as 'private' | 'password_protected',
                      ),
                    });
                  })();
                }}
                onFullScreenChange={(isFullScreen) => {
                  setIsWpEditorFullScreen(isFullScreen);
                }}
              />
            )}
          />

          <CourseSettings />
        </div>
        <Navigator styleModifier={styles.navigator} />
      </div>
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
                loading={!!isCourseDetailsFetching && !controllerProps.field.value}
                onChange={() => {
                  form.setValue('post_password', '');
                }}
              />
            )}
          />

          <Show when={courseDetails?.post_modified}>
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
                loading={!!isCourseDetailsFetching && !controllerProps.field.value}
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
              loading={!!isCourseDetailsFetching && !controllerProps.field.value}
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
              infoText={sprintf(__('MP4 format, up to %s', 'tutor'), tutorConfig.max_upload_size)}
              supportedFormats={['mp4']}
              loading={!!isCourseDetailsFetching && !controllerProps.field.value}
            />
          )}
        />

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
              (isTutorPro && tutorConfig.settings?.monetize_by === 'wc'))
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
            <FormTagsInput {...controllerProps} label={__('Tags', 'tutor')} placeholder={__('Add tags', 'tutor')} />
          )}
        />

        <Controller
          name="post_author"
          control={form.control}
          render={(controllerProps) => (
            <FormSelectUser
              {...controllerProps}
              label={__('Author', 'tutor')}
              options={
                userList.data?.map(
                  (user) =>
                    ({
                      id: user.id,
                      name: user.name || '',
                      email: user.email || '',
                      avatar_url: user.avatar_url || '',
                    }) as UserOption,
                ) ?? []
              }
              placeholder={__('Search to add author', 'tutor')}
              isSearchable
              disabled={!isAuthorEditable}
              loading={userList.isLoading}
              onChange={() => {
                const previousAuthor = courseDetails?.post_author;
                const courseInstructors = form.getValues('course_instructors');
                const isAlreadyAdded = !!courseInstructors.find(
                  (instructor) => String(instructor.id) === String(previousAuthor?.ID),
                );

                const convertedAuthor: UserOption = {
                  id: Number(previousAuthor?.ID),
                  name: previousAuthor?.display_name,
                  email: previousAuthor.user_email,
                  avatar_url: previousAuthor?.tutor_profile_photo_url,
                  isRemoveAble: String(previousAuthor?.ID) !== String(currentUser.data.id),
                };

                const updatedInstructors = isAlreadyAdded ? courseInstructors : [...courseInstructors, convertedAuthor];

                form.setValue('course_instructors', updatedInstructors);
              }}
              handleSearchOnChange={(searchText) => {
                setUserSearchText(searchText);
              }}
            />
          )}
        />

        <Show when={isInstructorVisible}>
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
                isMultiSelect
                loading={instructorListQuery.isLoading && !controllerProps.field.value}
                emptyStateText={__('No instructors added.', 'tutor')}
                isInstructorMode
              />
            )}
          />
        </Show>
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
  mainForm: ({
    isWpEditorFullScreen,
  }: {
    isWpEditorFullScreen: boolean;
  }) => css`
    padding-block: ${spacing[32]} ${spacing[24]};
    align-self: start;
    top: ${headerHeight}px;
    position: sticky;

    ${
      isWpEditorFullScreen &&
      css`
        z-index: ${zIndex.header + 1};
      `
    }
  `,

  fieldsWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
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
    gap: ${spacing[16]};
  `,
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
  statusAndDate: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  updatedOn: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
  `,
};
