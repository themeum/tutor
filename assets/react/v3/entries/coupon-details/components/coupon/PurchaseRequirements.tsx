import { Box, BoxSubtitle, BoxTitle } from '@Atoms/Box';
import FormInput from '@Components/fields/FormInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormRadioGroup from '@Components/fields/FormRadioGroup';
import { tutorConfig } from '@Config/config';
import { spacing } from '@Config/styles';
import Show from '@Controls/Show';
import type { Coupon } from '@CouponServices/coupon';
import { styleUtils } from '@Utils/style-utils';
import { requiredRule } from '@Utils/validation';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

function PurchaseRequirements() {
  const form = useFormContext<Coupon>();
  const { tutor_currency } = tutorConfig;
  const purchaseAmountLabel = sprintf(__('Minimum purchase amount (%s)', 'tutor'), tutor_currency?.symbol ?? '$');

  const requirementOptions = [
    {
      label: __('No minimum requirements', 'tutor'),
      value: 'no_minimum',
    },
    {
      label: purchaseAmountLabel,
      value: 'minimum_purchase',
    },
    {
      label: __('Minimum quantity of items', 'tutor'),
      value: 'minimum_quantity',
    },
  ];

  return (
    <Box bordered css={styles.discountWrapper}>
      <div css={styles.couponWrapper}>
        <BoxTitle>{__('Minimum purchase requirements', 'tutor')}</BoxTitle>
        <BoxSubtitle>{__('Set the minimum purchase amount/quantity for this coupon to be valid', 'tutor')}</BoxSubtitle>
      </div>
      <Controller
        name="purchase_requirement"
        control={form.control}
        render={(controllerProps) => (
          <FormRadioGroup
            {...controllerProps}
            options={requirementOptions}
            wrapperCss={styles.radioGroupWrapper}
            onSelectRender={(selectedOption) => {
              return (
                <Show when={selectedOption.value === 'minimum_purchase' || selectedOption.value === 'minimum_quantity'}>
                  <div css={styles.requirementInput}>
                    <Show when={selectedOption.value === 'minimum_purchase'}>
                      <Controller
                        name="purchase_requirement_value"
                        control={form.control}
                        rules={requiredRule()}
                        render={(controllerProps) => (
                          <FormInputWithContent
                            {...controllerProps}
                            type="number"
                            placeholder={__('0.00', 'tutor')}
                            content={tutor_currency?.symbol ?? '$'}
                            contentCss={styleUtils.inputCurrencyStyle}
                          />
                        )}
                      />
                    </Show>
                    <Show when={selectedOption.value === 'minimum_quantity'}>
                      <Controller
                        name="purchase_requirement_value"
                        control={form.control}
                        rules={requiredRule()}
                        render={(controllerProps) => (
                          <FormInput {...controllerProps} type="number" placeholder={__('0', 'tutor')} />
                        )}
                      />
                    </Show>
                  </div>
                </Show>
              );
            }}
          />
        )}
      />
    </Box>
  );
}

export default PurchaseRequirements;

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
  requirementInput: css`
		width: 30%;
		margin-left: ${spacing[28]};
		margin-top: ${spacing[8]};
	`,
  radioGroupWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[8]};
	`,
};
