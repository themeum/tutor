import Show from '@/v3/shared/controls/Show';
import { getCountryByCode } from '@/v3/shared/utils/countries';
import Button from '@Atoms/Button';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import taxBanner from '@Images/tax-banner.png';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { FormProvider } from 'react-hook-form';
import Card from '../molecules/Card';
import EmptyState from '../molecules/EmptyState';
import { type TaxSettings, useTaxSettingsQuery } from '../services/tax';
import TaxRates from './TaxRates';
import { useCountrySelectModal } from './modals/CountrySelectModal';

const TaxSettingsPage = () => {
  const form = useFormWithGlobalError<TaxSettings>({
    defaultValues: {
      rates: [],
      apply_tax_on: 'product',
      active_country: null,
      show_price_with_tax: false,
      charge_tax_on_shipping: false,
      is_tax_included_in_price: 0,
    },
  });
  const { reset } = form;

  const taxSettingsQuery = useTaxSettingsQuery();

  const { openCountrySelectModal } = useCountrySelectModal();

  const ratesValue = taxSettingsQuery.data?.rates?.length ? taxSettingsQuery.data.rates : form.getValues('rates');

  const activeCountry = form.watch('active_country');
  const formData = form.watch();

  useEffect(() => {
    if (form.formState.isDirty) {
      document.getElementById('save_tutor_option')?.removeAttribute('disabled');
    }
  }, [form.formState.isDirty]);

  useEffect(() => {
    if (taxSettingsQuery.data) {
      const taxData = taxSettingsQuery.data;

      taxData.rates = taxData.rates.map((rate) => {
        if (rate.is_same_rate && !rate.states.length) {
          rate.states =
            getCountryByCode(rate.country)?.states?.map((state) => ({
              id: state.id,
              rate: 0,
              apply_on_shipping: false,
            })) || [];
        }
        return rate;
      });

      reset(taxSettingsQuery.data);
    }
  }, [reset, taxSettingsQuery.data]);

  if (taxSettingsQuery.isLoading) {
    return <LoadingSection />;
  }

  return (
    <div css={styles.wrapper} data-isdirty={form.formState.isDirty ? 'true' : undefined}>
      <Show when={activeCountry} fallback={<h6 css={typography.heading6('medium')}>{__('Tax', 'tutor')}</h6>}>
        {(countryCode) => {
          return (
            <Button
              onClick={() => {
                form.setValue('active_country', null);
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
            <div css={[styleUtils.cardInnerSection, styles.emptyStateWrapper]}>
              <EmptyState
                emptyStateImage={taxBanner}
                imageAltText={__('Tax Banner', 'tutor')}
                title={__('Apply Tax During Checkout', 'tutor')}
                content={__('Start configuring the tax settings to set up and manage the tax rates.', 'tutor')}
                buttonText={__('Add taxable country', 'tutor')}
                action={() => {
                  openCountrySelectModal({
                    form,
                    title: __('Add tax region', 'tutor'),
                  });
                }}
                orientation="vertical"
              />
            </div>
          </Card>
        }
      >
        <FormProvider {...form}>
          <TaxRates />
        </FormProvider>
      </Show>

      <input type="hidden" name="tutor_option[ecommerce_tax]" value={JSON.stringify(formData)} />
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
  emptyStateWrapper: css`
    margin-top: ${spacing[24]};
    margin-bottom: ${spacing[24]};
    
    img {
      margin-bottom: ${spacing[24]};
    }
  `,
};
