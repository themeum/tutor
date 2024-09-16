import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { isBefore } from 'date-fns';
import { useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import config, { tutorConfig } from '@Config/config';
import { TutorRoles } from '@Config/constants';
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
import Show from '@Controls/Show';
import { type CourseFormData, useCreateCourseMutation, useUpdateCourseMutation } from '@CourseBuilderServices/course';
import { convertCourseDataToPayload, determinePostStatus, getCourseId } from '@CourseBuilderUtils/utils';
import Logo from '@Images/logo.svg';
import DropdownButton from '@Molecules/DropdownButton';
import { styleUtils } from '@Utils/style-utils';
import { noop } from '@Utils/util';

import Tracker from './Tracker';

const courseId = getCourseId();

const Header = () => {
  const form = useFormContext<CourseFormData>();
  const navigate = useNavigate();
  const [localPostStatus, setLocalPostStatus] = useState<'publish' | 'draft' | 'future' | 'private' | 'trash'>(
    form.watch('post_status'),
  );

  const createCourseMutation = useCreateCourseMutation();
  const updateCourseMutation = useUpdateCourseMutation();

  const previewLink = useWatch({ name: 'preview_link' });
  const postStatus = useWatch({ name: 'post_status' });
  const postVisibility = useWatch({ name: 'visibility' });
  const postDate = useWatch({ name: 'post_date' });
  const isPostDateDirty = form.formState.dirtyFields.post_date;

  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const isAdmin = tutorConfig.current_user.roles.includes(TutorRoles.ADMINISTRATOR);
  const hasTrashAccess = tutorConfig.settings.instructor_can_delete_course === 'on' || isAdmin;

  const handleSubmit = async (data: CourseFormData, postStatus: 'publish' | 'draft' | 'future' | 'trash') => {
    const triggerAndFocus = (field: keyof CourseFormData) => {
      Promise.resolve().then(() => {
        form.trigger(field);
        form.setFocus(field);
      });
    };

    const navigateToBasicsWithError = () => {
      navigate('/basics', { state: { isError: true } });
    };

    if (data.course_pricing_category !== 'subscription' && data.course_price_type === 'paid') {
      if (tutorConfig.settings.monetize_by === 'edd' && !data.course_product_id) {
        navigateToBasicsWithError();
        triggerAndFocus('course_product_id');
        return;
      }

      if (
        (tutorConfig.settings.monetize_by === 'wc' || tutorConfig.settings.monetize_by === 'tutor') &&
        data.course_price === ''
      ) {
        navigateToBasicsWithError();
        triggerAndFocus('course_price');
        return;
      }
    }

    const isAdditionalFieldsDirty = [
      'course_benefits',
      'course_target_audience',
      'course_target_audience',
      'course_duration_minutes',
      'course_material_includes',
      'course_requirements',
    ].some((field) => form.formState.dirtyFields[field as keyof CourseFormData]);
    const isCoursePrerequisitesDirty = !!form.formState.dirtyFields.course_prerequisites;
    const isCourseAttachmentsDirty = !!form.formState.dirtyFields.course_attachments;

    const payload = convertCourseDataToPayload({
      ...data,
      _tutor_attachments_main_edit: isCourseAttachmentsDirty,
      _tutor_prerequisites_main_edit: isCoursePrerequisitesDirty,
      _tutor_course_additional_data_edit: isAdditionalFieldsDirty,
    });
    setLocalPostStatus(postStatus);

    if (courseId) {
      updateCourseMutation.mutate({
        course_id: Number(courseId),
        ...payload,
        post_status: determinePostStatus(postStatus as 'trash' | 'future' | 'draft', postVisibility),
      });
      return;
    }

    const response = await createCourseMutation.mutateAsync({ ...payload });

    if (response.data) {
      window.location.href = `${config.TUTOR_API_BASE_URL}/wp-admin/admin.php?page=create-course&course_id=${response.data}`;
    }
  };

  const dropdownButton = () => {
    let text: string;
    let action: 'publish' | 'draft' | 'future';

    if (isBefore(new Date(), new Date(postDate))) {
      text = isPostDateDirty ? __('Schedule', 'tutor') : __('Update', 'tutor');
      action = 'future';
    } else if (!courseId || (postStatus === 'draft' && !isBefore(new Date(), new Date(postDate)))) {
      text = __('Publish', 'tutor');
      action = 'publish';
    } else {
      text = __('Update', 'tutor');
      action = 'publish';
    }

    return { text, action };
  };

  const dropdownItems = () => {
    const previewItem = {
      text: (
        <div
          css={[
            styleUtils.display.flex(),
            {
              alignItems: 'center',
            },
          ]}
        >
          {__('Preview', 'tutor')}
          <SVGIcon name="linkExternal" width={24} height={24} />
        </div>
      ),
      onClick:
        !courseId || (postStatus === 'draft' && courseId) ? () => window.open(previewLink, '_blank', 'noopener') : noop,
      isDanger: false,
    };

    const moveToTrashItem = {
      text: <>{__('Move to trash', 'tutor')}</>,
      onClick: async () => {
        if (hasTrashAccess) {
          try {
            await form.handleSubmit((data) => handleSubmit(data, 'trash'))();
          } catch (error) {
            console.error(error);
          } finally {
            window.location.href = `${tutorConfig.home_url}/wp-admin/admin.php?page=tutor`;
          }
        }
      },
      isDanger: true,
    };

    const switchToDraftItem = {
      text: <>{__('Switch to draft', 'tutor')}</>,
      onClick: form.handleSubmit((data) => handleSubmit(data, 'draft')),
      isDanger: false,
    };

    const backToLegacyItem = {
      text: (
        <div
          css={[
            styleUtils.display.flex(),
            {
              alignItems: 'center',
            },
          ]}
        >
          {__('Legacy mode', 'tutor')}
          <SVGIcon name="linkExternal" width={24} height={24} />
        </div>
      ),
      onClick: () => {
        const legacyUrl = courseId
          ? `${config.TUTOR_API_BASE_URL}/wp-admin/post.php?post=${courseId}&action=edit`
          : `${config.TUTOR_API_BASE_URL}/wp-admin/post-new.php?post_type=courses`;

        window.open(legacyUrl, '_blank', 'noopener');
      },
      isDanger: false,
    };

    const items = [previewItem];

    if (courseId && postStatus !== 'draft') {
      items.pop();
      items.push(switchToDraftItem);
    }

    items.push(moveToTrashItem, backToLegacyItem);

    return items;
  };

  return (
    <div css={styles.wrapper}>
      <button
        type="button"
        css={[styleUtils.resetButton, styles.logo]}
        onClick={() => {
          window.open(tutorConfig.tutor_frontend_dashboard_url, '_blank', 'noopener');
        }}
      >
        <Show
          when={isTutorPro && tutorConfig.settings.course_builder_logo_url}
          fallback={<Logo width={108} height={24} />}
        >
          {(logo) => <img src={logo} alt="Tutor LMS" />}
        </Show>
      </button>

      <div css={styles.container}>
        <div css={styles.titleAndTacker}>
          <h6 css={styles.title}>{__('Course Builder', 'tutor')}</h6>
          <span css={styles.divider} />
          <Tracker />
        </div>
        <div css={styles.headerRight}>
          <Show
            when={postStatus === 'draft' && postVisibility !== 'private'}
            fallback={
              <Button
                variant="text"
                icon={<SVGIcon name="linkExternal" width={24} height={24} />}
                iconPosition="right"
                onClick={() => {
                  window.open(previewLink, '_blank', 'noopener');
                }}
                disabled={!previewLink}
              >
                {__('Preview', 'tutor')}
              </Button>
            }
          >
            <Button
              size="small"
              variant="secondary"
              icon={<SVGIcon name="upload" width={24} height={24} />}
              loading={localPostStatus === 'draft' && updateCourseMutation.isPending}
              iconPosition="left"
              onClick={form.handleSubmit((data) => handleSubmit(data, 'draft'))}
            >
              {__('Save draft', 'tutor')}
            </Button>
          </Show>

          <DropdownButton
            text={dropdownButton().text}
            variant="primary"
            loading={
              createCourseMutation.isPending ||
              ((localPostStatus === 'publish' || localPostStatus === 'future') && updateCourseMutation.isPending)
            }
            onClick={form.handleSubmit((data) => handleSubmit(data, dropdownButton().action))}
            dropdownMaxWidth="164px"
            disabledDropdown={!form.formState.isDirty && !courseId}
          >
            {dropdownItems().map((item, index) => (
              <DropdownButton.Item key={index} text={item.text} onClick={item.onClick} isDanger={item.isDanger} />
            ))}
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

    img {
      max-height: 24px;
      width: auto;
      object-fit: contain;
      object-position: center;
    }
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
