import React from 'react';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import { __ } from '@wordpress/i18n';
import { css } from '@emotion/react';
import { borderRadius, colorTokens, fontWeight, footerHeight, headerHeight, shadow, spacing } from '@Config/styles';
import { Controller, useFormContext } from 'react-hook-form';
import { CourseFormData } from '@CourseBuilderServices/course';
import FormInput from '@Components/fields/FormInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import SVGIcon from '@Atoms/SVGIcon';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';
import Button from '@Atoms/Button';
import For from '@Controls/For';
import LiveClass from '@CourseBuilderComponents/additional/LiveClass';

const courses = [
  {
    id: 1,
    title: 'Digital Fantasy Portra...with Photoshop',
    image: 'https://via.placeholder.com/76x76',
  },
  {
    id: 2,
    title: 'Portrait Sketchbooking: Explore the Human Face',
    image: 'https://via.placeholder.com/76x76',
  },
  {
    id: 3,
    title: 'Portrait Sketchbooking: Explore the Human Face',
    image: 'https://via.placeholder.com/76x76',
  },
  {
    id: 4,
    title: 'Portrait Sketchbooking: Explore the Human Face',
    image: 'https://via.placeholder.com/76x76',
  },
  {
    id: 5,
    title: 'Digital Fantasy Portra...with Photoshop',
    image: 'https://via.placeholder.com/76x76',
  },
  {
    id: 6,
    title: 'Digital Fantasy Portra...with Photoshop',
    image: 'https://via.placeholder.com/76x76',
  },
];

const Additional = () => {
  const form = useFormContext<CourseFormData>();
  const searchForm = useFormWithGlobalError();

  return (
    <div css={styles.wrapper}>
      <div css={styles.mainForm}>
        <CanvasHead title={__('Additionals', 'tutor')} />
        <div css={styles.fieldsWrapper}>
          <Controller
            name="course_benefits"
            control={form.control}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                label={__('What Will I Learn?', 'tutor')}
                placeholder={__('Write here the course benefits', 'tutor')}
                maxLimit={245}
              />
            )}
          />

          <Controller
            name="course_target_audience"
            control={form.control}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                label={__('Targeted Audience', 'tutor')}
                placeholder={__('Specify the target audience that will benefit the most from the course', 'tutor')}
              />
            )}
          />

          <div css={styles.totalCourseDuration}>
            <Controller
              name="course_duration_hours"
              control={form.control}
              render={(controllerProps) => (
                <FormInputWithContent
                  {...controllerProps}
                  type="number"
                  label={__('Total Course Duration', 'tutor')}
                  placeholder="0"
                  contentPosition="right"
                  content={__('hour', 'tutor')}
                />
              )}
            />
            <Controller
              name="course_duration_minutes"
              control={form.control}
              render={(controllerProps) => (
                <FormInputWithContent
                  {...controllerProps}
                  type="number"
                  placeholder="0"
                  contentPosition="right"
                  content={__('min', 'tutor')}
                />
              )}
            />
          </div>

          <Controller
            name="course_material_includes"
            control={form.control}
            render={(controllerProps) => (
              <FormTextareaInput
                {...controllerProps}
                label={__('Materials Included', 'tutor')}
                placeholder={__(
                  'A list of assets you will be providing for the students in this course (One per line)',
                  'tutor'
                )}
                rows={3}
                enableResize
              />
            )}
          />

          <Controller
            name="course_requirements"
            control={form.control}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                label={__('Requirements/Instructions', 'tutor')}
                placeholder={__('Additional requirements or special instructions for the students', 'tutor')}
              />
            )}
          />
        </div>
      </div>
      <div css={styles.sidebar}>
        <div css={styles.coursePrerequisite}>
          <Controller
            name="course_price_type"
            control={searchForm.control}
            render={(controllerProps) => (
              <FormInputWithContent
                {...controllerProps}
                label={__('Course prerequisites', 'tutor')}
                placeholder={__('Search to add course prerequisites', 'tutor')}
                content={<SVGIcon name="search" width={24} height={24} />}
                showVerticalBar={false}
              />
            )}
          />
          <div css={styles.courses}>
            <For each={courses}>
              {(course) => (
                <div key={course.id} css={styles.courseCard}>
                  <div css={styles.imageWrapper}>
                    <img src={course.image} alt={course.title} css={styles.image} />
                  </div>
                  <div css={styles.cardContent}>
                    <span css={styles.cardTitle}>{course.title}</span>
                    <p css={typography.tiny()}>{course.id}</p>
                  </div>
                  <button css={styles.removeButton}>
                    <SVGIcon name="times" width={14} height={14} />
                  </button>
                </div>
              )}
            </For>
          </div>

          <div css={styles.uploadAttachment}>
            <span css={styles.uploadLabel}>{__('Attachments', 'tutor')}</span>
            <Button
              icon={<SVGIcon name="attach" height={24} width={24} />}
              variant="secondary"
              buttonContentCss={css`
                justify-content: center;
              `}
            >
              Upload Attachment
            </Button>
          </div>

          <LiveClass />
        </div>
      </div>
    </div>
  );
};

export default Additional;

const styles = {
  wrapper: css`
    display: grid;
    grid-template-columns: 1fr 402px;
  `,
  mainForm: css`
    padding: ${spacing[24]} ${spacing[64]};
  `,
  fieldsWrapper: css`
    position: sticky;
    top: 0;
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
    margin-top: ${spacing[40]};
  `,
  totalCourseDuration: css`
    display: flex;
    gap: ${spacing[8]};
    align-items: end;

    & > div {
      flex: 1;
    }
  `,
  sidebar: css`
    padding: ${spacing[24]} ${spacing[32]} ${spacing[24]} ${spacing[64]};
    border-left: 1px solid ${colorTokens.stroke.default};
    min-height: calc(100vh - (${headerHeight}px + ${footerHeight}px));
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
  coursePrerequisite: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
  courses: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
    max-height: 256px;
    height: 100%;
    overflow-y: auto;
  `,
  courseCard: css`
    position: relative;
    padding: ${spacing[8]};
    /* margin-right: ${spacing[12]}; */
    border: 1px solid transparent;
    border-radius: ${borderRadius.card};
    display: grid;
    grid-template-columns: 76px 1fr;
    gap: ${spacing[10]};
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease;

    &:hover {
      border-color: ${colorTokens.stroke.default};
    }

    &:hover button {
      opacity: 1;
    }
  `,
  imageWrapper: css`
    height: 42px;
  `,
  image: css`
    width: 100%;
    height: 100%;
    border-radius: ${borderRadius.card};
    object-fit: cover;
    object-position: center;
  `,
  cardContent: css`
    display: flex;
    flex-direction: column;
  `,
  cardTitle: css`
    ${typography.small()};
    font-weight: ${fontWeight.medium};
    line-clamp: 1;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
    overflow: hidden;
  `,
  removeButton: css`
    ${styleUtils.resetButton};
    position: absolute;
    top: 50%;
    right: ${spacing[8]};
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: ${borderRadius.circle};
    background: ${colorTokens.background.white};
    opacity: 0;
    transition: opacity 0.3s ease-in-out;

    svg {
      color: ${colorTokens.icon.default};
      transition: color 0.3s ease-in-out;
    }

    :hover {
      svg {
        color: ${colorTokens.icon.hover};
      }
    }

    :focus {
      box-shadow: ${shadow.focus};
    }
  `,
  uploadAttachment: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
  uploadLabel: css`
    ${typography.body()}
    color: ${colorTokens.text.title};
  `,
  liveClass: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
};
