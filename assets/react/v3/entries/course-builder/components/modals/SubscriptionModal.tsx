import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import type { ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { SubscriptionEmptyState } from '@CourseBuilderComponents/subscription/SubscriptionEmptyState';
import SubscriptionItem from '@CourseBuilderComponents/subscription/SubscriptionItem';
import { type Subscription, defaultSubscription } from '@CourseBuilderServices/subscription';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';

interface SubscriptionModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  subscriptions: (Subscription & { isExpanded: boolean })[];
  courseId: number;
}

export default function SubscriptionModal({
  title,
  subtitle,
  icon,
  closeModal,
  subscriptions,
}: SubscriptionModalProps) {
  const [items, setItems] = useState(subscriptions);
  const [isExpandedAll, setIsExpandedAll] = useState(false);

  useEffect(() => {
    setItems(subscriptions.map((item, index) => ({ ...item, isExpanded: index === 0 })));
  }, [subscriptions]);

  return (
    <ModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      icon={icon}
      title={title}
      subtitle={subtitle}
      actions={
        <>
          <Button variant="text" size="small" onClick={() => closeModal()}>
            {__('Cancel', 'tutor')}
          </Button>
          <Button size="small" onClick={() => closeModal()}>
            {__('Done', 'tutor')}
          </Button>
        </>
      }
    >
      <div css={styles.wrapper}>
        <Show
          when={items.length}
          fallback={
            <SubscriptionEmptyState
              onCreateSubscription={() => {
                setItems([{ ...defaultSubscription, isExpanded: true }]);
              }}
            />
          }
        >
          <div css={styles.container}>
            <div css={styles.header}>
              <h6>{__('Subscriptions', 'tutor')}</h6>
              <Button
                variant="text"
                onClick={() => {
                  if (isExpandedAll) {
                    // All are expanded already, so collapse all
                    setItems((previous) => previous.map((data) => ({ ...data, isExpanded: false })));
                    setIsExpandedAll(false);
                    return;
                  }

                  setItems((previous) => previous.map((data) => ({ ...data, isExpanded: true })));
                  setIsExpandedAll(true);
                }}
              >
                {!isExpandedAll ? __('Expand All', 'tutor') : __('Collapse All', 'tutor')}
              </Button>
            </div>
            <div css={styles.content}>
              <For each={items}>
                {(subscription) => {
                  return (
                    <SubscriptionItem
                      key={subscription.id}
                      subscription={subscription}
                      toggleCollapse={(id) => {
                        setItems((previous) => {
                          return previous.map((item) => {
                            if (item.id === id) {
                              return { ...item, isExpanded: !item.isExpanded };
                            }
                            return { ...item, isExpanded: false };
                          });
                        });
                      }}
                    />
                  );
                }}
              </For>
              <div>
                <Button
                  variant="secondary"
                  icon={<SVGIcon name="plusSquareBrand" width={24} height={24} />}
                  onClick={() => {
                    setItems((previous) => {
                      const newItems = previous.map((item) => ({ ...item, isExpanded: false }));
                      const subscriptionId = Math.max(...newItems.map((item) => item.id)) + 1;
                      return [...newItems, { ...defaultSubscription, id: subscriptionId, isExpanded: true }];
                    });
                  }}
                >
                  {__('Add Subscription', 'tutor')}
                </Button>
              </div>
            </div>
          </div>
        </Show>
      </div>
    </ModalWrapper>
  );
}

const styles = {
  wrapper: css`
		width: 1218px;
	`,
  container: css`
		max-width: 640px;
		width: 100%;
		margin: ${spacing[40]} auto;
		display: flex;
		flex-direction: column;
		gap: ${spacing[32]};
	`,
  header: css`
		display: flex;
		align-items: center;
    justify-content: space-between;

		h6 {
			${typography.heading6('medium')};
			color: ${colorTokens.text.primary};
		}
	`,
  content: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
	`,
};
