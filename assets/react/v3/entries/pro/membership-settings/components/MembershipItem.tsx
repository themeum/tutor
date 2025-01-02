import {
  type MembershipSettings,
  type MembershipPlan,
  useDeleteMembershipPlanMutation,
  type DurationUnit,
  useDuplicateMembershipPlanMutation,
} from '../services/memberships';
import MembershipModal from './modals/MembershipModal';
import { __, sprintf } from '@wordpress/i18n';
import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import { useModal } from '@/v3/shared/components/modals/Modal';
import { css } from '@emotion/react';
import { borderRadius, colorTokens, fontSize, fontWeight, lineHeight, spacing } from '@/v3/shared/config/styles';
import { Controller, useFormContext } from 'react-hook-form';
import FormSwitch from '@/v3/shared/components/fields/FormSwitch';
import ThreeDots from '@/v3/shared/molecules/ThreeDots';
import { useState } from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { animateLayoutChanges } from '@/v3/shared/utils/dndkit';
import { CSS } from '@dnd-kit/utilities';
import StaticConfirmationModal from '@/v3/shared/components/modals/StaticConfirmationModal';
import Show from '@/v3/shared/controls/Show';
import { formatPrice } from '@/v3/shared/utils/currency';
import { makeFirstCharacterUpperCase } from '@/v3/shared/utils/util';
import { AnimationType } from '@/v3/shared/hooks/useAnimation';

interface MembershipItemProps {
  data: MembershipPlan;
  index: number;
}

function formatRepeatUnit(unit: Omit<DurationUnit, 'hour'>, value: number) {
  switch (unit) {
    case 'hour':
      return value > 1 ? __('Hours', 'tutor') : __('Hour', 'tutor');
    case 'day':
      return value > 1 ? __('Days', 'tutor') : __('Day', 'tutor');
    case 'week':
      return value > 1 ? __('Weeks', 'tutor') : __('Week', 'tutor');
    case 'month':
      return value > 1 ? __('Months', 'tutor') : __('Month', 'tutor');
    case 'year':
      return value > 1 ? __('Years', 'tutor') : __('Year', 'tutor');
    case 'until_cancellation':
      return __('Until Cancellation', 'tutor');
  }
}

export default function MembershipItem({ data, index }: MembershipItemProps) {
  const form = useFormContext<MembershipSettings>();
  const { showModal } = useModal();

  const [isOpen, setIsOpen] = useState(false);

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: data.id,
    animateLayoutChanges,
  });

  const duplicateMembershipPlanMutation = useDuplicateMembershipPlanMutation();
  const deleteMembershipPlanMutation = useDeleteMembershipPlanMutation();

  const style = {
    transform: CSS.Transform.toString(transform ? { ...transform, scaleX: 1, scaleY: 1 } : null),
    transition,
    zIndex: isDragging ? 1 : 0,
  };

  return (
    <div ref={setNodeRef} style={style} css={styles.wrapper}>
      <button type="button" {...attributes} {...listeners} css={styles.dragButton} data-drag-button>
        <SVGIcon name="dragVertical" width={24} height={24} />
      </button>

      <div css={styles.content}>
        <SVGIcon name="priceTag" width={32} height={32} />
        <div css={styles.planInfo}>
          <h5 css={styles.planTitle}>
            <strong>{data.plan_name}</strong>
            <span />
            <div css={styles.planPerMonth}>
              {sprintf(
                __('%s per %s', 'tutor'),
                formatPrice(Number(data.regular_price)),
                makeFirstCharacterUpperCase(data.recurring_interval),
              )}
            </div>
          </h5>
          <p css={styles.planFeatures}>
            <span>
              {sprintf(
                __('Renews every %s %s', 'tutor'),
                data.recurring_value.toString().padStart(2, '0'),
                formatRepeatUnit(data.recurring_interval, Number(data.recurring_value)),
              )}
            </span>
            <Show when={data.provide_certificate === '1'}>
              <span css={styles.pipe}>|</span>
              <span>{__('Certificate available', 'tutor')}</span>
            </Show>
            <Show
              when={data.recurring_limit === '0'}
              fallback={
                <>
                  <span css={styles.pipe}>|</span>
                  <span>{sprintf(__('%s Times', 'tutor'), data.recurring_limit.toString().padStart(2, '0'))}</span>
                </>
              }
            >
              <span css={styles.pipe}>|</span>
              <span>{__('Until Cancellation', 'tutor')}</span>
            </Show>
          </p>
        </div>
      </div>

      <div css={styles.actions}>
        <Controller
          control={form.control}
          name={`plans.${index}.is_enabled` as 'plans.0.is_enabled'}
          render={(controllerProps) => <FormSwitch {...controllerProps} />}
        />
        <ThreeDots
          arrowPosition="top"
          animationType={AnimationType.slideDown}
          isOpen={isOpen}
          onClick={() => {
            setIsOpen(true);
          }}
          closePopover={() => setIsOpen(false)}
        >
          <ThreeDots.Option
            text={__('Edit', 'tutor')}
            icon={<SVGIcon name="edit" width={24} height={24} />}
            onClick={() => {
              showModal({
                component: MembershipModal,
                props: {
                  title: __('Update Membership', 'tutor'),
                  icon: <SVGIcon name="dollar-recurring" width={24} height={24} />,
                  plan: data,
                },
                depthIndex: 9999,
              });
            }}
            onClosePopover={() => setIsOpen(false)}
          />
          <ThreeDots.Option
            text={__('Duplicate', 'tutor')}
            icon={<SVGIcon name="copyPaste" width={24} height={24} />}
            onClick={() => {
              duplicateMembershipPlanMutation.mutate(data.id);
            }}
            onClosePopover={() => setIsOpen(false)}
          />
          <ThreeDots.Option
            text={__('Delete', 'tutor')}
            icon={<SVGIcon name="delete" width={24} height={24} />}
            isTrash={true}
            onClick={async () => {
              const { action } = await showModal({
                component: StaticConfirmationModal,
                props: {
                  title: __('Are you sure to delete this?', 'tutor'),
                  icon: <SVGIcon name="dollar-recurring" width={24} height={24} />,
                },
                depthIndex: 9999,
              });

              if (action === 'CONFIRM') {
                deleteMembershipPlanMutation.mutate(data.id);
              }
            }}
            onClosePopover={() => setIsOpen(false)}
          />
        </ThreeDots>
      </div>
    </div>
  );
}

const styles = {
  wrapper: css`
    background-color: ${colorTokens.background.white};
    padding: ${spacing[16]} ${spacing[24]};
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;

    &:hover {
      [data-drag-button] {
        display: block;
      }
    }

    &:not(:last-of-type) {
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    }

    &:first-of-type {
      border-top-left-radius: ${borderRadius[6]};
      border-top-right-radius: ${borderRadius[6]};
    }

    &:last-of-type {
      border-bottom-left-radius: ${borderRadius[6]};
      border-bottom-right-radius: ${borderRadius[6]};
    }
  `,
  content: css`
    display: flex;
    align-items: center;
    gap: ${spacing[12]};

    svg {
      color: ${colorTokens.icon.default};
    }
  `,
  planInfo: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[6]};
  `,
  planTitle: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};

    font-size: ${fontSize[16]};
    line-height: ${lineHeight[20]};
    font-weight: ${fontWeight.regular};
    color: ${colorTokens.text.primary};

    strong {
      font-weight: ${fontWeight.medium};
    }

    span {
      height: 2px;
      width: 2px;
      border-radius: ${borderRadius.circle};
      background-color: ${colorTokens.icon.default};
    }
  `,
  planPerMonth: css`
    color: ${colorTokens.text.title};
  `,
  planFeatures: css`
    font-size: ${fontSize[11]};
    line-height: ${lineHeight[16]};
    color: ${colorTokens.text.hints};
  `,
  actions: css`
    display: flex;
    align-items: center;
    gap: ${spacing[16]};
  `,
  dragButton: css`
    display: flex;
    align-items: center;
    padding: 0;
    color: ${colorTokens.icon.default};
    background: transparent;
    border: none;
    cursor: grab;

    position: absolute;
    height: 100%;
    left: -${spacing[24]};
    top: 0;
    display: none;

    :focus-visible {
      border-radius: ${borderRadius[4]};
      outline: 2px solid ${colorTokens.stroke.brand};
    }
  `,
  pipe: css`
    display: inline-block;
    color: ${colorTokens.stroke.divider};
    padding-inline: ${spacing[8]};
  `,
};
