import Button from '@Atoms/Button';
import { css } from '@emotion/react';
import { styleUtils } from '@Utils/style-utils';
import { useFormContext } from 'react-hook-form';
import TaxOverrideList from './TaxOverrideList';

function TaxOverride() {
  const { openTaxOverrideModal } = useTaxOverrideModal();
  const t = useTranslation();
  const form = useFormContext<TaxSettings>();

  const activeCountryCode = form.watch('activeCountry');
  const rates = form.watch('rates');

  const activeCountryIndex = rates.findIndex((rate) => rate.country == activeCountryCode);
  const activeCountryRate = rates[activeCountryIndex];
  const isEU = isEuropeanUnion(activeCountryCode ?? '');
  const isSingleCountry = activeCountryRate.isSameRate || (!isEU && !activeCountryRate.states.length);

  const showEmpty = (overrideType: OverrideOn) =>
    (isSingleCountry &&
      !activeCountryRate.overrideValues?.some((overrideValue) => overrideValue?.overrideOn === overrideType)) ||
    ((!isSingleCountry || isEU) &&
      !activeCountryRate.states.some((state) =>
        state?.overrideValues?.some((overrideValue) => overrideValue?.overrideOn === overrideType),
      ));

  return (
    <>
      <Card>
        <CardHeader
          title={t('COM_EASYSTORE_APP_TAX_SETTINGS_SHIPPING_TAX_OVERRIDES')}
          subtitle={t('COM_EASYSTORE_APP_TAX_SETTINGS_SHIPPING_TAX_OVERRIDES_DESC')}
          noSeparator={true}
        />
        <div css={[styleUtils.cardInnerSection, styles.innerSection]}>
          <Show when={showEmpty(OverrideOn.shipping)} fallback={<TaxOverrideList overrideType={OverrideOn.shipping} />}>
            <div>
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
          </Show>
        </div>
      </Card>
      <Card>
        <CardHeader
          title={t('COM_EASYSTORE_APP_TAX_SETTINGS_TAX_OVERRIDES')}
          subtitle={t('COM_EASYSTORE_APP_TAX_SETTINGS_TAX_OVERRIDES_DESC')}
          noSeparator={true}
        />
        <div css={[styleUtils.cardInnerSection, styles.innerSection]}>
          <Show when={showEmpty(OverrideOn.products)} fallback={<TaxOverrideList overrideType={OverrideOn.products} />}>
            <div>
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
          </Show>
        </div>
      </Card>
    </>
  );
}

export default TaxOverride;

const styles = {
  innerSection: css`
    padding-top: 0;
  `,
};
