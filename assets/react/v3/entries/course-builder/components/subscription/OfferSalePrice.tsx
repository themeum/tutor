import FormCheckbox from '@Components/fields/FormCheckbox';
import FormDateInput from '@Components/fields/FormDateInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormSwitch from '@Components/fields/FormSwitch';
import FormTimeInput from '@Components/fields/FormTimeInput';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { Subscription } from '@CourseBuilderServices/subscription';
import { AnimatedDiv, AnimationType, useAnimation } from '@Hooks/useAnimation';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, type UseFormReturn } from 'react-hook-form';

export function OfferSalePrice({ form }: { form: UseFormReturn<Subscription> }) {
  const hasSale = form.watch('has_sale');
  const hasSchedule = form.watch('schedule_sale_price');
  const { transitions } = useAnimation({
    animationType: AnimationType.slideDown,
    data: hasSale,
  });

  return (
    <div css={saleStyles.wrapper}>
      <div>
        <Controller
          control={form.control}
          name="has_sale"
          render={(props) => <FormSwitch {...props} label="Offer sale price" />}
        />
      </div>
      {transitions((style, openState) => {
        if (openState) {
          return (
            <AnimatedDiv style={style} css={saleStyles.inputWrapper}>
              <Controller
                control={form.control}
                name="sale_price"
                render={(props) => <FormInputWithContent {...props} label="Sale price" content={'$'} />}
              />
              <Controller
                control={form.control}
                name="schedule_sale_price"
                render={(props) => <FormCheckbox {...props} label="Schedule the sale price" />}
              />
              <Show when={hasSchedule}>
                <div css={saleStyles.datetimeWrapper}>
                  <label>{__('Sale starts from', 'tutor')}</label>
                  <div css={styleUtils.dateAndTimeWrapper}>
                    <Controller
                      name="schedule_start_date"
                      control={form.control}
                      rules={{
                        required: __('Schedule date is required', 'tutor'),
                      }}
                      render={(controllerProps) => (
                        <FormDateInput {...controllerProps} isClearable={false} placeholder="yyyy-mm-dd" />
                      )}
                    />

                    <Controller
                      name="schedule_start_time"
                      control={form.control}
                      rules={{
                        required: __('Schedule time is required', 'tutor'),
                      }}
                      render={(controllerProps) => (
                        <FormTimeInput {...controllerProps} interval={60} isClearable={false} placeholder="hh:mm A" />
                      )}
                    />
                  </div>
                </div>
                <div css={saleStyles.datetimeWrapper}>
                  <label>{__('Sale ends to', 'tutor')}</label>
                  <div css={styleUtils.dateAndTimeWrapper}>
                    <Controller
                      name="schedule_end_date"
                      control={form.control}
                      rules={{
                        required: __('Schedule date is required', 'tutor'),
                      }}
                      render={(controllerProps) => (
                        <FormDateInput {...controllerProps} isClearable={false} placeholder="yyyy-mm-dd" />
                      )}
                    />

                    <Controller
                      name="schedule_end_time"
                      control={form.control}
                      rules={{
                        required: __('Schedule time is required', 'tutor'),
                      }}
                      render={(controllerProps) => (
                        <FormTimeInput {...controllerProps} interval={60} isClearable={false} placeholder="hh:mm A" />
                      )}
                    />
                  </div>
                </div>
              </Show>
            </AnimatedDiv>
          );
        }
      })}
    </div>
  );
}

const saleStyles = {
  wrapper: css`
		background-color: ${colorTokens.background.white};
		padding: ${spacing[12]};
		border: 1px solid ${colorTokens.stroke.default};
		border-radius: ${borderRadius[8]};
		display: flex;
		flex-direction: column;
		gap: ${spacing[20]};
	`,
  inputWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[12]};
	`,
  datetimeWrapper: css`
		label {
			${typography.caption()};
			color: ${colorTokens.text.title};
		}
	`,
};
