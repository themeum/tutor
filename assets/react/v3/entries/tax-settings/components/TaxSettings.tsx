import Show from '@/v3/shared/controls/Show';
import { getCountryByCode } from '@/v3/shared/utils/countries';
import Button from '@Atoms/Button';
import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { FormProvider } from 'react-hook-form';
import Card from '../molecules/Card';
import { type TaxSettings, useTaxSettingsQuery } from '../services/tax';
import TaxRates from './TaxRates';
import { useCountrySelectModal } from './modals/CountrySelectModal';

const TaxSettingsPage = () => {
  const form = useFormWithGlobalError<TaxSettings>({
    defaultValues: {
      rates: [],
      applyTaxOn: 'product',
      activeCountry: null,
      showPriceWithTax: false,
      chargeTaxOnShipping: false,
      isTaxIncludedInPrice: 0,
    },
  });
  const { reset } = form;

  const taxSettingsQuery = useTaxSettingsQuery();

  const { openCountrySelectModal } = useCountrySelectModal();

  const ratesValue = taxSettingsQuery.data?.rates?.length ? taxSettingsQuery.data?.rates : form.getValues('rates');

  const activeCountry = form.watch('activeCountry');

  useEffect(() => {
    if (taxSettingsQuery.data) {
      const taxData = taxSettingsQuery.data;

      taxData.rates = taxData.rates.map((rate) => {
        if (rate.isSameRate && !rate.states.length) {
          rate.states =
            getCountryByCode(rate.country)?.states?.map((state) => ({
              id: state.id,
              rate: 0,
              applyOnShipping: false,
            })) || [];
        }
        return rate;
      });

      reset(taxSettingsQuery.data);
    }
  }, [reset, taxSettingsQuery.data]);

  if (taxSettingsQuery.isLoading) {
    return <LoadingOverlay />;
  }

  if (!taxSettingsQuery.data) {
    return <div>{__('Something went wrong', 'tutor')}</div>;
  }

  return (
    <div css={styles.wrapper} data-isdirty={form.formState.isDirty ? 'true' : undefined}>
      <Show when={activeCountry} fallback={<h6 css={typography.heading6('medium')}>{__('Tax settings', 'tutor')}</h6>}>
        {(countryCode) => {
          return (
            <Button
              onClick={() => {
                form.setValue('activeCountry', null);
              }}
              buttonCss={styles.backButton}
              variant="text"
              icon={<SVGIcon name="arrowLeft" height={24} width={24} />}
              size="small"
            >
              {getCountryByCode(countryCode)?.name}
            </Button>
          );
        }}
      </Show>
      <Show
        when={ratesValue.length}
        fallback={
          <Card>
            <div css={styleUtils.cardInnerSection}>
              {/* <EmptyState
                emptyStateImage={TaxBanner}
                imageAltText={t('COM_EASYSTORE_APP_TAX_SETTINGS_EMPTY_STATE_IMAGE_ALT')}
                title={t('COM_EASYSTORE_APP_TAX_SETTINGS_EMPTY_STATE_TITLE')}
                content={<Trans transKey="COM_EASYSTORE_APP_TAX_SETTINGS_EMPTY_STATE_CONTENT" />}
                buttonText={t('COM_EASYSTORE_APP_TAX_SETTINGS_EMPTY_STATE_BUTTON_TEXT')}
                action={() => {
                  openCountrySelectModal({
                    form,
                    title: t('COM_EASYSTORE_APP_TAX_SETTINGS_ADD_TAX_REGION'),
                  });
                }}
                orientation="vertical"
              /> */}
            </div>
          </Card>
        }
      >
        <FormProvider {...form}>
          <TaxRates />
        </FormProvider>
        <div css={styles.saveButtonContainer}>
          <Button
            onClick={async () => {
              try {
                form.setValue('activeCountry', null);
                // const updatedTaxData = {
                //   ...form.getValues(),
                //   rates: form.getValues('rates').map((rate) => {
                //     let copyRate = { ...rate };

                //     if (copyRate.isSameRate) {
                //       copyRate.states = [];
                //     } else {
                //       if (copyRate.overrideValues && copyRate.states.length) {
                //         delete copyRate.overrideValues;
                //       }
                //     }
                //     return copyRate;
                //   }),
                // };

                // @TODO: need to make the save functionalities
                // await taxSettingsQuery.mutateAsync({
                //   property: 'tax',
                //   data: JSON.stringify(updatedTaxData),
                // });
              } catch (error) {
                console.error(error);
              }
            }}
            loading={taxSettingsQuery.isLoading}
          >
            {__('Save changes', 'tutor')}
          </Button>
        </div>
      </Show>
    </div>
  );
};

export default TaxSettingsPage;

const styles = {
  wrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
  `,
  saveButtonContainer: css`
    display: flex;
    justify-content: flex-end;
  `,
  backButton: css`
    ${typography.heading5('medium')};
    text-decoration: none;
    color: ${colorTokens.text.title};
		width: 100%;
    display: flex;
    align-items: center;
    justify-content: start;

		svg {
			color: ${colorTokens.text.title};
		}

    &:hover {
      text-decoration: none;
      color: ${colorTokens.text.title};
    }
  `,
};
