import { css } from '@emotion/react';
import { useIsFetching, useQueryClient } from '@tanstack/react-query';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import {} from 'react-router-dom';

import SVGIcon from '@Atoms/SVGIcon';

import FormCategoriesInput from '@Components/fields/FormCategoriesInput';
import FormImageInput from '@Components/fields/FormImageInput';
import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSelectUser, { type UserOption } from '@Components/fields/FormSelectUser';
import FormTagsInput from '@Components/fields/FormTagsInput';
import FormVideoInput from '@Components/fields/FormVideoInput';
import ScheduleOptions from '@CourseBuilderComponents/course-basic/ScheduleOptions';

import { tutorConfig } from '@Config/config';
import { Addons, DateFormats, TutorRoles } from '@Config/constants';
import { colorTokens, headerHeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { CourseDetailsResponse, CourseFormData } from '@CourseBuilderServices/course';
import { getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { useInstructorListQuery, useUserListQuery } from '@Services/users';
import { styleUtils } from '@Utils/style-utils';
import CoursePricing from './CoursePricing';

const courseId = getCourseId();

const CourseBasicSidebar = () => {
  const form = useFormContext<CourseFormData>();
  const queryClient = useQueryClient();
  const isCourseDetailsFetching = useIsFetching({
    queryKey: ['CourseDetails', courseId],
  });
  const [userSearchText, setUserSearchText] = useState('');

  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;

  const currentUser = tutorConfig.current_user;
  const isMultiInstructorEnabled = isAddonEnabled(Addons.TUTOR_MULTI_INSTRUCTORS);
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const isOpenAiEnabled = tutorConfig.settings?.chatgpt_enable === 'on';
  const isAdministrator = currentUser.roles.includes(TutorRoles.ADMINISTRATOR);
  const isInstructor = (courseDetails?.course_instructors || []).find(
    (instructor) => String(instructor.id) === String(currentUser.data.id),
  );

  const currentAuthor = form.watch('post_author');
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
            infoText={sprintf(__('MP4, and WebM formats, up to %s', 'tutor'), tutorConfig.max_upload_size)}
            supportedFormats={['mp4', 'webm']}
            loading={!!isCourseDetailsFetching && !controllerProps.field.value}
          />
        )}
      />

      <CoursePricing />

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
  );
};

export default CourseBasicSidebar;

const styles = {
  sidebar: css`
    border-left: 1px solid ${colorTokens.stroke.divider};
    min-height: calc(100vh - ${headerHeight}px);
    padding-left: ${spacing[32]};
    padding-block: ${spacing[24]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
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
