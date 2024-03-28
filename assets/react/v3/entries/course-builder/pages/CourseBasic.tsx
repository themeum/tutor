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
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { tutorConfig } from '@Config/config';
import { TutorRoles } from '@Config/constants';
import { colorTokens, headerHeight, spacing } from '@Config/styles';
import CourseSettings from '@CourseBuilderComponents/course-basic/CourseSettings';
import ScheduleOptions from '@CourseBuilderComponents/course-basic/ScheduleOptions';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import Navigator from '@CourseBuilderComponents/layouts/Navigator';
import type { CourseFormData } from '@CourseBuilderServices/course';
import { useUserListQuery } from '@Services/users';
import { maxValueRule, requiredRule } from '@Utils/validation';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';

const CourseBasic = () => {
  const form = useFormContext<CourseFormData>();

  const [instructorSearchText, setInstructorSearchText] = useState('');

  const visibilityStatus = useWatch({
    control: form.control,
    name: 'post_status',
  });
  const coursePriceType = useWatch({
    control: form.control,
    name: 'course_price_type',
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

  const coursePriceOptions = [
    {
      label: __('Free', 'tutor'),
      value: 'free',
    },
    {
      label: __('Paid', 'tutor'),
      value: 'paid',
    },
  ];

  const instructorListQuery = useUserListQuery({
    context: 'edit',
    roles: ['administrator', 'tutor_instructor'],
    search: instructorSearchText,
  });

  const instructorOptions =
    instructorListQuery.data?.map((item) => {
      return {
        id: item.id,
        name: item.name,
        email: item.email,
        avatar_url: item.avatar_urls[48],
      };
    }) ?? [];

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
                  baseURL={`${tutorConfig.home_url}/courses`}
                />
              )}
            />
          </div>

          <Controller
            name="post_content"
            control={form.control}
            render={(controllerProps) => <FormTextareaInput {...controllerProps} label={__('Description', 'tutor')} />}
          />

          <CourseSettings />
        </div>
        <Navigator styleModifier={styles.navigator} />
      </div>
      <div css={styles.sidebar}>
        <Controller
          name="post_status"
          control={form.control}
          render={(controllerProps) => (
            <FormSelectInput
              {...controllerProps}
              label={__('Visibility Status', 'tutor')}
              options={visibilityStatusOptions}
              leftIcon={<SVGIcon name="eye" width={32} height={32} />}
            />
          )}
        />

        {visibilityStatus === 'password_protected' && (
          <Controller
            name="post_password"
            control={form.control}
            render={(controllerProps) => <FormInput {...controllerProps} label={__('Password', 'tutor')} />}
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

        {/* <Controller
          name="video"
          control={form.control}
          render={(controllerProps) => (
            <FormImageInput
              {...controllerProps}
              label={__('Intro Video', 'tutor')}
              buttonText={__('Upload Video', 'tutor')}
              infoText={__('Supported file formats .mp4 ', 'tutor')}
            />
          )}
        /> */}

        {/* @TODO: Add course price options based on monetization setting */}
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

        {coursePriceType === 'paid' && (
          <div css={styles.coursePriceWrapper}>
            <Controller
              name="course_price"
              control={form.control}
              render={(controllerProps) => (
                <FormInputWithContent
                  {...controllerProps}
                  label={__('Regular Price', 'tutor')}
                  content="$"
                  placeholder={__('0', 'tutor')}
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
                  content="$"
                  placeholder={__('0', 'tutor')}
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
              />
            )}
          />
        )}

        {/* @TODO: Need to add condition based on tutor pro, marketplace, multi instructor addon, and admin role */}
        <Controller
          name="course_instructors"
          control={form.control}
          render={(controllerProps) => (
            <FormSelectUser
              {...controllerProps}
              label={__('Instructors', 'tutor')}
              options={instructorOptions}
              placeholder={__('Search to add instructors', 'tutor')}
              isSearchable
              handleSearchOnChange={setInstructorSearchText}
              isMultiSelect
            />
          )}
        />
      </div>
    </div>
  );
};

export default CourseBasic;

const styles = {
  wrapper: css`
    display: grid;
    grid-template-columns: 1fr 370px;
    gap: ${spacing[32]};
  `,
  mainForm: css`
    padding-block: ${spacing[24]};
    align-self: start;
    position: sticky;
    top: ${headerHeight}px;
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
    border-left: 1px solid ${colorTokens.stroke.default};
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
