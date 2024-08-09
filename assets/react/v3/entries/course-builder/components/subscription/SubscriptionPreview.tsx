import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import { useModal } from '@Components/modals/Modal';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import SubscriptionModal from '@CourseBuilderComponents/modals/SubscriptionModal';
import { convertSubscriptionToFormData, useCourseSubscriptionsQuery } from '@CourseBuilderServices/subscription';
import { styleUtils } from '@Utils/style-utils';

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
      <div css={styles.header}>
        <p>{__('Subscriptions')}</p>
        <Show when={subscriptions.length > 0}>
          <button
            type="button"
            css={styles.editButton}
            onClick={() => {
              showModal({
                component: SubscriptionModal,
                props: {
                  title: __('Manage Subscriptions', 'tutor'),
                  icon: <SVGIcon name="dollar-recurring" width={24} height={24} />,
                },
              });
            }}
          >
            <SVGIcon name="edit" width={24} height={24} />
          </button>
        </Show>
      </div>
      <Show
        when={subscriptions.length > 0}
        fallback={
          <div css={styles.emptyState}>
            <Button
              variant="secondary"
              icon={<SVGIcon name="dollar-recurring" width={24} height={24} />}
              onClick={() => {
                showModal({
                  component: SubscriptionModal,
                  props: {
                    title: __('Create Subscriptions', 'tutor'),
                    icon: <SVGIcon name="dollar-recurring" width={24} height={24} />,
                  },
                });
              }}
            >
              {__('Add Subscription', 'tutor')}
            </Button>
          </div>
        }
      >
        <div css={styles.inner}>
          <For each={subscriptions}>
            {(subscription, index) => (
              <PreviewItem key={index} subscription={convertSubscriptionToFormData(subscription)} />
            )}
          </For>
        </div>
      </Show>
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
  inner: css`
		border: 1px solid ${colorTokens.stroke.default};
		border-radius: ${borderRadius.card};
		width: 100%;
		margin-top: ${spacing[8]};
		overflow: hidden;
	`,
  header: css`
		display: flex;
		align-items: center;
		justify-content: space-between;
		${typography.body()};
		color: ${colorTokens.text.title};
	`,
  editButton: css`
		${styleUtils.resetButton};
		color: ${colorTokens.icon.default};
		transition: color 0.3s ease;
		&:hover {
			color: ${colorTokens.icon.hover};
		}
	`,
  emptyState: css`
		width: 100%;
		
		& > button {
			width: 100%;
		}
	`,
};
