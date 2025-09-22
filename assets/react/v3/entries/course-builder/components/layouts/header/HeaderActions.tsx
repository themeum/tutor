import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { format, isBefore } from 'date-fns';
import { useEffect, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import DropdownButton from '@TutorShared/molecules/DropdownButton';

import { useModal } from '@TutorShared/components/modals/Modal';
import SuccessModal from '@TutorShared/components/modals/SuccessModal';

import {
  convertCourseDataToPayload,
  useCreateCourseMutation,
  useUpdateCourseMutation,
  type CourseDetailsResponse,
  type CourseFormData,
} from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import config, { tutorConfig } from '@TutorShared/config/config';
import { CURRENT_VIEWPORT, DateFormats, TutorRoles } from '@TutorShared/config/constants';
import { spacing } from '@TutorShared/config/styles';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { isDefined, type WPPostStatus } from '@TutorShared/utils/types';
import { convertToGMT, determinePostStatus, findSlotFields, noop } from '@TutorShared/utils/util';

import { CourseBuilderRouteConfigs } from '@CourseBuilderConfig/route-configs';
import { useCourseBuilderSlot } from '@CourseBuilderContexts/CourseBuilderSlotContext';
import reviewSubmitted2x from '@SharedImages/review-submitted-2x.webp';
import reviewSubmitted from '@SharedImages/review-submitted.webp';

const courseId = getCourseId();

const HeaderActions = () => {
  const { fields } = useCourseBuilderSlot();
  const form = useFormContext<CourseFormData>();
  const navigate = useNavigate();
  const { showModal } = useModal();
  const postStatus = useWatch({ name: 'post_status' });
  const postVisibility = useWatch({ name: 'visibility' });
  const previewLink = useWatch({ name: 'preview_link' });
  const isScheduleEnabled = useWatch({ name: 'isScheduleEnabled' });
  const scheduleDate = useWatch({ name: 'schedule_date' });
  const scheduleTime = useWatch({ name: 'schedule_time' });

  const [localPostStatus, setLocalPostStatus] = useState<WPPostStatus>(postStatus);

  const queryClient = useQueryClient();
  const createCourseMutation = useCreateCourseMutation();
  const updateCourseMutation = useUpdateCourseMutation();

  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;
  const isPostDateDirty = form.formState.dirtyFields.schedule_date || form.formState.dirtyFields.schedule_time;

  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const isAdmin = tutorConfig.current_user.roles?.includes(TutorRoles.ADMINISTRATOR);
  const isInstructor = tutorConfig.current_user.roles?.includes(TutorRoles.TUTOR_INSTRUCTOR);
  const hasTrashAccess = tutorConfig.settings?.instructor_can_delete_course === 'on' || isAdmin;
  const hasWpAdminAccess = tutorConfig.settings?.hide_admin_bar_for_users === 'off';
  const isAllowedToPublishCourse = tutorConfig.settings?.instructor_can_publish_course === 'on';

  const handleSubmit = async (data: CourseFormData, postStatus: WPPostStatus) => {
    const triggerAndFocus = (field: keyof CourseFormData) => {
      Promise.resolve().then(() => {
        form.trigger(field, { shouldFocus: true });
      });
    };

    const navigateToBasicsWithError = () => {
      navigate(CourseBuilderRouteConfigs.CourseBasics.buildLink(), { state: { isError: true } });
    };

    if (
      data.isScheduleEnabled &&
      (!data.schedule_date ||
        !data.schedule_time ||
        !isBefore(new Date(), new Date(`${data.schedule_date} ${data.schedule_time}`)))
    ) {
      navigateToBasicsWithError();
      form.setValue('showScheduleForm', true, { shouldDirty: true });
      if (!data.schedule_date) {
        triggerAndFocus('schedule_date');
        return;
      }
      if (!data.schedule_time) {
        triggerAndFocus('schedule_time');
        return;
      }
    }

    if (data.course_price_type === 'paid') {
      if (!data.course_product_id && tutorConfig.settings?.monetize_by === 'edd') {
        navigateToBasicsWithError();
        triggerAndFocus('course_product_id');
        return;
      }

      if (
        (isTutorPro && tutorConfig.settings?.monetize_by === 'wc' && data.course_product_id !== '-1') ||
        (tutorConfig.settings?.monetize_by === 'tutor' &&
          !['membership', 'subscription'].includes(data.course_selling_option))
      ) {
        if (data.course_price === '' || Number(data.course_price) <= 0) {
          navigateToBasicsWithError();
          triggerAndFocus('course_price');
          return;
        }

        if (data.course_sale_price && Number(data.course_sale_price) >= Number(data.course_price)) {
          navigateToBasicsWithError();
          triggerAndFocus('course_sale_price');
          return;
        }
      }
    }

    const payload = convertCourseDataToPayload(
      data,
      findSlotFields({ fields: fields.Basic }, { fields: fields.Additional }),
    );
    setLocalPostStatus(postStatus);

    if (courseId) {
      const determinedPostStatus = determinePostStatus(postStatus, postVisibility);

      const response = await updateCourseMutation.mutateAsync({
        course_id: Number(courseId),
        ...payload,
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
        ...(['subscription', 'membership'].includes(data.course_selling_option) &&
          (!isDefined(courseDetails.course_pricing.price) || Number(courseDetails.course_pricing.price) === 0) && {
            course_price: 1,
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
                  as="a"
                  href={
                    window.location.href.includes('wp-admin')
                      ? tutorConfig.backend_course_list_url
                      : tutorConfig.frontend_course_list_url
                  }
                  size="small"
                >
                  {__('Back to Courses', 'tutor')}
                </Button>
              </div>
            ),
          },
        });
      }

      if (
        isInstructor &&
        tutorConfig.settings?.enable_redirect_on_course_publish_from_frontend === 'on' &&
        ['publish', 'future'].includes(determinedPostStatus)
      ) {
        window.location.href = config.TUTOR_MY_COURSES_PAGE_URL;
      }

      return;
    }

    const response = await createCourseMutation.mutateAsync({ ...payload });

    if (response.data) {
      window.location.href = `${config.TUTOR_SITE_URL}/wp-admin/admin.php?page=create-course&course_id=${response.data}`;
    }
  };

  const dropdownButton = (): {
    text: string;
    action: WPPostStatus;
  } => {
    if (!isAllowedToPublishCourse && !isAdmin && isInstructor) {
      return { text: __('Submit', 'tutor'), action: 'pending' };
    }

    const isInFuture = isBefore(new Date(), new Date(`${scheduleDate} ${scheduleTime}`));
    const isNewOrDraft = !courseId || ['pending', 'draft'].includes(postStatus);

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
        !courseId || (postStatus === 'draft' && courseId) ? () => window.open(previewLink, '_blank', 'noopener') : noop,
      isDanger: false,
      dataCy: 'preview-course',
    };

    const moveToTrashItem = {
      text: <>{__('Move to Trash', 'tutor')}</>,
      onClick: async () => {
        if (hasTrashAccess) {
          try {
            await form.handleSubmit((data) => handleSubmit(data, 'trash'))();
          } finally {
            window.location.href = window.location.href.includes('wp-admin')
              ? tutorConfig.backend_course_list_url
              : tutorConfig.frontend_course_list_url;
          }
        }
      },
      isDanger: true,
      dataCy: 'move-to-trash',
    };

    const switchToDraftItem = {
      text: <>{__('Switch to Draft', 'tutor')}</>,
      onClick: form.handleSubmit((data) => handleSubmit(data, 'draft')),
      isDanger: false,
      dataCy: 'switch-to-draft',
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
          {__('Legacy Mode', 'tutor')}
          <SVGIcon name="linkExternal" width={24} height={24} />
        </div>
      ),
      onClick: () => {
        const legacyUrl = courseId
          ? `${config.TUTOR_SITE_URL}/wp-admin/post.php?post=${courseId}&action=edit`
          : `${config.TUTOR_SITE_URL}/wp-admin/post-new.php?post_type=courses`;

        window.open(legacyUrl, '_blank', 'noopener');
      },
      isDanger: false,
      dataCy: 'back-to-legacy',
    };

    const publishImmediatelyItem = {
      text: <>{__('Publish Immediately', 'tutor')}</>,
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
      dataCy: 'publish-immediately',
    };

    const items = [previewItem];

    if (
      (isAdmin || isAllowedToPublishCourse) &&
      isScheduleEnabled &&
      isBefore(new Date(), new Date(`${scheduleDate} ${scheduleTime}`))
    ) {
      items.unshift(publishImmediatelyItem);
    }

    if (courseId && postStatus !== 'draft') {
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
    if (updateCourseMutation.isSuccess) {
      form.reset(form.getValues());
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [updateCourseMutation.isSuccess]);

  return (
    <div css={styles.headerRight}>
      <Show
        when={postStatus === 'draft' && postVisibility !== 'private'}
        fallback={
          <Button
            as="a"
            href={previewLink}
            target="_blank"
            variant="text"
            icon={<SVGIcon name="linkExternal" width={24} height={24} />}
            iconPosition="right"
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
          loading={localPostStatus === 'draft' && updateCourseMutation.isPending}
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
        when={dropdownItems().length > 0}
        fallback={
          <Button
            data-cy="course-builder-submit-button"
            size={CURRENT_VIEWPORT.isAboveDesktop ? 'regular' : 'small'}
            loading={
              createCourseMutation.isPending ||
              (['publish', 'future', 'pending'].includes(localPostStatus) && updateCourseMutation.isPending)
            }
            onClick={form.handleSubmit((data) => handleSubmit(data, dropdownButton().action))}
          >
            {dropdownButton().text}
          </Button>
        }
      >
        <DropdownButton
          data-cy="course-builder-submit-button"
          text={dropdownButton().text}
          size={CURRENT_VIEWPORT.isAboveDesktop ? 'regular' : 'small'}
          variant="primary"
          loading={
            createCourseMutation.isPending ||
            (['publish', 'future', 'pending'].includes(localPostStatus) && updateCourseMutation.isPending)
          }
          onClick={form.handleSubmit((data) => handleSubmit(data, dropdownButton().action))}
          dropdownMaxWidth={
            isScheduleEnabled && isBefore(new Date(), new Date(`${scheduleDate} ${scheduleTime}`)) ? '190px' : '164px'
          }
          disabledDropdown={dropdownItems().length === 0}
        >
          {dropdownItems().map((item, index) => (
            <DropdownButton.Item
              key={index}
              text={item.text}
              onClick={item.onClick}
              isDanger={item.isDanger}
              data-cy={item.dataCy}
            />
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
