import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { format, isBefore } from 'date-fns';
import { useEffect, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import DropdownButton from '@Molecules/DropdownButton';

import { useModal } from '@Components/modals/Modal';
import SuccessModal from '@CourseBuilderComponents/modals/SuccessModal';

import {
  convertBundleFormDataToPayload,
  useSaveCourseBundle,
  type BundleFormData,
} from '@BundleBuilderServices/bundle';
import config, { tutorConfig } from '@Config/config';
import { CURRENT_VIEWPORT, DateFormats, TutorRoles } from '@Config/constants';
import { spacing } from '@Config/styles';
import Show from '@Controls/Show';
import { type PostStatus } from '@CourseBuilderServices/course';
import { determinePostStatus } from '@CourseBuilderUtils/utils';
import { styleUtils } from '@Utils/style-utils';
import { convertToGMT, noop } from '@Utils/util';

import reviewSubmitted2x from '@Images/review-submitted-2x.webp';
import reviewSubmitted from '@Images/review-submitted.webp';
import { getBundleId } from '../../../utils/utils';

const bundleId = getBundleId();

const HeaderActions = () => {
  const form = useFormContext<BundleFormData>();
  const { showModal } = useModal();
  const postStatus = useWatch({ name: 'post_status' });
  const postVisibility = useWatch({ name: 'visibility' });
  const previewLink = useWatch({ name: 'preview_link' });
  const isScheduleEnabled = useWatch({ name: 'isScheduleEnabled' });
  const scheduleDate = useWatch({ name: 'schedule_date' });
  const scheduleTime = useWatch({ name: 'schedule_time' });

  const [localPostStatus, setLocalPostStatus] = useState<PostStatus>(postStatus);

  const saveCourseBundleMutation = useSaveCourseBundle();

  const isPostDateDirty = form.formState.dirtyFields.schedule_date || form.formState.dirtyFields.schedule_time;

  const isAdmin = tutorConfig.current_user.roles.includes(TutorRoles.ADMINISTRATOR);
  const isInstructor = tutorConfig.current_user.roles.includes(TutorRoles.TUTOR_INSTRUCTOR);
  const hasTrashAccess = tutorConfig.settings?.instructor_can_delete_course === 'on' || isAdmin;
  const hasWpAdminAccess = tutorConfig.settings?.hide_admin_bar_for_users === 'off';
  const isAllowedToPublishCourse = tutorConfig.settings?.instructor_can_publish_course === 'on';

  const handleSubmit = async (data: BundleFormData, postStatus: PostStatus) => {
    const payload = convertBundleFormDataToPayload(data);
    setLocalPostStatus(postStatus);

    if (bundleId) {
      const determinedPostStatus = determinePostStatus(postStatus, postVisibility);

      const response = await saveCourseBundleMutation.mutateAsync({
        ...payload,
        ...(bundleId ? { ID: bundleId } : {}),
        post_status: determinedPostStatus,
        ...(!data.isScheduleEnabled
          ? {
              post_date: format(new Date(), DateFormats.yearMonthDayHourMinuteSecond24H),
              post_date_gmt: convertToGMT(new Date()),
            }
          : {}),
        ...(data.isScheduleEnabled && {
          edit_date: true,
        }),
      });

      if (!response.data) {
        return;
      }

      if (postStatus === 'pending') {
        showModal({
          component: SuccessModal,
          props: {
            title: __('Course submitted for review', 'tutor'),
            description: __('Thank you for submitting your course. It will be reviewed by our team shortly.', 'tutor'),
            image: reviewSubmitted,
            image2x: reviewSubmitted2x,
            imageAlt: __('Course submitted for review', 'tutor'),
            wrapperCss: css`
              align-items: center;
              text-align: center;
            `,
            actions: (
              <div css={styleUtils.flexCenter()}>
                <Button
                  onClick={() => {
                    if (window.location.href.includes('wp-admin')) {
                      window.location.href = tutorConfig.backend_bundle_list_url;
                    } else {
                      window.location.href = tutorConfig.frontend_bundle_list_url;
                    }
                  }}
                  size="small"
                >
                  {__('Back to Courses', 'tutor')}
                </Button>
              </div>
            ),
          },
        });
      }

      return;
    }
  };

  const dropdownButton = (): {
    text: string;
    action: PostStatus;
  } => {
    if (!isAllowedToPublishCourse && !isAdmin && isInstructor) {
      return { text: __('Submit', 'tutor'), action: 'pending' };
    }

    const isInFuture = isBefore(new Date(), new Date(`${scheduleDate} ${scheduleTime}`));
    const isNewOrDraft = !bundleId || ['pending', 'draft'].includes(postStatus);

    if (isNewOrDraft) {
      const shouldSchedule = isPostDateDirty && isScheduleEnabled && isInFuture;

      return {
        text: shouldSchedule ? __('Schedule', 'tutor') : __('Publish', 'tutor'),
        action: shouldSchedule ? 'future' : 'publish',
      };
    }

    if (isScheduleEnabled) {
      const shouldSchedule = isPostDateDirty && isInFuture;

      return {
        text: shouldSchedule ? __('Schedule', 'tutor') : __('Update', 'tutor'),
        action: 'future',
      };
    }

    return { text: __('Update', 'tutor'), action: 'publish' };
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
        !bundleId || (postStatus === 'draft' && bundleId) ? () => window.open(previewLink, '_blank', 'noopener') : noop,
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
            window.location.href = window.location.href.includes('wp-admin')
              ? tutorConfig.backend_course_list_url
              : tutorConfig.frontend_course_list_url;
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
        const legacyUrl = bundleId
          ? `${config.TUTOR_SITE_URL}/wp-admin/post.php?post=${bundleId}&action=edit`
          : `${config.TUTOR_SITE_URL}/wp-admin/post-new.php?post_type=courses`;

        window.open(legacyUrl, '_blank', 'noopener');
      },
      isDanger: false,
    };

    const publishImmediatelyItem = {
      text: <>{__('Publish immediately', 'tutor')}</>,
      onClick: form.handleSubmit((data) =>
        handleSubmit(
          {
            ...data,
            isScheduleEnabled: false,
          },
          'publish',
        ),
      ),
      isDanger: false,
    };

    const items = [previewItem];

    if (
      (isAdmin || isAllowedToPublishCourse) &&
      isScheduleEnabled &&
      isBefore(new Date(), new Date(`${scheduleDate} ${scheduleTime}`))
    ) {
      items.unshift(publishImmediatelyItem);
    }

    if (bundleId && postStatus !== 'draft') {
      items.pop();

      if (isAdmin || isAllowedToPublishCourse) {
        items.push(switchToDraftItem);
      }
    }

    if (isAdmin || hasTrashAccess) {
      items.push(moveToTrashItem);
    }

    if (isAdmin || hasWpAdminAccess) {
      items.push(backToLegacyItem);
    }

    return items;
  };

  useEffect(() => {
    if (saveCourseBundleMutation.isSuccess) {
      form.reset(form.getValues());
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [saveCourseBundleMutation.isSuccess]);

  return (
    <div css={styles.headerRight}>
      <Show
        when={postStatus === 'draft' && postVisibility !== 'private'}
        fallback={
          <Button
            variant="text"
            icon={<SVGIcon name="linkExternal" width={24} height={24} />}
            iconPosition="right"
            onClick={() => window.open(previewLink, '_blank', 'noopener')}
            disabled={!previewLink}
            size={CURRENT_VIEWPORT.isAboveDesktop ? 'regular' : 'small'}
          >
            <Show when={CURRENT_VIEWPORT.isAboveDesktop}>{__('Preview', 'tutor')}</Show>
          </Button>
        }
      >
        <Button
          variant="secondary"
          icon={<SVGIcon name="upload" width={24} height={24} />}
          loading={localPostStatus === 'draft' && saveCourseBundleMutation.isPending}
          iconPosition="left"
          buttonCss={css`
            padding-inline: ${spacing[16]};
          `}
          onClick={form.handleSubmit((data) => handleSubmit(data, 'draft'))}
          size={CURRENT_VIEWPORT.isAboveDesktop ? 'regular' : 'small'}
        >
          <Show when={CURRENT_VIEWPORT.isAboveDesktop}>{__('Save as Draft', 'tutor')}</Show>
        </Button>
      </Show>

      <Show
        when={dropdownItems().length > 1}
        fallback={
          <Button
            size={CURRENT_VIEWPORT.isAboveDesktop ? 'regular' : 'small'}
            loading={['publish', 'future', 'pending'].includes(localPostStatus) && saveCourseBundleMutation.isPending}
            onClick={form.handleSubmit((data) => handleSubmit(data, dropdownButton().action))}
          >
            {dropdownButton().text}
          </Button>
        }
      >
        <DropdownButton
          text={dropdownButton().text}
          size={CURRENT_VIEWPORT.isAboveDesktop ? 'regular' : 'small'}
          variant="primary"
          loading={['publish', 'future', 'pending'].includes(localPostStatus) && saveCourseBundleMutation.isPending}
          onClick={form.handleSubmit((data) => handleSubmit(data, dropdownButton().action))}
          dropdownMaxWidth={
            isScheduleEnabled && isBefore(new Date(), new Date(`${scheduleDate} ${scheduleTime}`)) ? '190px' : '164px'
          }
          disabledDropdown={dropdownItems().length === 0}
        >
          {dropdownItems().map((item, index) => (
            <DropdownButton.Item key={index} text={item.text} onClick={item.onClick} isDanger={item.isDanger} />
          ))}
        </DropdownButton>
      </Show>
    </div>
  );
};

const styles = {
  headerRight: css`
    display: flex;
    align-items: center;
    gap: ${spacing[12]};
  `,
};

export default HeaderActions;
