import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useRef, useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import ProBadge from '@TutorShared/atoms/ProBadge';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import EmptyState from '@TutorShared/molecules/EmptyState';
import Popover from '@TutorShared/molecules/Popover';

import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';

import type { CourseDetailsResponse, GoogleMeet, MeetingType, ZoomMeeting } from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import config, { tutorConfig } from '@TutorShared/config/config';
import { Addons, CURRENT_VIEWPORT } from '@TutorShared/config/constants';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { isAddonEnabled, noop } from '@TutorShared/utils/util';

import GoogleMeetMeetingCard from './meeting/GoogleMeetCard';
import GoogleMeetForm from './meeting/GoogleMeetForm';
import ZoomMeetingCard from './meeting/ZoomMeetingCard';
import ZoomMeetingForm from './meeting/ZoomMeetingForm';

import liveClassPro2x from '@SharedImages/pro-placeholders/live-class-2x.webp';
import liveClassPro from '@SharedImages/pro-placeholders/live-class.webp';
import { withVisibilityControl } from '@TutorShared/hoc/withVisibilityControl';
import { POPOVER_PLACEMENTS } from '@TutorShared/hooks/usePortalPopover';

const isTutorPro = !!tutorConfig.tutor_pro_url;
const isZoomAddonEnabled = isAddonEnabled(Addons.TUTOR_ZOOM_INTEGRATION);
const isGoogleMeetAddonEnabled = isAddonEnabled(Addons.TUTOR_GOOGLE_MEET_INTEGRATION);

const courseId = getCourseId();

const LiveClass = () => {
  const queryClient = useQueryClient();
  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;

  const zoomMeetings = courseDetails?.zoom_meetings ?? ([] as ZoomMeeting[]);
  const zoomUsers = courseDetails?.zoom_users ?? ({} as { [key: string]: string });

  const googleMeetMeetings = courseDetails?.google_meet_meetings ?? ([] as GoogleMeet[]);

  const [showMeetingForm, setShowMeetingForm] = useState<MeetingType | null>(null);

  const zoomButtonRef = useRef<HTMLButtonElement>(null);
  const googleMeetButtonRef = useRef<HTMLButtonElement>(null);

  if (isTutorPro && !isZoomAddonEnabled && !isGoogleMeetAddonEnabled) {
    return null;
  }

  return (
    <div css={styles.liveClass}>
      <span css={styles.label}>
        {__('Schedule Live Class', 'tutor')}
        {!isTutorPro && <ProBadge content={__('Pro', 'tutor')} />}
      </span>
      <Show
        when={isTutorPro}
        fallback={
          <EmptyState
            size="small"
            removeBorder={false}
            emptyStateImage={liveClassPro}
            emptyStateImage2x={liveClassPro2x}
            imageAltText={__('Tutor LMS PRO', 'tutor')}
            title={__('Bring your courses to life and engage students with interactive live classes.', 'tutor')}
            actions={
              <Button
                size="small"
                icon={<SVGIcon name="crown" width={24} height={24} />}
                onClick={() => {
                  window.open(config.TUTOR_PRICING_PAGE, '_blank', 'noopener');
                }}
              >
                {__('Get Tutor LMS Pro', 'tutor')}
              </Button>
            }
          />
        }
      >
        <Show when={isZoomAddonEnabled || isGoogleMeetAddonEnabled}>
          <Show when={isZoomAddonEnabled}>
            <div
              css={styles.meetingsWrapper({
                hasMeeting: zoomMeetings.length > 0,
              })}
            >
              <For each={zoomMeetings}>
                {(meeting) => (
                  <div
                    key={meeting.ID}
                    css={styles.meeting({
                      hasMeeting: zoomMeetings.length > 0,
                    })}
                  >
                    <ZoomMeetingCard data={meeting} meetingHost={zoomUsers} />
                  </div>
                )}
              </For>
              <div
                css={styles.meetingsFooter({
                  hasMeeting: zoomMeetings.length > 0,
                })}
              >
                <Button
                  data-cy="create-zoom-meeting"
                  variant="secondary"
                  icon={<SVGIcon name="zoomColorize" width={24} height={24} />}
                  buttonCss={css`
                    width: 100%;
                  `}
                  onClick={() => setShowMeetingForm('zoom')}
                  ref={zoomButtonRef}
                >
                  {__('Create a Zoom Meeting', 'tutor')}
                </Button>
              </div>
            </div>
          </Show>

          <Show when={isGoogleMeetAddonEnabled}>
            <div
              css={styles.meetingsWrapper({
                hasMeeting: googleMeetMeetings.length > 0,
              })}
            >
              <For each={googleMeetMeetings}>
                {(meeting) => (
                  <div
                    key={meeting.ID}
                    css={styles.meeting({
                      hasMeeting: googleMeetMeetings.length > 0,
                    })}
                  >
                    <GoogleMeetMeetingCard data={meeting} />
                  </div>
                )}
              </For>
              <div
                css={styles.meetingsFooter({
                  hasMeeting: googleMeetMeetings.length > 0,
                })}
              >
                <Button
                  data-cy="create-google-meet-link"
                  variant="secondary"
                  icon={<SVGIcon name="googleMeetColorize" width={24} height={24} />}
                  buttonCss={css`
                    width: 100%;
                  `}
                  onClick={() => setShowMeetingForm('google_meet')}
                  ref={googleMeetButtonRef}
                >
                  {__('Create a Google Meet Link', 'tutor')}
                </Button>
              </div>
            </div>
          </Show>
        </Show>
      </Show>

      <Popover
        triggerRef={zoomButtonRef}
        isOpen={showMeetingForm === 'zoom'}
        closePopover={noop}
        animationType={AnimationType.slideUp}
        closeOnEscape={false}
        placement={CURRENT_VIEWPORT.isAboveMobile ? POPOVER_PLACEMENTS.BOTTOM : POPOVER_PLACEMENTS.ABSOLUTE_CENTER}
      >
        <ZoomMeetingForm
          data={null}
          meetingHost={zoomUsers}
          onCancel={() => {
            setShowMeetingForm(null);
          }}
        />
      </Popover>
      <Popover
        triggerRef={googleMeetButtonRef}
        isOpen={showMeetingForm === 'google_meet'}
        closePopover={noop}
        animationType={AnimationType.slideUp}
        closeOnEscape={false}
        placement={CURRENT_VIEWPORT.isAboveMobile ? POPOVER_PLACEMENTS.BOTTOM : POPOVER_PLACEMENTS.ABSOLUTE_CENTER}
        arrow={false}
      >
        <GoogleMeetForm
          data={null}
          onCancel={() => {
            setShowMeetingForm(null);
          }}
        />
      </Popover>
    </div>
  );
};

export default withVisibilityControl(LiveClass);

const styles = {
  label: css`
    ${styleUtils.display.inlineFlex()}
    align-items: center;
    gap: ${spacing[4]};
    ${typography.body()}
    color: ${colorTokens.text.title};
  `,
  liveClass: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
  `,
  meetingsWrapper: ({ hasMeeting }: { hasMeeting: boolean }) => css`
    ${styleUtils.display.flex('column')}
    background-color: ${colorTokens.background.white};
    border-radius: ${borderRadius.card};

    ${hasMeeting &&
    css`
      border: 1px solid ${colorTokens.stroke.default};
    `}
  `,
  meeting: ({ hasMeeting }: { hasMeeting: boolean }) => css`
    padding: ${spacing[8]} ${spacing[8]} ${spacing[12]} ${spacing[8]};
    ${hasMeeting &&
    css`
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    `}
  `,
  meetingsFooter: ({ hasMeeting }: { hasMeeting: boolean }) => css`
    width: 100%;
    ${hasMeeting &&
    css`
      padding: ${spacing[12]} ${spacing[8]};
    `}
  `,
};
