import Button, { ButtonVariant } from '@Atoms/Button';
import Show from '@Atoms/controls/Show';
import SVGIcon from '@Atoms/SVGIcon';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import { useTaxOverrideModal } from '@Components/modals/TaxOverrideModal';
import { borderRadius, colorPalate, fontSize, fontWeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { useTranslation } from '@Hooks/useTranslation';
import { CountryOverrideType, OverrideOn, TaxSettings } from '@Services/app';
import { useCategoryOptionsQuery } from '@Services/category';
import { europeanUnionData, getCountryByCode, getStateByCode, isEuropeanUnion } from '@Utils/countries';
import { Fragment } from 'react';

import { Controller, useFormContext } from 'react-hook-form';

interface OverrideItem {
  location: string | undefined;
  rate: number;
  type: CountryOverrideType | undefined;
  category: string | undefined;
  name: string | undefined;
}

type TaxOverrideListProps = {
  overrideType: OverrideOn;
};

function TaxOverrideList({ overrideType = OverrideOn.shipping }: TaxOverrideListProps) {
  const form = useFormContext<TaxSettings>();
  const { openTaxOverrideModal } = useTaxOverrideModal();
  const categoryOptionsQuery = useCategoryOptionsQuery();
  const categoryOptions = categoryOptionsQuery.data ?? [];

  const t = useTranslation();

  const activeCountryCode = form.watch('activeCountry');
  const rates = form.watch('rates');

  const activeCountryIndex = rates.findIndex((rate) => rate.country == activeCountryCode);
  const activeCountryRate = rates[activeCountryIndex];
  const isEU = isEuropeanUnion(activeCountryCode ?? '');
  const isSingleCountry = activeCountryRate.isSameRate || (!isEU && !activeCountryRate.states.length);

  function getOverrideList(overrideOn: OverrideOn) {
    if (isSingleCountry) {
      return activeCountryRate.overrideValues
        ?.filter((item) => item.overrideOn === overrideOn)
        ?.map((item) => ({
          location: activeCountryRate.country,
          rate: item.rate,
          type: item.type,
          category: item.category,
          name: getCountryByCode(activeCountryCode ?? '')?.name ?? '',
        }));
    } else {
      return activeCountryRate.states
        .filter((state) => state.overrideValues?.length)
        .flatMap((state) => state.overrideValues)
        .filter((state) => state?.overrideOn === overrideOn)
        .map((state) => ({
          location: state?.location,
          rate: state?.rate ?? 0,
          type: state?.type,
          category: state?.category,
          name: isEU
            ? europeanUnionData.states.find((euState) => euState.numeric_code == state?.location)?.name
            : getStateByCode(activeCountryCode ?? '', Number(state?.location))?.name,
        }));
    }
  }

  const locationOverrides = getOverrideList(OverrideOn.shipping);
  const categoryOverrides = getOverrideList(OverrideOn.products);

  const categoryLocations = categoryOverrides?.reduce((acc: Record<string, OverrideItem[]>, curr) => {
    if (curr.category) {
      if (!acc[curr.category]) {
        acc[curr.category] = [];
      }
      acc[curr.category].push(curr);
    }
    return acc;
  }, {});

  function getIndex<T>(overrideValues: T[], fn: (arg: T) => boolean) {
    return overrideValues.findIndex((overrideValue) => fn(overrideValue));
  }

  return (
    <div css={styles.wrapper}>
      <Show when={overrideType === OverrideOn.shipping && locationOverrides?.length}>
        <div css={styles.tableWrapper}>
          <div css={styles.tableHeader}>
            <div css={styles.col1}>
              {isSingleCountry || isEU
                ? t('COM_EASYSTORE_APP_TAX_SETTINGS_REGION')
                : t('COM_EASYSTORE_APP_TAX_SETTINGS_STATE')}
            </div>
            <div css={styles.col2}>{t('COM_EASYSTORE_APP_TAX_SETTINGS_TAX_RATE')}</div>
          </div>
          {locationOverrides?.map((locationOverride, index) => {
            const singleOverrideIndex = getIndex(rates[activeCountryIndex].overrideValues ?? [], (overrideValue) => {
              return (
                overrideValue.overrideOn === OverrideOn.shipping && overrideValue.location == locationOverride.location
              );
            });

            const stateIndex = getIndex(rates[activeCountryIndex].states ?? [], (state) => {
              return (
                state?.overrideValues?.some(
                  (overrideValue) =>
                    overrideValue.overrideOn === OverrideOn.shipping &&
                    overrideValue.location == locationOverride.location,
                ) ?? false
              );
            });

            const statefulOverrideIndex = getIndex(
              rates[activeCountryIndex]?.states[stateIndex]?.overrideValues ?? [],
              (overrideValue) => {
                return (
                  overrideValue.overrideOn === OverrideOn.shipping &&
                  overrideValue.location == locationOverride.location
                );
              },
            );

            return (
              <div css={styles.tableRow} key={index}>
                <div css={styles.col1}>
                  <SVGIcon name="tax-location" width={20} height={20} />
                  <span css={styles.locationName}> {locationOverride?.name}</span>
                </div>

                <div css={[styles.rateWrapper, styles.col2]}>
                  <div css={styles.rateValue} data-rate-field="plain">
                    {`${locationOverride?.rate}${t('COM_EASYSTORE_APP_TAX_SETTINGS_TAX_PERCENTAGE')}`}
                  </div>
                  <div css={styles.editableWrapper} data-rate-field="editable">
                    {isSingleCountry ? (
                      <Controller
                        control={form.control}
                        key={`rates.${activeCountryIndex}.overrideValues.${singleOverrideIndex}.rate`}
                        name={`rates.${activeCountryIndex}.overrideValues.${singleOverrideIndex}.rate`}
                        render={(controllerProps) => (
                          <FormInputWithContent
                            {...controllerProps}
                            content={t('COM_EASYSTORE_APP_TAX_SETTINGS_TAX_PERCENTAGE')}
                            contentPosition="right"
                          />
                        )}
                      />
                    ) : (
                      <Controller
                        control={form.control}
                        key={`rates.${activeCountryIndex}.states.${stateIndex}.overrideValues.${statefulOverrideIndex}.rate`}
                        name={`rates.${activeCountryIndex}.states.${stateIndex}.overrideValues.${statefulOverrideIndex}.rate`}
                        render={(controllerProps) => (
                          <FormInputWithContent
                            {...controllerProps}
                            content={t('COM_EASYSTORE_APP_TAX_SETTINGS_TAX_PERCENTAGE')}
                            contentPosition="right"
                          />
                        )}
                      />
                    )}
                    <Button
                      buttonCss={styles.trashButton}
                      variant={ButtonVariant.plain}
                      icon={<SVGIcon name="delete" />}
                      onClick={() => {
                        if (isSingleCountry) {
                          const overrideIndex = getIndex(
                            activeCountryRate?.overrideValues ?? [],
                            (overrideValue) =>
                              overrideValue.overrideOn === OverrideOn.shipping &&
                              overrideValue.location == locationOverride.location,
                          );

                          const updatedActiveCountryRate = {
                            ...activeCountryRate,
                            overrideValues: activeCountryRate.overrideValues?.filter((_, idx) => idx !== overrideIndex),
                          };

                          rates[activeCountryIndex] = updatedActiveCountryRate;
                          form.setValue('rates', rates);
                        } else {
                          const updatedStates = activeCountryRate.states.map((state) => {
                            if (state?.overrideValues?.length) {
                              const overrideIndex = getIndex(
                                state?.overrideValues ?? [],
                                (overrideValue) =>
                                  overrideValue.overrideOn === OverrideOn.shipping &&
                                  overrideValue.location == locationOverride.location,
                              );

                              state.overrideValues = state.overrideValues?.filter((_, idx) => idx !== overrideIndex);
                            }
                            return state;
                          });

                          const updatedActiveCountryRate = {
                            ...activeCountryRate,
                            states: updatedStates,
                          };

                          rates[activeCountryIndex] = updatedActiveCountryRate;
                          form.setValue('rates', rates);
                        }
                      }}
                    />
                  </div>
                </div>
              </div>
            );
          })}
          <div css={[styles.tableRow, styles.rowNoHover]}>
            <Button
              variant={ButtonVariant.primaryLight}
              onClick={() => {
                openTaxOverrideModal({
                  form,
                  title: t('COM_EASYSTORE_APP_TAX_SETTINGS_ADD_SHIPPING_TAX_OVERRIDE'),
                  overrideType: OverrideOn.shipping,
                });
              }}
            >
              {t('COM_EASYSTORE_APP_TAX_SETTINGS_SHIPPING_ADD_OVERRIDE')}
            </Button>
          </div>
        </div>
      </Show>
      <Show when={overrideType === OverrideOn.products && categoryOverrides?.length}>
        <div css={styles.tableWrapper}>
          {Object.entries(categoryLocations ?? {})?.map(([category, values], idx) => {
            const categoryTitle = categoryOptions.find((categoryOption) => categoryOption.value == category)?.label;

            return (
              <Fragment key={idx}>
                <div css={styles.tableHeader}>
                  <div css={[styles.col1, styles.categoryTitle]}>{categoryTitle}</div>
                  {idx === 0 && <div css={styles.col2}>{t('COM_EASYSTORE_APP_TAX_SETTINGS_TAX_RATE')}</div>}
                </div>
                {values?.map((value, index) => {
                  const singleOverrideIndex = getIndex(
                    rates[activeCountryIndex].overrideValues ?? [],
                    (overrideValue) => {
                      return overrideValue.category == category && overrideValue.location == value.location;
                    },
                  );

                  const stateIndex = getIndex(rates[activeCountryIndex].states ?? [], (state) => {
                    return (
                      state?.overrideValues?.some(
                        (overrideValue) =>
                          overrideValue.category == category && overrideValue.location == value.location,
                      ) ?? false
                    );
                  });

                  const statefulOverrideIndex = getIndex(
                    rates[activeCountryIndex]?.states[stateIndex]?.overrideValues ?? [],
                    (overrideValue) => {
                      return overrideValue.category == category && overrideValue.location == value.location;
                    },
                  );

                  return (
                    <div css={styles.tableRow} key={index}>
                      <div css={styles.col1}>
                        <SVGIcon name="tax-location" width={20} height={20} />
                        <span css={styles.locationName}> {value.name}</span>
                      </div>
                      <div css={[styles.rateWrapper, styles.col2]}>
                        <div css={styles.rateValue} data-rate-field="plain">
                          {`${value.rate}${t('COM_EASYSTORE_APP_TAX_SETTINGS_TAX_PERCENTAGE')}`}
                        </div>
                        <div css={styles.editableWrapper} data-rate-field="editable">
                          {isSingleCountry ? (
                            <Controller
                              control={form.control}
                              key={`rates.${activeCountryIndex}.overrideValues.${singleOverrideIndex}.rate`}
                              name={`rates.${activeCountryIndex}.overrideValues.${singleOverrideIndex}.rate`}
                              render={(controllerProps) => (
                                <FormInputWithContent
                                  {...controllerProps}
                                  content={t('COM_EASYSTORE_APP_TAX_SETTINGS_TAX_PERCENTAGE')}
                                  contentPosition="right"
                                />
                              )}
                            />
                          ) : (
                            <Controller
                              control={form.control}
                              key={`rates.${activeCountryIndex}.states.${stateIndex}.overrideValues.${statefulOverrideIndex}.rate`}
                              name={`rates.${activeCountryIndex}.states.${stateIndex}.overrideValues.${statefulOverrideIndex}.rate`}
                              render={(controllerProps) => (
                                <FormInputWithContent
                                  {...controllerProps}
                                  content={t('COM_EASYSTORE_APP_TAX_SETTINGS_TAX_PERCENTAGE')}
                                  contentPosition="right"
                                />
                              )}
                            />
                          )}

                          <Button
                            buttonCss={styles.trashButton}
                            variant={ButtonVariant.plain}
                            icon={<SVGIcon name="delete" />}
                            onClick={() => {
                              if (isSingleCountry) {
                                const overrideIndex = getIndex(
                                  activeCountryRate?.overrideValues ?? [],
                                  (overrideValue) =>
                                    overrideValue.category == category && overrideValue.location == value.location,
                                );

                                const updatedActiveCountryRate = {
                                  ...activeCountryRate,
                                  overrideValues: activeCountryRate.overrideValues?.filter(
                                    (_, idx) => idx !== overrideIndex,
                                  ),
                                };

                                rates[activeCountryIndex] = updatedActiveCountryRate;
                                form.setValue('rates', rates);
                              } else {
                                const updatedStates = activeCountryRate.states.map((state) => {
                                  if (state?.overrideValues?.length) {
                                    const overrideIndex = getIndex(
                                      state?.overrideValues ?? [],
                                      (overrideValue) =>
                                        overrideValue.category == category && overrideValue.location == value.location,
                                    );

                                    state.overrideValues = state.overrideValues?.filter(
                                      (_, indx) => indx !== overrideIndex,
                                    );
                                  }
                                  return state;
                                });

                                const updatedActiveCountryRate = {
                                  ...activeCountryRate,
                                  states: updatedStates,
                                };

                                rates[activeCountryIndex] = updatedActiveCountryRate;
                                form.setValue('rates', rates);
                              }
                            }}
                          />
                        </div>
                      </div>
                    </div>
                  );
                })}
              </Fragment>
            );
          })}
          <div css={[styles.tableRow, styles.rowNoHover]}>
            <Button
              variant={ButtonVariant.primaryLight}
              onClick={() => {
                openTaxOverrideModal({
                  form,
                  title: t('COM_EASYSTORE_APP_TAX_SETTINGS_ADD_TAX_OVERRIDE'),
                  overrideType: OverrideOn.products,
                });
              }}
            >
              {t('COM_EASYSTORE_APP_TAX_SETTINGS_ADD_OVERRIDE')}
            </Button>
          </div>
        </div>
      </Show>
    </div>
  );
}

export default TaxOverrideList;

const styles = {
  wrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
  tableWrapper: css`
    display: flex;
    flex-direction: column;
    border: 1px solid ${colorPalate.surface.neutral.hover};
    border-radius: ${borderRadius[6]};
  `,
  tableHeader: css`
    ${typography.body()};
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top-right-radius: ${borderRadius[6]};
    border-top-left-radius: ${borderRadius[6]};
    background-color: ${colorPalate.surface.neutral.default};
    padding: ${spacing[12]} ${spacing[16]};
    color: ${colorPalate.text.neutral};
    border-bottom: 1px solid ${colorPalate.border.neutral};
  `,
  tableRow: css`
    ${typography.body()};
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: ${spacing[16]};
    border-bottom: 1px solid ${colorPalate.border.neutral};
    border-top: none;

    &:hover {
      background-color: ${colorPalate.surface.hover};

      [data-rate-field='plain'] {
        display: none;
      }

      [data-rate-field='editable'] {
        display: flex;
        align-items: center;
        gap: ${spacing[8]};
      }
    }

    &:last-child {
      border-bottom: none;
    }
  `,
  rowNoHover: css`
    &:hover {
      background-color: unset;
    }
  `,
  rateWrapper: css`
    display: flex;
    align-items: center;
    height: 36px;
  `,
  col1: css`
    width: 370px;
    display: flex;
    flex-direction: row;
    gap: ${spacing[8]};
    align-items: center;
  `,

  col2: css`
    width: 120px;
  `,
  rateValue: css`
    padding: ${spacing[6]} ${spacing[12]};
  `,
  editableWrapper: css`
    display: none;
  `,
  trashButton: css`
    margin-left: auto;
    color: ${colorPalate.icon.default};
  `,
  categoryTitle: css`
    color: ${colorPalate.text.primary};
  `,
  locationName: css`
    color: ${colorPalate.text.default};
    font-weight: ${fontWeight.medium};
    font-size: ${fontSize[15]};
  `,
};
