import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller, useFormContext } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import Checkbox from '@TutorShared/atoms/CheckBox';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';

import { colorTokens, fontSize, fontWeight, spacing } from '@TutorShared/config/styles';
import Show from '@TutorShared/controls/Show';
import Table, { type Column } from '@TutorShared/molecules/Table';
import { getCountryByCode, getStateByCode, isEuropeanUnion } from '@TutorShared/utils/countries';
import { styleUtils } from '@TutorShared/utils/style-utils';

import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import { typography } from '@TutorShared/config/typography';
import Card from '../molecules/Card';
import type { TaxSettings } from '../services/tax';
import EuropeanUnionTax from './EuropeanUnionTax';
import { MoreOptions } from './MoreOptions';
import TaxSettingGlobal from './TaxSettingGlobal';
import { useCountrySelectModal } from './modals/CountrySelectModal';
import { useCountryTaxRateModal } from './modals/CountryTaxRateModal';

export interface ColumnDataType {
  locationId: string | number;
  rate: number | null;
  emoji?: string;
}

export default function TaxRates() {
  const form = useFormContext<TaxSettings>();
  const { openCountrySelectModal } = useCountrySelectModal();
  const { openCountryTaxRateModal } = useCountryTaxRateModal();
  const rates = form.watch('rates');
  const activeCountry = form.watch('active_country');
  const activeCountryIndex = rates.findIndex((rate) => String(rate.country) === String(activeCountry));
  const activeCountrySelectedStates = rates[activeCountryIndex]?.states ?? [];
  const activeCountryAllStates = getCountryByCode(activeCountry ?? '')?.states ?? [];

  let tableData: ColumnDataType[] = activeCountry
    ? (rates
        .find((rate) => String(rate.country) === String(activeCountry))
        ?.states?.map((state) => ({
          locationId: getStateByCode(activeCountry, Number(state.id))?.id ?? '',
          rate: state?.rate,
        })) ?? [])
    : (rates?.map((countryObj) => ({
        locationId: getCountryByCode(countryObj.country)?.numeric_code ?? '',
        rate: countryObj?.rate,
        emoji: getCountryByCode(countryObj.country)?.emoji,
      })) ?? []);

  const isEU = isEuropeanUnion(activeCountry ?? '');

  const isSingleCountry = activeCountry && (!tableData.length || rates[activeCountryIndex].is_same_rate);

  if (isSingleCountry) {
    tableData = [{ locationId: activeCountry, rate: rates[activeCountryIndex].rate }];
  }

  useEffect(() => {
    if (
      isSingleCountry &&
      activeCountry &&
      activeCountryAllStates?.length &&
      !activeCountrySelectedStates.length &&
      !rates[activeCountryIndex].is_same_rate
    ) {
      form.setValue(`rates.${activeCountryIndex}.is_same_rate`, true);
      form.setValue(
        `rates.${activeCountryIndex}.states`,
        activeCountryAllStates.map((state) => ({ id: state.id, rate: 0, apply_on_shipping: false })),
      );
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isSingleCountry]);

  const columns: Column<ColumnDataType>[] = [
    {
      Header: isSingleCountry ? __('Region', 'tutor') : __('Countries', 'tutor'),
      Cell: (item) => {
        let name = activeCountry
          ? (getStateByCode(activeCountry, Number(item.locationId))?.name ?? '')
          : (getCountryByCode(`${item.locationId}`)?.name ?? '');

        if (isSingleCountry) {
          name = getCountryByCode(`${item.locationId}`)?.name ?? '';
        }

        return (
          <div css={styles.nameWrapper}>
            {item.emoji && <span css={styles.emoji}>{item.emoji}</span>}
            {item.emoji ? (
              <button
                type="button"
                css={styles.regionTitle}
                onClick={() => {
                  form.setValue('active_country', `${item.locationId}`);
                }}
              >
                {name}
              </button>
            ) : (
              <span>{name}</span>
            )}
          </div>
        );
      },
    },
    {
      Header: __('Tax rate', 'tutor'),
      Cell: (item) => {
        const countryObj = rates.find((rate) => String(rate.country) === String(item.locationId));
        const states = countryObj?.states || [];
        const stateRates = states.map((state) => state.rate);

        const displayStateFulCountryRate = () => {
          const minRate = Math.min(...stateRates);
          const maxRate = Math.max(...stateRates);
          return <span> {minRate === maxRate ? `${minRate}%` : `${minRate}-${maxRate}%`}</span>;
        };

        const displayCountryRate = () => {
          return !!item.rate || item.rate === 0 ? `${item.rate}%` : '0%';
        };

        if (activeCountry) {
          if (isSingleCountry) {
            return (
              <>
                <div css={[styles.rateWrapper, styles.col2]}>
                  <span css={styles.rateValue} data-rate-field="plain">
                    {displayCountryRate()}
                  </span>

                  <div css={styles.editableWrapper} data-rate-field="editable">
                    <Controller
                      key={item.locationId}
                      control={form.control}
                      name={`rates.${activeCountryIndex}.rate` as 'rates.0.rate'}
                      render={(controllerProps) => {
                        const handleChange = (value: string | number) => {
                          let stringValue = String(value);

                          if (stringValue.includes('.')) {
                            const [integer, decimal] = stringValue.split('.');
                            stringValue = `${Number.parseInt(integer, 10)}.${decimal}`;
                          } else {
                            stringValue = `${Number.parseInt(stringValue, 10)}`;
                          }

                          if (Number(stringValue) <= 100 || stringValue === '') {
                            controllerProps.field.onChange(stringValue);
                          } else {
                            controllerProps.field.onChange('');
                          }
                        };

                        return (
                          <FormInputWithContent
                            {...controllerProps}
                            onChange={handleChange}
                            type="number"
                            content={'%'}
                            contentCss={styleUtils.inputCurrencyStyle}
                            contentPosition="right"
                          />
                        );
                      }}
                    />
                    <Button
                      variant="text"
                      buttonCss={styles.deleteIcon}
                      icon={<SVGIcon height={24} width={24} name="delete" />}
                      onClick={() => {
                        const filteredRates = rates.filter((rate) => rate.country !== activeCountry);
                        form.setValue('rates', filteredRates);
                        form.setValue('active_country', null);
                      }}
                    />
                  </div>
                </div>
              </>
            );
          }

          const stateIndex = rates[activeCountryIndex].states.findIndex(
            (state) => String(state.id) === String(item.locationId),
          );
          if (stateIndex > -1) {
            return (
              <>
                <div css={[styles.rateWrapper, styles.col2]}>
                  <span css={styles.rateValue} data-rate-field="plain">
                    {displayCountryRate()}
                  </span>
                  <div css={styles.editableWrapper} data-rate-field="editable">
                    <Controller
                      key={item.locationId}
                      control={form.control}
                      name={`rates.${activeCountryIndex}.states.${stateIndex}.rate`}
                      render={(controllerProps) => {
                        const handleChange = (value: string | number) => {
                          let stringValue = String(value);

                          if (stringValue.includes('.')) {
                            const [integer, decimal] = stringValue.split('.');
                            stringValue = `${Number.parseInt(integer, 10)}.${decimal}`;
                          } else {
                            stringValue = `${Number.parseInt(stringValue, 10)}`;
                          }

                          if (Number(stringValue) <= 100 || stringValue === '') {
                            controllerProps.field.onChange(stringValue);
                          } else {
                            controllerProps.field.onChange('');
                          }
                        };
                        return (
                          <FormInputWithContent
                            {...controllerProps}
                            type="number"
                            onChange={handleChange}
                            content={'%'}
                            contentCss={styleUtils.inputCurrencyStyle}
                            contentPosition="right"
                          />
                        );
                      }}
                    />

                    <Button
                      variant="text"
                      buttonCss={styles.deleteIcon}
                      icon={<SVGIcon height={24} width={24} name="delete" />}
                      onClick={() => {
                        const updatedRates = rates.map((rate) => {
                          if (String(rate.country) === String(activeCountry)) {
                            rate.states = rate.states.filter((state) => state.id !== item.locationId);
                          }
                          return rate;
                        });

                        form.setValue('rates', updatedRates);
                      }}
                    />
                  </div>
                </div>
              </>
            );
          }
        }

        return (
          <div>
            {countryObj?.states.length && !countryObj.is_same_rate
              ? displayStateFulCountryRate()
              : displayCountryRate()}
          </div>
        );
      },
      width: activeCountry ? 180 : 100,
    },
  ];

  if (!activeCountry) {
    columns.push({
      Header: '',
      Cell: (item) => {
        return <MoreOptions data={item} />;
      },
      width: 32,
    });
  }

  function renderCountrySelectButton() {
    return (
      <Button
        variant="primary"
        size="small"
        buttonCss={styles.addRegionButton}
        onClick={() => {
          openCountrySelectModal({
            form,
            title: __('Add tax region', 'tutor'),
          });
        }}
      >
        {__('Add Region', 'tutor')}
      </Button>
    );
  }

  return isEU ? (
    <EuropeanUnionTax />
  ) : (
    <>
      <Card>
        <div css={styles.enableTaxWrapper}>
          <div css={styles.header}>
            <div css={typography.body('medium')}>{__('Tax Rates and Calculations', 'tutor')}</div>
          </div>
          <Controller
            control={form.control}
            name="enable_tax"
            render={(controllerProps) => (
              <FormCheckbox {...controllerProps} label={__('Enable tax rates and calculations', 'tutor')} />
            )}
          />
        </div>
      </Card>

      <Show when={form.watch('enable_tax')}>
        <Card>
          <div css={styleUtils.cardInnerSection}>
            <div css={styles.header}>
              <div css={typography.body('medium')}>{__('Tax Rates', 'tutor')}</div>
              <div css={styles.subtitle}>
                {
                  // prettier-ignore
                  __( "Set up tax rates for different regions. These rates will apply based on your customer's location.", 'tutor')
                }
              </div>
            </div>
            <Show when={activeCountry && activeCountryAllStates?.length}>
              <Checkbox
                label={__('Apply single tax rate for entire country', 'tutor')}
                checked={rates[activeCountryIndex]?.is_same_rate ?? false}
                onChange={(isChecked) => {
                  const currentCountry = rates[activeCountryIndex];
                  currentCountry.is_same_rate = isChecked;
                  form.setValue('rates', rates);
                }}
              />
            </Show>
            <Show when={tableData.length} fallback={<div>{renderCountrySelectButton()}</div>}>
              <Table
                columns={columns}
                data={tableData}
                isRounded={true}
                rowStyle={activeCountry ? styles.rowStyle : undefined}
                renderInLastRow={
                  !activeCountry ||
                  (activeCountry &&
                    !isSingleCountry &&
                    activeCountrySelectedStates.length !== activeCountryAllStates?.length) ? (
                    <Show
                      when={!activeCountry}
                      fallback={
                        <Show
                          when={
                            !isSingleCountry && activeCountrySelectedStates.length !== activeCountryAllStates?.length
                          }
                        >
                          <Button
                            variant="tertiary"
                            onClick={() => {
                              openCountryTaxRateModal({
                                form,
                                title: __('Add State & VAT Rate', 'tutor'),
                              });
                            }}
                          >
                            {__('Add State', 'tutor')}
                          </Button>
                        </Show>
                      }
                    >
                      {renderCountrySelectButton()}
                    </Show>
                  ) : undefined
                }
              />
            </Show>
          </div>
        </Card>

        <TaxSettingGlobal />
      </Show>
    </>
  );
}

const styles = {
  nameWrapper: css`
    display: flex;
    gap: ${spacing[8]};
    color: ${colorTokens.text.primary};
    font-weight: ${fontWeight.medium};
  `,
  header: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  addRegionButton: css`
    padding: ${spacing[8]} ${spacing[16]};
  `,
  subtitle: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
  `,
  emoji: css`
    font-size: ${fontSize[24]};
  `,
  regionTitle: css`
    ${styleUtils.resetButton};

    &:hover {
      text-decoration: underline;
    }
  `,
  deleteIcon: css`
    svg {
      color: ${colorTokens.icon.hints};
      transition: color 0.3s ease-in-out;
    }

    &:hover {
      svg {
        color: ${colorTokens.icon.error};
      }
    }
  `,
  editableWrapper: css`
    display: none;
    width: 100%;

    input {
      min-width: 60px;
    }
  `,
  rowStyle: css`
    &:hover {
      [data-rate-field='editable'] {
        display: flex;
        align-items: center;
        gap: ${spacing[8]};
      }
      [data-rate-field='edit'] {
        display: flex;
        align-items: center;
        width: 20px;
      }

      [data-rate-field='plain'] {
        display: none;
      }
    }
  `,
  rateValue: css`
    padding: ${spacing[6]} ${spacing[12]};
  `,

  col2: css`
    width: 120px;
  `,
  rateWrapper: css`
    display: flex;
    align-items: center;
    height: 36px;
  `,
  enableTaxWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
    padding: ${spacing[20]};
  `,
};
