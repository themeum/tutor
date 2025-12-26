import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useCallback, useMemo, useState } from 'react';
import { type UseFormReturn } from 'react-hook-form';

import Checkbox from '@TutorShared/atoms/CheckBox';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Table, { type Column } from '@TutorShared/molecules/Table';

import { type Coupon } from '@CouponDetails/services/coupon';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { useMembershipPlansQuery } from '@TutorShared/services/subscription';
import { formatPrice } from '@TutorShared/utils/currency';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type MembershipPlan } from '@TutorShared/utils/types';
import { formatSubscriptionRepeatUnit } from '@TutorShared/utils/util';

import SearchField from './SearchField';

interface MembershipPlanListTableProps {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<Coupon, any, undefined>;
}

const MembershipPlanListTable = ({ form }: MembershipPlanListTableProps) => {
  const selectedPlans = useMemo(() => form.watch('membershipPlans') || [], [form]);
  const getMembershipPlanListQuery = useMembershipPlansQuery();
  const [searchTerm, setSearchTerm] = useState<string>('');

  const plans = useMemo(() => {
    if (!getMembershipPlanListQuery.data) return [];

    const enabledPlans = getMembershipPlanListQuery.data.filter((plan) => plan.is_enabled === '1');

    if (!searchTerm) {
      return enabledPlans;
    }

    return enabledPlans.filter((plan) => plan.plan_name.toLowerCase().includes(searchTerm.toLowerCase()));
  }, [getMembershipPlanListQuery.data, searchTerm]);

  const handleSearch = useCallback((filter: { search?: string }) => {
    setSearchTerm(filter.search || '');
  }, []);

  const toggleSelection = useCallback(
    (isChecked = false) => {
      const selectedPlanIds = selectedPlans.map((plan) => plan.id);
      const fetchedPlanIds = plans.map((plan) => plan.id);

      if (isChecked) {
        const newPlans = plans.filter((plan) => !selectedPlanIds.includes(plan.id));
        form.setValue('membershipPlans', [...selectedPlans, ...newPlans]);
        return;
      }

      const newPlans = selectedPlans.filter((plan) => !fetchedPlanIds.includes(plan.id));
      form.setValue('membershipPlans', newPlans);
    },
    [form, plans, selectedPlans],
  );

  function handleAllIsChecked() {
    return plans.every((plan) => selectedPlans.map((selectedPlan) => selectedPlan.id).includes(plan.id));
  }

  const columns: Column<MembershipPlan>[] = [
    {
      Header: plans.length ? (
        <Checkbox
          onChange={toggleSelection}
          checked={
            getMembershipPlanListQuery.isLoading || getMembershipPlanListQuery.isRefetching
              ? false
              : handleAllIsChecked()
          }
          label={__('Membership Plans', 'tutor')}
          labelCss={styles.checkboxLabel}
        />
      ) : (
        '#'
      ),
      Cell: (item) => {
        return (
          <div css={styles.title}>
            <Checkbox
              onChange={() => {
                const filteredItems = selectedPlans.filter((plan) => plan.id !== item.id);
                const isNewItem = filteredItems?.length === selectedPlans.length;

                if (isNewItem) {
                  form.setValue('membershipPlans', [...filteredItems, item]);
                } else {
                  form.setValue('membershipPlans', filteredItems);
                }
              }}
              checked={selectedPlans.map((plan) => plan.id).includes(item.id)}
            />
            <SVGIcon name="crownOutlined" width={32} height={32} />
            <div>
              {item.plan_name}
              <Show when={item.is_featured === '1'}>
                <SVGIcon name="star" width={20} height={20} />
              </Show>
            </div>
          </div>
        );
      },
    },
    {
      Header: <div css={styles.tablePriceLabel}>{__('Price', 'tutor')}</div>,
      Cell: (item) => {
        return (
          <div css={styles.priceWrapper}>
            <div css={styles.price}>
              <span>{formatPrice(Number(item.sale_price) || Number(item.regular_price))}</span>
              {Number(item.sale_price) > 0 && (
                <span css={styles.discountPrice}>{formatPrice(Number(item.regular_price))}</span>
              )}
              /
              <span css={styles.recurringInterval}>
                {formatSubscriptionRepeatUnit({ unit: item.recurring_interval, value: Number(item.recurring_value) })}
              </span>
            </div>
          </div>
        );
      },
    },
  ];

  if (getMembershipPlanListQuery.isLoading) {
    return <LoadingSection />;
  }

  if (!getMembershipPlanListQuery.data) {
    return <div css={styles.errorMessage}>{__('Something went wrong', 'tutor')}</div>;
  }

  return (
    <>
      <div css={styles.tableActions}>
        <SearchField onFilterItems={handleSearch} />
      </div>

      <div css={styles.tableWrapper}>
        <Table columns={columns} data={plans} loading={getMembershipPlanListQuery.isFetching} />
      </div>
    </>
  );
};

export default MembershipPlanListTable;

const styles = {
  tableLabel: css`
    text-align: left;
  `,
  tablePriceLabel: css`
    text-align: right;
  `,
  tableActions: css`
    padding: ${spacing[20]};
  `,
  tableWrapper: css`
    max-height: calc(100vh - 350px);
    overflow: auto;
  `,
  checkboxLabel: css`
    ${typography.body()};
    color: ${colorTokens.text.primary};
  `,
  title: css`
    height: 48px;
    ${typography.caption()};
    color: ${colorTokens.text.primary};
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};

    svg {
      flex-shrink: 0;
      color: ${colorTokens.icon.hints};
    }

    div {
      ${styleUtils.display.flex()};
      align-items: center;
      gap: ${spacing[4]};

      svg {
        color: ${colorTokens.icon.brand};
      }
    }
  `,
  priceWrapper: css`
    ${styleUtils.display.flex()};
    align-items: center;
    justify-content: flex-end;
    height: 48px;
    text-align: right;
  `,
  price: css`
    ${typography.caption()};
    display: flex;
    gap: ${spacing[2]};
    justify-content: end;
  `,
  discountPrice: css`
    text-decoration: line-through;
    color: ${colorTokens.text.subdued};
  `,
  recurringInterval: css`
    text-transform: capitalize;
    color: ${colorTokens.text.hints};
  `,
  errorMessage: css`
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
  `,
};
