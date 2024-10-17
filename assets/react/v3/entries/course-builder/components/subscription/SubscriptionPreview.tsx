import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import { useModal } from '@Components/modals/Modal';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import SubscriptionModal from '@CourseBuilderComponents/modals/SubscriptionModal';
import { convertSubscriptionToFormData, useCourseSubscriptionsQuery } from '@CourseBuilderServices/subscription';

import Show from '@/v3/shared/controls/Show';
import { PreviewItem } from './PreviewItem';

function SubscriptionPreview({ courseId }: { courseId: number }) {
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
        <div css={styles.header}>
          <p>{__('Subscriptions', 'tutor')}</p>
        </div>
      </Show>

      <div
        css={styles.inner({
          hasSubscriptions: subscriptions.length > 0,
        })}
      >
        <For each={subscriptions}>
          {(subscription, index) => (
            <PreviewItem key={index} subscription={convertSubscriptionToFormData(subscription)} />
          )}
        </For>

        <div
          css={styles.emptyState({
            hasSubscriptions: subscriptions.length > 0,
          })}
        >
          <Button
            variant="secondary"
            icon={<SVGIcon name="dollar-recurring" width={24} height={24} />}
            onClick={() => {
              showModal({
                component: SubscriptionModal,
                props: {
                  title: __('Manage Subscription Plans', 'tutor'),
                  icon: <SVGIcon name="dollar-recurring" width={24} height={24} />,
                  createEmptySubscriptionOnMount: true,
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
  inner: ({
    hasSubscriptions,
  }: {
    hasSubscriptions: boolean;
  }) => css`
    background: ${colorTokens.background.white};
		border: 1px solid ${colorTokens.stroke.default};
		border-radius: ${borderRadius.card};
		width: 100%;
		overflow: hidden;

    ${
      !hasSubscriptions &&
      css`
        border: none;
      `
    }
	`,
  header: css`
		display: flex;
		align-items: center;
		justify-content: space-between;
		${typography.body()};
		color: ${colorTokens.text.title};
	`,
  emptyState: ({
    hasSubscriptions,
  }: {
    hasSubscriptions: boolean;
  }) => css`
    padding: ${hasSubscriptions ? `${spacing[8]} ${spacing[12]}` : 0};
		width: 100%;
		
		& > button {
			width: 100%;
		}
	`,
};
