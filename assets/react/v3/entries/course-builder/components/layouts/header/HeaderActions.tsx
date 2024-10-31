import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { format, isBefore } from 'date-fns';
import { useEffect, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import DropdownButton from '@Molecules/DropdownButton';

import { useModal } from '@Components/modals/Modal';
import SuccessModal from '@CourseBuilderComponents/modals/SuccessModal';

import config, { tutorConfig } from '@Config/config';
import { DateFormats, TutorRoles } from '@Config/constants';
import { spacing } from '@Config/styles';
import Show from '@Controls/Show';
import {
  type CourseDetailsResponse,
  type CourseFormData,
  type PostStatus,
  convertCourseDataToPayload,
  useCreateCourseMutation,
  useUpdateCourseMutation,
} from '@CourseBuilderServices/course';
import { determinePostStatus, getCourseId } from '@CourseBuilderUtils/utils';
import { styleUtils } from '@Utils/style-utils';
import { noop } from '@Utils/util';

import reviewSubmitted2x from '@Images/review-submitted-2x.webp';
import reviewSubmitted from '@Images/review-submitted.webp';

const courseId = getCourseId();

const HeaderActions = () => {
  const form = useFormContext<CourseFormData>();
  const navigate = useNavigate();
  const { showModal } = useModal();
  const queryClient = useQueryClient();
  const postStatus = useWatch({ name: 'post_status' });
  const postVisibility = useWatch({ name: 'visibility' });
  const previewLink = useWatch({ name: 'preview_link' });
  const isScheduleEnabled = useWatch({ name: 'isScheduleEnabled' });
  const postDate = useWatch({ name: 'post_date' });

  const [localPostStatus, setLocalPostStatus] = useState<PostStatus>(postStatus);

  const createCourseMutation = useCreateCourseMutation();
  const updateCourseMutation = useUpdateCourseMutation();

  const isPostDateDirty = form.formState.dirtyFields.post_date;

  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;

  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const isAdmin = tutorConfig.current_user.roles.includes(TutorRoles.ADMINISTRATOR);
  const isInstructor = tutorConfig.current_user.roles.includes(TutorRoles.TUTOR_INSTRUCTOR);
  const hasTrashAccess = tutorConfig.settings?.instructor_can_delete_course === 'on' || isAdmin;
  const hasWpAdminAccess = tutorConfig.settings?.hide_admin_bar_for_users === 'off';
  const isPendingAdminApproval = tutorConfig.settings?.enable_course_review_moderation === 'off';

  const handleSubmit = async (data: CourseFormData, postStatus: PostStatus) => {
    const triggerAndFocus = (field: keyof CourseFormData) => {
      Promise.resolve().then(() => {
        form.trigger(field);
        form.setFocus(field);
      });
    };

    const navigateToBasicsWithError = () => {
      navigate('/basics', { state: { isError: true } });
    };

    if (
      data.isScheduleEnabled &&
      (!data.schedule_date ||
        !data.schedule_time ||
        !isBefore(new Date(), new Date(`${data.schedule_date} ${data.schedule_time}`)))
    ) {
      navigateToBasicsWithError();
      form.setValue('showScheduleForm', true, { shouldDirty: true });
      triggerAndFocus('schedule_date');
      triggerAndFocus('schedule_time');
      return;
    }

    if (data.course_price_type === 'paid') {
      if (!data.course_product_id && tutorConfig.settings?.monetize_by === 'edd') {
        navigateToBasicsWithError();
        triggerAndFocus('course_product_id');
        return;
      }

      if ((isTutorPro && tutorConfig.settings?.monetize_by === 'wc') || tutorConfig.settings?.monetize_by === 'tutor') {
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

    const payload = convertCourseDataToPayload(data);
    setLocalPostStatus(postStatus);

    if (courseId) {
      const determinedPostStatus = determinePostStatus(postStatus, postVisibility);

      const response = await updateCourseMutation.mutateAsync({
        course_id: Number(courseId),
        ...payload,
        post_status: determinedPostStatus,
        ...(determinedPostStatus === 'draft' ||
        (determinedPostStatus === 'publish' && isBefore(new Date(), new Date(courseDetails?.post_date ?? postDate)))
          ? {
              post_date: format(new Date(), DateFormats.yearMonthDayHourMinuteSecond24H),
              post_date_gmt: format(new Date(), DateFormats.yearMonthDayHourMinuteSecond24H),
            }
          : {}),
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
                      window.location.href = tutorConfig.backend_course_list_url;
                    } else {
                      window.location.href = tutorConfig.frontend_course_list_url;
                    }
                  }}
                  size="small"
                >
                  {__('Back to courses', 'tutor')}
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
      window.location.href = `${config.TUTOR_API_BASE_URL}/wp-admin/admin.php?page=create-course&course_id=${response.data}`;
    }
  };

  const dropdownButton = () => {
    let text: string;
    let action: PostStatus;

    if (!isPendingAdminApproval && !isAdmin && isInstructor) {
      text = __('Submit', 'tutor');
      action = 'pending';
    } else if (isScheduleEnabled) {
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
        const legacyUrl = courseId
          ? `${config.TUTOR_API_BASE_URL}/wp-admin/post.php?post=${courseId}&action=edit`
          : `${config.TUTOR_API_BASE_URL}/wp-admin/post-new.php?post_type=courses`;

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
            post_date: format(new Date(), DateFormats.yearMonthDayHourMinuteSecond24H),
          },
          'publish',
        ),
      ),
      isDanger: false,
    };

    const items = [previewItem];

    if (isBefore(new Date(), new Date(postDate))) {
      items.unshift(publishImmediatelyItem);
    }

    if (courseId && postStatus !== 'draft') {
      items.pop();

      if (isAdmin || isPendingAdminApproval) {
        items.push(switchToDraftItem);
      }
    }

    if (isAdmin || hasWpAdminAccess) {
      items.push(moveToTrashItem, backToLegacyItem);
    } else if (hasTrashAccess) {
      items.push(moveToTrashItem);
    }

    return items;
  };

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (updateCourseMutation.isSuccess) {
      form.reset(form.getValues());
    }
  }, [updateCourseMutation.isSuccess]);

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
          onClick={form.handleSubmit((data) =>
            handleSubmit(
              {
                ...data,
                post_date: isPostDateDirty ? postDate : format(new Date(), DateFormats.yearMonthDayHourMinuteSecond24H),
              },
              'draft',
            ),
          )}
        >
          {__('Save as Draft', 'tutor')}
        </Button>
      </Show>

      <Show
        when={dropdownItems().length > 1}
        fallback={
          <Button
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
          text={dropdownButton().text}
          variant="primary"
          loading={
            createCourseMutation.isPending ||
            (['publish', 'future', 'pending'].includes(localPostStatus) && updateCourseMutation.isPending)
          }
          onClick={form.handleSubmit((data) => handleSubmit(data, dropdownButton().action))}
          dropdownMaxWidth={['draft', 'future'].includes(postStatus) ? '190px' : '164px'}
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
