import type { Coupon } from '@CouponDetails/services/coupon';
import { Box, BoxSubtitle, BoxTitle } from '@TutorShared/atoms/Box';
import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import FormDateInput from '@TutorShared/components/fields/FormDateInput';
import FormTimeInput from '@TutorShared/components/fields/FormTimeInput';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { requiredRule } from '@TutorShared/utils/validation';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

function CouponValidity() {
  const form = useFormContext<Coupon>();

  const isEndEnabled = form.watch('is_end_enabled');
  const startDate = form.watch('start_date');
  const startTime = form.watch('start_time');
  const hasStartDateTime = !!startDate && !!startTime;

  return (
    <Box bordered css={styles.discountWrapper}>
      <div css={styles.couponWrapper}>
        <BoxTitle>{__('Validity', 'tutor')}</BoxTitle>
      </div>

      <Box css={[styleUtils.boxReset, styles.validityWrapper]}>
        <BoxSubtitle css={styles.dateTimeTitle}>{__('Starts from', 'tutor')}</BoxSubtitle>
        <div css={styles.dateTimeWrapper}>
          <Controller
            name="start_date"
            control={form.control}
            rules={requiredRule()}
            render={(controllerProps) => <FormDateInput {...controllerProps} placeholder="2030-10-24" />}
          />
          <Controller
            name="start_time"
            control={form.control}
            rules={requiredRule()}
            render={(controllerProps) => <FormTimeInput {...controllerProps} placeholder="12:30 PM" />}
          />
        </div>
        <Controller
          control={form.control}
          name="is_end_enabled"
          render={(controllerProps) => (
            <FormCheckbox
              {...controllerProps}
              label={__('Set end date', 'tutor')}
              description={__('Leaving the end date blank will make the coupon valid indefinitely.', 'tutor')}
              onChange={(value) => {
                if (!value) {
                  form.setValue('end_date', '');
                  form.setValue('end_time', '');
                }
              }}
              disabled={!hasStartDateTime}
              labelCss={styles.setEndDateLabel}
            />
          )}
        />
        <Show when={hasStartDateTime && isEndEnabled}>
          <>
            <BoxSubtitle css={styles.dateTimeTitle}>{__('Ends in', 'tutor')}</BoxSubtitle>
            <div css={styles.dateTimeWrapper}>
              <Controller
                name="end_date"
                control={form.control}
                rules={requiredRule()}
                render={(controllerProps) => (
                  <FormDateInput {...controllerProps} placeholder="2030-10-24" disabledBefore={startDate} />
                )}
              />
              <Controller
                name="end_time"
                control={form.control}
                rules={requiredRule()}
                render={(controllerProps) => <FormTimeInput {...controllerProps} placeholder="12:30 PM" />}
              />
            </div>
          </>
        </Show>
      </Box>
    </Box>
  );
}

export default CouponValidity;

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
  validityWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,
  dateTimeWrapper: css`
    display: flex;
    gap: ${spacing[12]};
    width: fit-content;
  `,
  dateTimeTitle: css`
    color: ${colorTokens.text.title};
  `,
  setEndDateLabel: css`
    ${typography.caption()};
    color: ${colorTokens.text.title};
  `,
};
