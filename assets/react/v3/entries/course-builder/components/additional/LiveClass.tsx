import React, { useState } from 'react';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import Show from '@Controls/Show';
import EmptyState from '@Molecules/EmptyState';
import MeetingForm, { MeetingType } from './MeetingForm';

// @TODO: will come from app config api later.
const isPro = true;
const hasLiveAddons = true;

const LiveClass = () => {
  const [showMeetingForm, setShowMeetingForm] = useState<MeetingType | null>(null);

  return (
    <div css={styles.liveClass}>
      <span css={styles.label}>
        {__('Live Class', 'tutor')}
        {!isPro && <SVGIcon name="crown" width={24} height={24} />}
      </span>
      <Show
        when={isPro}
        fallback={
          <EmptyState
            size="small"
            removeBorder={false}
            emptyStateImage="https://via.placeholder.com/360x360"
            emptyStateImage2x="https://via.placeholder.com/760x760"
            imageAltText="No live class addons found"
            title={__('Make the learning more interactive and fun using Live class feature! ', 'tutor')}
            description={__(
              'when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
              'tutor'
            )}
            actions={
              <Button
                icon={<SVGIcon name="crown" width={24} height={24} />}
                onClick={() => {
                  alert('@TODO: Will be implemented in future');
                }}
              >
                {__('Get Tutor LMS Pro', 'tutor')}
              </Button>
            }
          />
        }
      >
        <Show
          when={hasLiveAddons}
          fallback={
            <EmptyState
              size="small"
              removeBorder={false}
              emptyStateImage="https://via.placeholder.com/360x360"
              emptyStateImage2x="https://via.placeholder.com/760x760"
              imageAltText="No live class addons found"
              title={__('You can use this feature by activating Google meet, Zoom or Jitsi from addons', 'tutor')}
              description={__(
                'when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
                'tutor'
              )}
              actions={
                <Button
                  variant="secondary"
                  onClick={() => {
                    alert('@TODO: Will be implemented in future');
                  }}
                >
                  {__('Go to addons', 'tutor')}
                </Button>
              }
            />
          }
        >
          <Show
            when={showMeetingForm === 'zoom'}
            fallback={
              <Button
                variant="secondary"
                icon={<SVGIcon name="zoomColorize" width={24} height={24} />}
                buttonContentCss={css`
                  justify-content: center;
                `}
                onClick={() => setShowMeetingForm('zoom')}
              >
                {__('Create a Zoom meeting', 'tutor')}
              </Button>
            }
          >
            <MeetingForm type="zoom" setShowMeetingForm={setShowMeetingForm} />
          </Show>

          <Show
            when={showMeetingForm === 'google_meet'}
            fallback={
              <Button
                variant="secondary"
                icon={<SVGIcon name="googleMeetColorize" width={24} height={24} />}
                buttonContentCss={css`
                  justify-content: center;
                `}
                onClick={() => setShowMeetingForm('google_meet')}
              >
                {__('Create a Google Meet', 'tutor')}
              </Button>
            }
          >
            <MeetingForm type="google_meet" setShowMeetingForm={setShowMeetingForm} />
          </Show>

          <Show
            when={showMeetingForm === 'jitsi'}
            fallback={
              <Button
                variant="secondary"
                icon={<SVGIcon name="jitsiColorize" width={24} height={24} />}
                buttonContentCss={css`
                  justify-content: center;
                `}
                onClick={() => {
                  alert('@TODO: Will be implemented in future');
                }}
              >
                {__('Create a Jitsi meeting', 'tutor')}
              </Button>
            }
          >
            <MeetingForm type="jitsi" setShowMeetingForm={setShowMeetingForm} />
          </Show>
        </Show>
      </Show>
    </div>
  );
};

export default LiveClass;

const styles = {
  label: css`
    display: inline-flex;
    align-items: center;
    gap: ${spacing[2]};
    ${typography.body()}
    color: ${colorTokens.text.title};
  `,
  liveClass: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
};
