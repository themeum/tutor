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
import { typography } from '@Config/typography';
import Logo from '@CourseBuilderPublic/images/logo.svg';
import { type CourseFormData, useCreateCourseMutation, useUpdateCourseMutation } from '@CourseBuilderServices/course';
import { convertCourseDataToPayload, getCourseId } from '@CourseBuilderUtils/utils';
import DropdownButton from '@Molecules/DropdownButton';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useFormContext, useWatch } from 'react-hook-form';
import Tracker from './Tracker';
import config from '@Config/config';

const Header = () => {
  const courseId = getCourseId();

  const form = useFormContext<CourseFormData>();

  const createCourseMutation = useCreateCourseMutation();
  const updateCourseMutation = useUpdateCourseMutation();

  const previewLink = useWatch({ name: 'preview_link' });

  const handleSubmit = async (data: CourseFormData) => {
    const payload = convertCourseDataToPayload(data);

    if (courseId) {
      updateCourseMutation.mutate({ course_id: Number(courseId), ...payload });
    } else {
      const response = await createCourseMutation.mutateAsync({
        ...payload,
      });

      if (response.data) {
        window.location.href = `${config.TUTOR_API_BASE_URL}/wp-admin/admin.php?page=create-course&course_id=${response.data}`;
      }
    }
  };

  return (
    <div css={styles.wrapper}>
      <div css={styles.logo}>
        <Logo width={108} height={24} />
      </div>
      <div css={styles.container}>
        <div css={styles.titleAndTacker}>
          <h6 css={styles.title}>{__('Course Builder', 'tutor')}</h6>
          <span css={styles.divider} />
          <Tracker />
        </div>
        <div css={styles.headerRight}>
          <Button
            variant="text"
            buttonCss={styles.previewButton}
            icon={<SVGIcon name="linkExternal" width={24} height={24} />}
            iconPosition="right"
            onClick={() => {
              const legacyUrl = courseId
                ? `${config.TUTOR_API_BASE_URL}/wp-admin/post.php?post=${courseId}&action=edit`
                : `${config.TUTOR_API_BASE_URL}/wp-admin/post-new.php?post_type=courses`;

              window.open(legacyUrl, '_blank');
            }}
          >
            {__('Back To Legacy', 'tutor')}
          </Button>

          {previewLink && (
            <Button
              variant="text"
              buttonCss={styles.previewButton}
              icon={<SVGIcon name="linkExternal" width={24} height={24} />}
              iconPosition="right"
              onClick={() => {
                window.open(previewLink, '_blank');
              }}
            >
              {__('Preview', 'tutor')}
            </Button>
          )}

          <DropdownButton
            text="Publish"
            variant="primary"
            loading={createCourseMutation.isPending || updateCourseMutation.isPending}
            onClick={form.handleSubmit(handleSubmit)}
            dropdownMaxWidth="144px"
          >
            <DropdownButton.Item text="Save as Draft" onClick={() => alert('@TODO: will be implemented later.')} />
            <DropdownButton.Item
              text="Move to trash"
              onClick={() => alert('@TODO: will be implemented later.')}
              isDanger
            />
          </DropdownButton>
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
  titleAndTacker: css`
    ${styleUtils.display.flex()};
    gap: ${spacing[20]};
    align-items: center;
  `,
  divider: css`
    width: 2px;
    height: 16px;
    background-color: ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[20]};
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
  previewButton: css`
    color: ${colorTokens.text.title};
    svg {
      color: ${colorTokens.icon.default};
    }
  `,
};
