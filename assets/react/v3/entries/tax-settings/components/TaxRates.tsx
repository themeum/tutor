import Show from '@/v3/shared/controls/Show';
import { getCountryByCode, getStateByCode, isEuropeanUnion } from '@/v3/shared/utils/countries';
import Button from '@Atoms/Button';
import Checkbox from '@Atoms/CheckBox';
import SVGIcon from '@Atoms/SVGIcon';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import { colorPalate, fontSize, fontWeight, spacing } from '@Config/styles';
import Table, { type Column } from '@Molecules/Table';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import Card, { CardHeader } from '../molecules/Card';
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
  const activeCountry = form.watch('activeCountry');
  const activeCountryIndex = rates.findIndex((rate) => rate.country === activeCountry);
  const activeCountrySelectedStates = rates[activeCountryIndex]?.states ?? [];
  const activeCountryAllStates = getCountryByCode(activeCountry ?? '')?.states ?? [];

  console.log({ activeCountry });

  let tableData: ColumnDataType[] = activeCountry
    ? rates
        .find((rate) => rate.country === activeCountry)
        ?.states?.map((state) => ({
          locationId: getStateByCode(activeCountry, Number(state.id))?.id ?? '',
          rate: state?.rate,
        })) ?? []
    : rates?.map((countryObj) => ({
        locationId: getCountryByCode(countryObj.country)?.numeric_code ?? '',
        rate: countryObj?.rate,
        emoji: getCountryByCode(countryObj.country)?.emoji,
      })) ?? [];

  const isEU = isEuropeanUnion(activeCountry ?? '');

  const isSingleCountry = activeCountry && (!tableData.length || rates[activeCountryIndex].isSameRate);

  if (isSingleCountry) {
    tableData = [{ locationId: activeCountry, rate: rates[activeCountryIndex].rate }];
  }

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (
      isSingleCountry &&
      activeCountry &&
      activeCountryAllStates?.length &&
      !activeCountrySelectedStates.length &&
      !rates[activeCountryIndex].isSameRate
    ) {
      form.setValue(`rates.${activeCountryIndex}.isSameRate`, true);
      form.setValue(
        `rates.${activeCountryIndex}.states`,
        activeCountryAllStates.map((state) => ({ id: state.id, rate: 0, applyOnShipping: false })),
      );
    }
  }, [isSingleCountry]);

  const columns: Column<ColumnDataType>[] = [
    {
      Header: isSingleCountry ? __('Region', 'tutor') : __('States', 'tutor'),
      Cell: (item) => {
        let name = activeCountry
          ? getStateByCode(activeCountry, Number(item.locationId))?.name ?? ''
          : getCountryByCode(`${item.locationId}`)?.name ?? '';

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
                  form.setValue('activeCountry', `${item.locationId}`);
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
        const countryObj = rates.find((rate) => rate.country === item.locationId);
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
                        return <FormInputWithContent {...controllerProps} content={'%'} contentPosition="right" />;
                      }}
                    />
                    <Button
                      variant="text"
                      icon={<SVGIcon name="delete" style={styles.deleteIcon} />}
                      onClick={() => {
                        const filteredRates = rates.filter((rate) => rate.country !== activeCountry);
                        form.setValue('rates', filteredRates);
                        form.setValue('activeCountry', null);
                      }}
                    />
                  </div>
                </div>
              </>
            );
          }

          const stateIndex = rates[activeCountryIndex].states.findIndex((state) => state.id === item.locationId);
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
                        return <FormInputWithContent {...controllerProps} content={'%'} contentPosition="right" />;
                      }}
                    />

                    <Button
                      variant="text"
                      icon={<SVGIcon name="delete" style={styles.deleteIcon} />}
                      onClick={() => {
                        const updatedRates = rates.map((rate) => {
                          if (rate.country === activeCountry) {
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
            {countryObj?.states.length && !countryObj.isSameRate ? displayStateFulCountryRate() : displayCountryRate()}
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
        variant="secondary"
        onClick={() => {
          openCountrySelectModal({
            form,
            title: __('Add tax region', 'tutor'),
          });
        }}
      >
        {__('Add region', 'tutor')}
      </Button>
    );
  }

  return isEU ? (
    <EuropeanUnionTax />
  ) : (
    <>
      <Card>
        <CardHeader
          title={activeCountry ? __('Regions & tax rates', 'tutor') : __('Regional tax rates', 'tutor')}
          subtitle={
            activeCountry
              ? __('Add region you want to collect tax & their tax rates', 'tutor')
              : __('Add the destinations in this region', 'tutor')
          }
        />
        <div css={styleUtils.cardInnerSection}>
          <Show when={activeCountry && activeCountryAllStates?.length}>
            <Checkbox
              label={__('Apply single tax rate for entire country', 'tutor')}
              checked={rates[activeCountryIndex]?.isSameRate ?? false}
              onChange={(isChecked) => {
                const currentCountry = rates[activeCountryIndex];
                currentCountry.isSameRate = isChecked;
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
                        when={!isSingleCountry && activeCountrySelectedStates.length !== activeCountryAllStates?.length}
                      >
                        <Button
                          variant="tertiary"
                          onClick={() => {
                            openCountryTaxRateModal({
                              form,
                              title: __('Add state & VAT rate', 'tutor'),
                            });
                          }}
                        >
                          {__('Add state', 'tutor')}
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
      {/* <Show when={activeCountrySelectedStates.length || isSingleCountry} fallback={<TaxSettingGlobal />}>
        <TaxOverride />
      </Show> */}
    </>
  );
}

const styles = {
  nameWrapper: css`
    display: flex;
    gap: ${spacing[8]};
    color: ${colorPalate.text.default};
    font-weight: ${fontWeight.medium};
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
    color: ${colorPalate.icon.default};
  `,
  editableWrapper: css`
    display: none;
		width: 100%;
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
};
