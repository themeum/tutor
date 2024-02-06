import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { borderRadius, colorTokens, footerHeight, headerHeight, shadow, spacing, zIndex } from '@Config/styles';
import { css } from '@emotion/react';
import { Controller, useFormContext } from 'react-hook-form';
import { __ } from '@wordpress/i18n';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import FormEditableAlias from '@Components/fields/FormEditableAlias';
import CourseSettings from '@CourseBuilderComponents/course-basic/CourseSettings';
import ScheduleOptions from '@CourseBuilderComponents/course-basic/ScheduleOptions';
import FormImageInput from '@Components/fields/FormImageInput';
import FormRadioGroup from '@Components/fields/FormRadioGroup';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import SVGIcon from '@Atoms/SVGIcon';
import FormTagsInput from '@Components/fields/FormTagsInput';
import FormCategoriesInput from '@Components/fields/FormCategoriesInput';

const CourseBasic = () => {
  const form = useFormContext();

  const visibilityStatusOptions = [
    {
      label: __('Public', 'tutor'),
      value: 'public',
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

  return (
    <div css={styles.wrapper}>
      <div css={styles.mainForm}>
        <CanvasHead title="Course Basic" />

        <div css={styles.fieldsWrapper}>
          <div css={styles.titleAndSlug}>
            <Controller
              name="post_title"
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  label={__('Course Title', 'tutor')}
                  maxLimit={245}
                  placeholder={__('ex. Learn Photoshop CS6 from scratch', 'tutor')}
                  isClearable
                />
              )}
            />

            <Controller
              name="course_slug"
              control={form.control}
              render={(controllerProps) => (
                <FormEditableAlias
                  {...controllerProps}
                  label={__('Course URL', 'tutor')}
                  baseURL={`${window._tutorobject.home_url}/courses`}
                />
              )}
            />
          </div>

          <Controller
            name="post_content"
            control={form.control}
            render={(controllerProps) => (
              <FormTextareaInput {...controllerProps} label={__('Description', 'tutor')} maxLimit={400} />
            )}
          />

          <CourseSettings />
        </div>
      </div>
      <div css={styles.sidebar}>
        <Controller
          name="visibility_status"
          control={form.control}
          defaultValue="public"
          render={(controllerProps) => (
            <FormSelectInput
              {...controllerProps}
              label={__('Visibility Status', 'tutor')}
              options={visibilityStatusOptions}
              leftIcon={<SVGIcon name="eye" width={32} height={32} />}
            />
          )}
        />

        <ScheduleOptions />

        <Controller
          name="featured_image"
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
          name="intro_video"
          control={form.control}
          render={(controllerProps) => (
            <FormImageInput
              {...controllerProps}
              label={__('Intro Video', 'tutor')}
              buttonText={__('Upload Video', 'tutor')}
              infoText={__('Supported file formats .mp4 ', 'tutor')}
            />
          )}
        />

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

        <Controller
          name="categories"
          control={form.control}
          defaultValue={[]}
          render={(controllerProps) => <FormCategoriesInput {...controllerProps} label={__('Categories', 'tutor')} />}
        />

        <Controller
          name="tags"
          control={form.control}
          render={(controllerProps) => <FormTagsInput {...controllerProps} label={__('Tags', 'tutor')} />}
        />

        <Controller
          name="instructors"
          control={form.control}
          render={(controllerProps) => (
            <FormInputWithContent
              {...controllerProps}
              label={__('Instructors', 'tutor')}
              placeholder={__('Search to add instructors', 'tutor')}
              content={<SVGIcon name="search" width={24} height={24} />}
              showVerticalBar={false}
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
    grid-template-columns: 1fr 402px;
  `,
  mainForm: css`
    padding: ${spacing[24]} ${spacing[64]};
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
    padding: ${spacing[24]} ${spacing[32]} ${spacing[24]} ${spacing[64]};
    border-left: 1px solid ${colorTokens.stroke.default};
    min-height: calc(100vh - (${headerHeight}px + ${footerHeight}px));

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
};
