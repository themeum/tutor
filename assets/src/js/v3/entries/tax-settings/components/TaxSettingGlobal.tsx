import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import FormRadioGroup from '@TutorShared/components/fields/FormRadioGroup';

import { borderRadius, colorTokens, fontSize, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';

import Card, { CardHeader } from '../molecules/Card';
import { TaxCollectionProcess, type TaxSettings } from '../services/tax';

function TaxSettingGlobal() {
  const form = useFormContext<TaxSettings>();

  const taxCollectionProcessOptions = [
    {
      label: __('Prices inclusive of tax', 'tutor'),
      value: TaxCollectionProcess.isTaxIncludedInPrice,
      description: __('Enter course prices inclusive of tax.', 'tutor'),
    },
    {
      label: __('Prices exclusive of tax', 'tutor'),
      value: TaxCollectionProcess.taxIsNotIncluded,
      // prettier-ignore
      description: __( 'Enter course prices without tax. Tax will be added at checkout based on your configured rates.', 'tutor'),
    },
  ];

  return (
    <>
      <div>
        <Card>
          <CardHeader
            title={__('Tax Settings', 'tutor')}
            subtitle={__('Choose how taxes are applied and shown on your product prices.', 'tutor')}
          />
          <div css={styles.inputGroupWrapper}>
            <div css={styles.wrapperWithLabel}>
              <label>{__('Tax Calculation Method', 'tutor')}</label>
              <Controller
                control={form.control}
                name="is_tax_included_in_price"
                render={(controllerProps) => {
                  return (
                    <FormRadioGroup
                      {...controllerProps}
                      options={taxCollectionProcessOptions}
                      wrapperCss={styles.radioGroupWrapperCss}
                    />
                  );
                }}
              />
            </div>
          </div>
          <div css={styles.inputGroupWrapper}>
            <div css={styles.wrapperWithLabel}>
              <label>{__('Advanced Settings', 'tutor')}</label>

              <div css={styles.radioGroupWrapperCss}>
                <Controller
                  control={form.control}
                  name="show_price_with_tax"
                  render={(controllerProps) => {
                    return (
                      <div>
                        <FormCheckbox
                          {...controllerProps}
                          label={__('Display tax-inclusive prices site-wide', 'tutor')}
                          labelCss={styles.checkboxLabel}
                          description={
                            // prettier-ignore
                            __("Show tax-inclusive pricing across course listings, detail pages, and at checkout", 'tutor')
                          }
                        />
                      </div>
                    );
                  }}
                />

                <Controller
                  control={form.control}
                  name="enable_individual_tax_control"
                  render={(controllerProps) => {
                    return (
                      <FormCheckbox
                        {...controllerProps}
                        label={__('Enable tax configuration per course & membership plan', 'tutor')}
                        labelCss={styles.checkboxLabel}
                        description={
                          // prettier-ignore
                          __('Allow tax settings when creating individual courses, bundles, and membership plans.', 'tutor')
                        }
                      />
                    );
                  }}
                />
              </div>
            </div>
          </div>
        </Card>
      </div>
    </>
  );
}

export default TaxSettingGlobal;
const styles = {
  inputGroupWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
    padding: ${spacing[10]} ${spacing[20]} ${spacing[24]} ${spacing[20]};
  `,
  checkboxLabel: css`
    font-size: ${fontSize[14]};
  `,
  radioGroupWrapperCss: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[10]};
    padding: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius.card};
  `,
  wrapperWithLabel: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};

    label {
      ${typography.caption('medium')};
    }
  `,
};
