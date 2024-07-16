import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import type { ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import type { Subscription } from '@CourseBuilderComponents/course-basic/SubscriptionPreview';
import SubscriptionItem from '@CourseBuilderComponents/subscription/SubscriptionItem';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';

interface SubscriptionModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  subscriptions: (Subscription & { isEdit: boolean })[];
}

export default function SubscriptionModal({
  title,
  subtitle,
  icon,
  closeModal,
  subscriptions,
}: SubscriptionModalProps) {
  const [items, setItems] = useState(subscriptions);

  useEffect(() => {
    setItems(subscriptions.map((item, index) => ({ ...item, isEdit: index === 0 })));
  }, [subscriptions]);

  return (
    <ModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} icon={icon} title={title} subtitle={subtitle}>
      <div css={styles.wrapper}>
        <div css={styles.container}>
          <div css={styles.header}>
            <button type="button" css={styles.backButton}>
              <SVGIcon name="arrowLeft" width={24} height={24} />
            </button>
            <h6>{__('Subscriptions', 'tutor')}</h6>
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
                            return { ...item, isEdit: !item.isEdit };
                          }
                          return { ...item, isEdit: false };
                        });
                      });
                    }}
                  />
                );
              }}
            </For>
            <div>
              <Button variant="secondary" icon={<SVGIcon name="plusSquareBrand" width={24} height={24} />}>
                {__('Add Subscription', 'tutor')}
              </Button>
            </div>
          </div>
        </div>
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
		gap: ${spacing[16]};

		h6 {
			${typography.heading6('medium')};
			color: ${colorTokens.text.primary};
		}
	`,
  content: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[32]};
	`,
  backButton: css`
		${styleUtils.resetButton};
		width: 32px;
		height: 32px;
		border: 1px solid ${colorTokens.stroke.default};
		border-radius: ${borderRadius[4]};
		display: flex;
		justify-content: center;
		align-items: center;

		svg {
			color: ${colorTokens.icon.default};
			transition: color 0.3s ease;
		}

		&:hover {
			svg {
				color: ${colorTokens.icon.hover};
			}
		}
	`,
};
