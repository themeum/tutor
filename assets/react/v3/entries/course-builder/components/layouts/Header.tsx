import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import {
  borderRadius,
  colorPalate,
  colorTokens,
  containerMaxWidth,
  headerHeight,
  shadow,
  spacing,
  zIndex,
} from '@Config/styles';
import { CourseFormData, useCreateCourseMutation, useUpdateCourseMutation } from '@CourseBuilderServices/course';
import { convertCourseDataToPayload } from '@CourseBuilderUtils/utils';
import { css } from '@emotion/react';
import { styleUtils } from '@Utils/style-utils';
import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';
import Logo from '@CourseBuilderPublic/images/logo.svg';
import { typography } from '@Config/typography';
import Tracker from './Tracker';

const Header = () => {
  const params = new URLSearchParams(window.location.href);
  const courseId = params.get('course-id')?.split('#')[0];

  const form = useFormContext<CourseFormData>();

  const createCourseMutation = useCreateCourseMutation();
  const updateCourseMutation = useUpdateCourseMutation();

  const handleSubmit = async (data: CourseFormData) => {
    const payload = convertCourseDataToPayload(data);

    if (courseId) {
      updateCourseMutation.mutate({ course_id: Number(courseId), ...payload });
    } else {
      const response = await createCourseMutation.mutateAsync(payload);

      if (response.data) {
        // @TODO: Redirect to edit page url
        console.log(response);
      }
    }
  };

  return (
    <div css={styles.wrapper}>
      <div css={styles.logo}>
        <Logo width={108} height={24} />
      </div>
      <div css={styles.container}>
        <h6 css={styles.title}>{__('Course Builder', 'tutor')}</h6>
        <div css={styles.tracker}>
          <Tracker />
        </div>
        <div css={styles.headerRight}>
          <Button variant="secondary">{__('Preview', 'tutor')}</Button>
          <Button
            variant="primary"
            loading={createCourseMutation.isPending || updateCourseMutation.isPending}
            onClick={form.handleSubmit(handleSubmit)}
          >
            {__('Publish', 'tutor')}
          </Button>
          <button
            type="button"
            css={styles.closeButton}
            onClick={() => {
              window.history.back();
            }}
          >
            <SVGIcon name="cross" width={32} height={32} />
          </button>
        </div>
      </div>
      <div />
    </div>
  );
};

export default Header;

const styles = {
  wrapper: css`
    height: ${headerHeight}px;
    width: 100%;
    background-color: ${colorTokens.surface.navbar};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    display: grid;
    grid-template-columns: 1fr ${containerMaxWidth}px 1fr;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: ${zIndex.header};
  `,
  container: css`
    max-width: ${containerMaxWidth}px;
    width: 100%;
    height: ${headerHeight}px;
    ${styleUtils.display.flex()};
    justify-content: space-between;
    align-items: center;
  `,
  logo: css`
    padding-left: ${spacing[32]};
  `,
  title: css`
    ${typography.heading6('medium')};
    color: ${colorTokens.text.subdued};
  `,
  headerRight: css`
    display: flex;
    align-items: center;
    gap: ${spacing[12]};
  `,
  closeButton: css`
    ${styleUtils.resetButton};
    cursor: pointer;
    display: flex;
    color: ${colorPalate.icon.default};
    margin-left: ${spacing[4]};
    border-radius: ${borderRadius[4]};

    &:focus {
      box-shadow: ${shadow.focus};
    }
  `,
  tracker: css``,
};
