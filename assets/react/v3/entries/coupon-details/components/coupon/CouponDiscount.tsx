import CouponSelectItemModal from '@CouponDetails/components/modals/CourseListModal';
import { Box, BoxTitle } from '@TutorShared/atoms/Box';
import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import { useModal } from '@TutorShared/components/modals/Modal';
import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import Show from '@TutorShared/controls/Show';

import type { Coupon } from '@CouponDetails/services/coupon';
import coursePlaceholder from '@SharedImages/course-placeholder.png';
import { Addons } from '@TutorShared/config/constants';
import { typography } from '@TutorShared/config/typography';
import { formatPrice } from '@TutorShared/utils/currency';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { formatSubscriptionRepeatUnit, isAddonEnabled } from '@TutorShared/utils/util';
import { requiredRule } from '@TutorShared/utils/validation';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import type { ReactNode } from 'react';
import { Controller, useFormContext } from 'react-hook-form';

const isTutorProActive = !!tutorConfig.tutor_pro_url;
const displayBundle = isTutorProActive && isAddonEnabled(Addons.COURSE_BUNDLE);
const isSubscriptionActive = isTutorProActive && isAddonEnabled(Addons.SUBSCRIPTION);

const discountTypeOptions = [
  { label: __('Percent', 'tutor'), value: 'percentage' },
  { label: __('Amount', 'tutor'), value: 'flat' },
];

const appliesToOptions = [
  { label: __('All courses', 'tutor'), value: 'all_courses' },
  ...(displayBundle
    ? [
        { label: __('All bundles', 'tutor'), value: 'all_bundles' },
        { label: __('All courses and bundles', 'tutor'), value: 'all_courses_and_bundles' },
      ]
    : []),
  ...(isSubscriptionActive ? [{ label: __('All membership plans', 'tutor'), value: 'all_membership_plans' }] : []),
  { label: __('Specific courses', 'tutor'), value: 'specific_courses' },
  ...(displayBundle ? [{ label: __('Specific bundles', 'tutor'), value: 'specific_bundles' }] : []),
  { label: __('Specific category', 'tutor'), value: 'specific_category' },
  ...(isSubscriptionActive
    ? [
        {
          label: __('Specific membership plans', 'tutor'),
          value: 'specific_membership_plans',
        },
      ]
    : []),
];

function CouponDiscount() {
  const form = useFormContext<Coupon>();
  const { tutor_currency } = tutorConfig;
  const { showModal } = useModal();

  const appliesTo = form.watch('applies_to');
  const discountType = form.watch('discount_type');
  const courses = form.watch('courses') ?? [];
  const bundles = form.watch('bundles') ?? [];
  const categories = form.watch('categories') ?? [];
  const membershipPlans = form.watch('membershipPlans') ?? [];

  const modalType = {
    specific_courses: 'courses',
    specific_bundles: 'bundles',
    specific_category: 'categories',
    specific_membership_plans: 'membershipPlans',
  } as const;

  function removesSelectedItem(type: string, id: number) {
    if (type === 'courses') {
      form.setValue(
        type,
        courses?.filter((item) => item.id !== id),
      );
    }
    if (type === 'bundles') {
      form.setValue(
        type,
        bundles?.filter((item) => item.id !== id),
      );
    }
    if (type === 'categories') {
      form.setValue(
        type,
        categories?.filter((item) => item.id !== id),
      );
    }
    if (type === 'membershipPlans') {
      form.setValue(
        type,
        membershipPlans?.filter((item) => item.id !== id),
      );
    }
  }

  return (
    <Box bordered css={styles.discountWrapper}>
      <div css={styles.couponWrapper}>
        <BoxTitle>{__('Discount', 'tutor')}</BoxTitle>
      </div>
      <div css={styles.discountTypeWrapper}>
        <Controller
          name="discount_type"
          control={form.control}
          rules={requiredRule()}
          render={(controllerProps) => (
            <FormSelectInput {...controllerProps} label={__('Discount Type', 'tutor')} options={discountTypeOptions} />
          )}
        />
        <Controller
          name="discount_amount"
          control={form.control}
          rules={requiredRule()}
          render={(controllerProps) => (
            <FormInputWithContent
              {...controllerProps}
              type="number"
              label={__('Discount Value', 'tutor')}
              placeholder="0"
              content={discountType === 'flat' ? (tutor_currency?.symbol ?? '$') : '%'}
              contentCss={styleUtils.inputCurrencyStyle}
            />
          )}
        />
      </div>
      <Controller
        name="applies_to"
        control={form.control}
        rules={requiredRule()}
        render={(controllerProps) => (
          <FormSelectInput {...controllerProps} label={__('Applies to', 'tutor')} options={appliesToOptions} />
        )}
      />

      {appliesTo === 'specific_courses' && courses.length > 0 && (
        <div css={styles.selectedWrapper}>
          {courses?.map((item) => (
            <AppliesToItem
              key={item.id}
              type="courses"
              image={item.image}
              title={item.title}
              subTitle={
                <div css={styles.price}>
                  {item.plan_start_price ? (
                    <span css={styles.startingFrom}>
                      {
                        // translators: %s is the starting price of the plan
                        sprintf(__('Starting from %s', 'tutor'), item.plan_start_price)
                      }
                    </span>
                  ) : (
                    <>
                      <span>{item.sale_price ? item.sale_price : item.regular_price}</span>
                      {item.sale_price && <span css={styles.discountPrice}>{item.regular_price}</span>}
                    </>
                  )}
                </div>
              }
              handleDeleteClick={() => removesSelectedItem('courses', item.id)}
            />
          ))}
        </div>
      )}

      {appliesTo === 'specific_bundles' && bundles.length > 0 && (
        <div css={styles.selectedWrapper}>
          {bundles?.map((item) => (
            <AppliesToItem
              key={item.id}
              type="bundles"
              image={item.image}
              title={item.title}
              subTitle={
                <div css={styles.price}>
                  <span>{item.sale_price ? item.sale_price : item.regular_price}</span>
                  {item.sale_price && <span css={styles.discountPrice}>{item.regular_price}</span>}
                </div>
              }
              handleDeleteClick={() => removesSelectedItem('bundles', item.id)}
            />
          ))}
        </div>
      )}

      {appliesTo === 'specific_category' && categories.length > 0 && (
        <div css={styles.selectedWrapper}>
          {categories?.map((item) => (
            <AppliesToItem
              key={item.id}
              type="categories"
              image={item.image}
              title={item.title}
              subTitle={`${item.total_courses} ${__('Courses', 'tutor')}`}
              handleDeleteClick={() => removesSelectedItem('categories', item.id)}
            />
          ))}
        </div>
      )}

      {appliesTo === 'specific_membership_plans' && membershipPlans.length > 0 && (
        <div css={styles.selectedWrapper}>
          {form.watch('membershipPlans')?.map((item) => (
            <AppliesToItem
              key={item.id}
              type="membershipPlans"
              title={item.plan_name}
              subTitle={
                <div css={styles.price}>
                  <span>{formatPrice(Number(item.sale_price) || Number(item.regular_price))}</span>
                  {Number(item.sale_price) > 0 && (
                    <span css={styles.discountPrice}>{formatPrice(Number(item.regular_price))}</span>
                  )}
                  /
                  <span css={styles.recurringInterval}>
                    {formatSubscriptionRepeatUnit({
                      unit: item.recurring_interval,
                      value: Number(item.recurring_value),
                    })}
                  </span>
                </div>
              }
              handleDeleteClick={() => removesSelectedItem('membershipPlans', item.id)}
            />
          ))}
        </div>
      )}

      <Show
        when={['specific_courses', 'specific_bundles', 'specific_category', 'specific_membership_plans'].includes(
          appliesTo,
        )}
      >
        <Button
          variant="tertiary"
          isOutlined={true}
          buttonCss={styles.addCoursesButton}
          icon={<SVGIcon name="plusSquareBrand" width={24} height={25} />}
          onClick={() => {
            showModal({
              component: CouponSelectItemModal,
              props: {
                title: __('Select items', 'tutor'),
                type: modalType[appliesTo as keyof typeof modalType],
                form,
              },
              closeOnOutsideClick: true,
            });
          }}
        >
          {__('Add Items', 'tutor')}
        </Button>
      </Show>
    </Box>
  );
}

export default CouponDiscount;

interface AppliesToItemProps {
  type: 'courses' | 'bundles' | 'categories' | 'membershipPlans';
  image?: string;
  title: string;
  subTitle: string | ReactNode;
  handleDeleteClick: () => void;
}

function AppliesToItem({ type, image, title, subTitle, handleDeleteClick }: AppliesToItemProps) {
  return (
    <div css={styles.selectedItem}>
      <div css={styles.selectedThumb}>
        {type !== 'membershipPlans' ? (
          <img src={image || coursePlaceholder} css={styles.thumbnail} alt="course item" />
        ) : (
          <SVGIcon name="crownOutlined" width={32} height={32} />
        )}
      </div>
      <div css={styles.selectedContent}>
        <div css={styles.selectedTitle}>{title}</div>
        <div css={styles.selectedSubTitle}>{subTitle}</div>
      </div>
      <div>
        <Button variant="text" onClick={handleDeleteClick}>
          <SVGIcon name="delete" width={24} height={24} />
        </Button>
      </div>
    </div>
  );
}

const styles = {
  discountWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,
  discountTypeWrapper: css`
    display: flex;
    gap: ${spacing[20]};
  `,
  couponWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
  `,
  addCoursesButton: css`
    width: fit-content;
    color: ${colorTokens.text.brand};

    svg {
      color: ${colorTokens.text.brand};
    }
  `,
  price: css`
    display: flex;
    gap: ${spacing[4]};
  `,
  discountPrice: css`
    text-decoration: line-through;
  `,
  selectedWrapper: css`
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[6]};
  `,
  selectedItem: css`
    padding: ${spacing[12]};
    display: flex;
    align-items: center;
    gap: ${spacing[16]};

    &:not(:last-child) {
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    }
  `,
  selectedContent: css`
    width: 100%;
  `,
  selectedTitle: css`
    ${typography.small()};
    color: ${colorTokens.text.primary};
    margin-bottom: ${spacing[4]};
  `,
  selectedSubTitle: css`
    ${typography.small()};
    color: ${colorTokens.text.hints};
  `,
  selectedThumb: css`
    height: 48px;
    color: ${colorTokens.icon.hints};
    ${styleUtils.flexCenter()};
    flex-shrink: 0;
  `,
  thumbnail: css`
    width: 48px;
    height: 48px;
    border-radius: ${borderRadius[4]};
  `,
  startingFrom: css`
    color: ${colorTokens.text.hints};
  `,
  recurringInterval: css`
    text-transform: capitalize;
    color: ${colorTokens.text.hints};
  `,
};
