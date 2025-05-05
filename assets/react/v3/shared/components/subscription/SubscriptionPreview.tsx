import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@TutorShared/atoms/Button';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { useModal } from '@TutorShared/components/modals/Modal';
import SubscriptionModal from '@TutorShared/components/modals/SubscriptionModal';
import { PreviewItem } from '@TutorShared/components/subscription/PreviewItem';

import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { convertSubscriptionToFormData, useCourseSubscriptionsQuery } from '@TutorShared/services/subscription';

interface SubscriptionPreviewProps {
  courseId: number;
  isBundle?: boolean;
}

function SubscriptionPreview({ courseId, isBundle = false }: SubscriptionPreviewProps) {
  const courseSubscriptionsQuery = useCourseSubscriptionsQuery(courseId);
  const { showModal } = useModal();

  if (courseSubscriptionsQuery.isLoading) {
    return <LoadingSection />;
  }

  if (!courseSubscriptionsQuery.data) {
    return null;
  }

  const subscriptions = courseSubscriptionsQuery.data;

  return (
    <div css={styles.outer}>
      <Show when={subscriptions.length > 0}>
        <div css={styles.header}>{__('Subscriptions', 'tutor')}</div>
      </Show>

      <div
        css={styles.inner({
          hasSubscriptions: subscriptions.length > 0,
        })}
      >
        <For each={subscriptions}>
          {(subscription, index) => (
            <PreviewItem
              key={index}
              subscription={convertSubscriptionToFormData(subscription)}
              courseId={courseId}
              isBundle={isBundle}
            />
          )}
        </For>

        <div
          css={styles.emptyState({
            hasSubscriptions: subscriptions.length > 0,
          })}
        >
          <Button
            data-cy="add-subscription"
            variant="secondary"
            icon={<SVGIcon name="dollar-recurring" width={24} height={24} />}
            onClick={() => {
              showModal({
                component: SubscriptionModal,
                props: {
                  title: __('Manage Subscription Plans', 'tutor'),
                  icon: <SVGIcon name="dollar-recurring" width={24} height={24} />,
                  createEmptySubscriptionOnMount: true,
                  courseId,
                  isBundle,
                },
              });
            }}
          >
            {__('Add Subscription', 'tutor')}
          </Button>
        </div>
      </div>
    </div>
  );
}

export default SubscriptionPreview;
const styles = {
  outer: css`
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
  inner: ({ hasSubscriptions }: { hasSubscriptions: boolean }) => css`
    background: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius.card};
    width: 100%;
    overflow: hidden;

    ${!hasSubscriptions &&
    css`
      border: none;
    `}
  `,
  header: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    ${typography.body()};
    color: ${colorTokens.text.title};
  `,
  emptyState: ({ hasSubscriptions }: { hasSubscriptions: boolean }) => css`
    padding: ${hasSubscriptions ? `${spacing[8]} ${spacing[12]}` : 0};
    width: 100%;

    & > button {
      width: 100%;
    }
  `,
};
