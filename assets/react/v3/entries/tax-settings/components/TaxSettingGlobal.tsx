import Show from '@/v3/shared/controls/Show';
import FormCheckbox from '@Components/fields/FormCheckbox';
import FormRadioGroup from '@Components/fields/FormRadioGroup';
import { colorPalate, fontSize, fontWeight, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';
import Card, { CardHeader } from '../molecules/Card';
import { TaxCollectionProcess, TaxSettings } from '../services/tax';

function TaxSettingGlobal() {
  const form = useFormContext<TaxSettings>();
  const { watch } = form;
  const isTaxIncludedInPrice = watch('isTaxIncludedInPrice');

  const taxCollectionProcessOptions = [
    {
      label: __('Tax is already included in my prices', 'tutor'),
      value: TaxCollectionProcess.isTaxIncludedInPrice,
    },
    {
      label: __('Tax should be calculated & display in the checkout page', 'tutor'),
      value: TaxCollectionProcess.taxIsNotIncluded,
    },
  ];

  return (
    <>
      <div>
        <Card>
          <CardHeader
            title={__('Global tax settings', 'tutor')}
            subtitle={__('Configure how tax is displayed and how it appears on your product listings.', 'tutor')}
          />
          <div css={styles.radioGroupWrapper}>
            <div>
              <Controller
                control={form.control}
                name="isTaxIncludedInPrice"
                render={(controllerProps) => {
                  return (
                    <FormRadioGroup
                      {...controllerProps}
                      label={__('How would you like to collect tax?', 'tutor')}
                      options={taxCollectionProcessOptions}
                      wrapperCss={styles.radioGroupWrapperCss}
                    />
                  );
                }}
              />
            </div>
            <div css={styles.checkboxWrapper}>
              <Show when={isTaxIncludedInPrice === TaxCollectionProcess.taxIsNotIncluded}>
                <Controller
                  control={form.control}
                  name="showPriceWithTax"
                  render={(controllerProps) => {
                    return (
                      <div>
                        <FormCheckbox
                          {...controllerProps}
                          label={__('Display prices inclusive tax', 'tutor')}
                          labelCss={styles.checkboxLabel}
                        />
                        <span css={styles.checkboxSubText}>
                          {__('Show prices with tax included, so customers see the final amount they’ll pay upfront', 'tutor')}
                        </span>
                      </div>
                    );
                  }}
                />
              </Show>
            </div>
          </div>
        </Card>
      </div>
    </>
  );
}

export default TaxSettingGlobal;
const styles = {
  radioGroupWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
    padding: ${spacing[10]} ${spacing[24]} ${spacing[20]};
  `,
  checkboxLabel: css`
    font-size: ${fontSize[14]};
  `,

  checkboxSubText: css`
    font-size: ${fontSize[14]};
    color: ${colorPalate.text.neutral};
    line-height: ${spacing[24]};
    font-weight: ${fontWeight.regular};
    padding-left: 28px;
  `,

  checkboxWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,
  radioGroupWrapperCss: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[10]};
    margin-top: ${spacing[8]};
  `,
};
