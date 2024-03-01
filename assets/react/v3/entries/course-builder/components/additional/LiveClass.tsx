import React from 'react';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import Show from '@Controls/Show';
import EmptyState from '@Molecules/EmptyState';

// @TODO: will come from app config api later.
const isPro = true;
const hasLiveAddons = true;

const LiveClass = () => {
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
          <Button
            variant="secondary"
            icon={<SVGIcon name="zoomColorize" width={24} height={24} />}
            buttonContentCss={css`
              justify-content: center;
            `}
            onClick={() => {
              alert('@TODO: Will be implemented in future');
            }}
          >
            {__('Create a Zoom meeting', 'tutor')}
          </Button>
          <Button
            variant="secondary"
            icon={<SVGIcon name="googleMeetColorize" width={24} height={24} />}
            buttonContentCss={css`
              justify-content: center;
            `}
            onClick={() => {
              alert('@TODO: Will be implemented in future');
            }}
          >
            {__('Create a Google Meet', 'tutor')}
          </Button>
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
            {__('Create a Jitsi Meet', 'tutor')}
          </Button>
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
