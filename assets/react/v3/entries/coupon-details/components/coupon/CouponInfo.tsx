import type { Coupon } from '@CouponDetails/services/coupon';
import { Box, BoxSubtitle, BoxTitle } from '@TutorShared/atoms/Box';
import Button from '@TutorShared/atoms/Button';
import FormInput from '@TutorShared/components/fields/FormInput';
import FormRadioGroup from '@TutorShared/components/fields/FormRadioGroup';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import { DateFormats } from '@TutorShared/config/constants';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { generateCouponCode } from '@TutorShared/utils/util';
import { maxLimitRule, requiredRule } from '@TutorShared/utils/validation';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { Controller, useFormContext } from 'react-hook-form';

const couponTypeOptions = [
  { label: __('Code', 'tutor'), value: 'code' },
  { label: __('Automatic', 'tutor'), value: 'automatic' },
];

function CouponInfo() {
  const params = new URLSearchParams(window.location.search);
  const courseId = params.get('coupon_id');
  const isEditMode = !!courseId;

  const form = useFormContext<Coupon>();
  const couponType = form.watch('coupon_type');

  function handleGenerateCouponCode() {
    const newCouponCode = generateCouponCode();
    form.setValue('coupon_code', newCouponCode, { shouldValidate: true });
  }

  const couponStatusOptions = [
    {
      label: __('Active', 'tutor'),
      value: 'active',
    },
    {
      label: __('Inactive', 'tutor'),
      value: 'inactive',
    },
    {
      label: __('Trash', 'tutor'),
      value: 'trash',
    },
  ];

  return (
    <Box bordered css={styles.discountWrapper}>
      <div css={styles.couponWrapper}>
        <BoxTitle>{__('Coupon Info', 'tutor')}</BoxTitle>
        <BoxSubtitle>{__('Create a coupon code or set up automatic discounts.', 'tutor')}</BoxSubtitle>
      </div>
      <Controller
        name="coupon_type"
        control={form.control}
        render={(controllerProps) => (
          <FormRadioGroup
            {...controllerProps}
            label={__('Method', 'tutor')}
            options={couponTypeOptions}
            wrapperCss={styles.radioWrapper}
            disabled={isEditMode}
          />
        )}
      />
      <Controller
        name="coupon_title"
        control={form.control}
        rules={requiredRule()}
        render={(controllerProps) => (
          <FormInput
            {...controllerProps}
            label={__('Title', 'tutor')}
            placeholder={
              /* translators: %s is the current year (e.g., 2025) */
              sprintf(__('e.g. Summer Sale %s', 'tutor'), format(new Date(), DateFormats.year))
            }
          />
        )}
      />

      {couponType === 'code' && (
        <div css={styles.couponCodeWrapper}>
          <Controller
            name="coupon_code"
            control={form.control}
            rules={{ ...requiredRule(), ...maxLimitRule(50) }}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                label={__('Coupon Code', 'tutor')}
                placeholder={__('e.g. SUMMER20', 'tutor')}
                disabled={isEditMode}
              />
            )}
          />
          {!isEditMode && (
            <Button
              data-cy="generate-code"
              variant="text"
              onClick={handleGenerateCouponCode}
              buttonCss={styles.generateCode}
            >
              {__('Generate Code', 'tutor')}
            </Button>
          )}
        </div>
      )}

      {isEditMode && (
        <Controller
          name="coupon_status"
          control={form.control}
          rules={requiredRule()}
          render={(controllerProps) => (
            <FormSelectInput {...controllerProps} label={__('Coupon status', 'tutor')} options={couponStatusOptions} />
          )}
        />
      )}
    </Box>
  );
}

export default CouponInfo;

const styles = {
  discountWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,
  couponWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
  `,
  couponCodeWrapper: css`
    position: relative;
  `,
  radioWrapper: css`
    display: flex;
    gap: ${spacing[40]};
  `,
  generateCode: css`
    ${styleUtils.resetButton};
    color: ${colorTokens.action.primary.default};
    position: absolute;
    right: ${spacing[0]};
    top: ${spacing[0]};

    &:hover,
    &:active,
    &:focus {
      color: ${colorTokens.action.primary.hover};
    }
  `,
};
