import { ModalProps } from '@/v3/shared/components/modals/Modal';
import Button from '@Atoms/Button';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormSelectInput from '@Components/fields/FormSelectInput';
import { colorPalate, shadow, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { europeanUnionData, getCountryByCode, getStateByCode, isEuropeanUnion } from '@Utils/countries';
import { requiredRule } from '@Utils/validation';
import { useEffect } from 'react';
import { Controller, UseFormReturn } from 'react-hook-form';
import { OverrideOn, TaxSettings } from '../../services/tax';

interface TaxOverrideModalProps extends ModalProps {
  form: UseFormReturn<TaxSettings>;
  overrideType: OverrideOn;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

interface TaxOverrideForm {
  overrideOn: OverrideOn;
  rate: number;
  location: string;
  category?: string;
}

const TaxOverrideModal = ({ form, closeModal, title, overrideType = OverrideOn.shipping }: TaxOverrideModalProps) => {
  const taxOverrideForm = useFormWithGlobalError<TaxOverrideForm>({
    defaultValues: {
      overrideOn: overrideType,
      rate: 0,
      location: '',
    },
  });
  const categoryOptionsQuery = useCategoryOptionsQuery();
  const categoryOptions = categoryOptionsQuery.data ?? [];

  const overrideOn = taxOverrideForm.watch('overrideOn');
  const selectedCategory = taxOverrideForm.watch('category');
  const activeCountryCode = form.watch('activeCountry');
  const rates = form.watch('rates');
  const activeCountryIndex = rates.findIndex((rate) => rate.country == activeCountryCode);
  const activeCountryRate = rates[activeCountryIndex];

  const isEU = isEuropeanUnion(activeCountryCode ?? '');
  const isSingleCountry = activeCountryRate.isSameRate || (!isEU && !activeCountryRate.states.length);
  let selectionType: CountryOverrideType = isSingleCountry ? 'region' : 'state';

  let locationOptions = activeCountryRate.states
    .filter((state) => {
      if (overrideOn === OverrideOn.products) {
        return !state.overrideValues?.some(
          (overrideValue) =>
            overrideValue.location == `${state.id}` &&
            overrideValue.overrideOn === overrideOn &&
            overrideValue.category == selectedCategory,
        );
      } else {
        return !state.overrideValues?.some(
          (overrideValue) => overrideValue.location == `${state.id}` && overrideValue.overrideOn === overrideOn,
        );
      }
    })
    .map((state) => ({
      label: isEU
        ? europeanUnionData.states.find((euState) => euState.numeric_code == state.id)?.name ?? ''
        : getStateByCode(activeCountryCode ?? '', Number(state.id))?.name ?? '',
      value: state.id,
    }));

  if (isSingleCountry) {
    const alreadySelected =
      overrideOn === OverrideOn.products
        ? activeCountryRate.overrideValues?.some(
            (overrideValue) =>
              overrideValue.location == activeCountryCode &&
              overrideValue.overrideOn === overrideOn &&
              overrideValue.category == selectedCategory,
          )
        : activeCountryRate.overrideValues?.some(
            (overrideValue) => overrideValue.location == activeCountryCode && overrideValue.overrideOn === overrideOn,
          );

    if (alreadySelected) {
      locationOptions = [];
    } else {
      locationOptions = [
        {
          label: getCountryByCode(activeCountryCode ?? '')?.name ?? '',
          value: activeCountryCode ?? '',
        },
      ];
    }
  }

  function handleModalClose({ action }: { action: 'CLOSE' | 'CONFIRM' }) {
    return () => {
      closeModal({ action });
    };
  }

  useEffect(() => {
    taxOverrideForm.setValue('overrideOn', overrideType);
  }, [overrideType]);

  return (
    <ModalWrapper onClose={handleModalClose({ action: 'CLOSE' })} title={title} modalStyle={styles.modalWrapperStyle}>
      <form
        onSubmit={taxOverrideForm.handleSubmit(async (values) => {
          const updatedValues = { ...values, type: selectionType };

          if (isSingleCountry) {
            activeCountryRate.overrideValues = [...(activeCountryRate.overrideValues ?? []), updatedValues];
          } else {
            activeCountryRate.states = activeCountryRate.states.map((state) => {
              if (state.id == values.location) {
                return {
                  ...state,
                  overrideValues: [...(state.overrideValues ?? []), updatedValues],
                };
              }
              return state;
            });
          }

          closeModal({ action: 'CONFIRM' });
        })}
      >
        <div css={styles.modalBody}>
          <Show when={overrideOn === OverrideOn.products}>
            <Controller
              control={taxOverrideForm.control}
              name="category"
              rules={requiredRule()}
              render={(controllerProps) => {
                return (
                  <FormSelectInput
                    {...controllerProps}
                    label={t('COM_EASYSTORE_APP_TAX_SETTINGS_CATEGORY')}
                    options={categoryOptions}
                    placeholder={t('COM_EASYSTORE_APP_TAX_SETTINGS_SELECT_CATEGORY')}
                  />
                );
              }}
            />
          </Show>
          <div css={styles.bottomWrapper}>
            <div css={styles.locationWrapper}>
              <Controller
                control={taxOverrideForm.control}
                name="location"
                rules={requiredRule()}
                render={(controllerProps) => {
                  return (
                    <FormSelectInput
                      {...controllerProps}
                      label={t('COM_EASYSTORE_APP_TAX_SETTINGS_LOCATION')}
                      options={locationOptions}
                      placeholder={t('COM_EASYSTORE_APP_TAX_SETTINGS_SELECT_LOCATION')}
                    />
                  );
                }}
              />
            </div>
            <Controller
              control={taxOverrideForm.control}
              name="rate"
              rules={requiredRule()}
              render={(controllerProps) => {
                return (
                  <FormInputWithContent
                    {...controllerProps}
                    content={t('COM_EASYSTORE_APP_TAX_SETTINGS_TAX_PERCENTAGE')}
                    label={t('COM_EASYSTORE_APP_TAX_SETTINGS_TAX_RATE')}
                    contentPosition="right"
                  />
                );
              }}
            />
          </div>
        </div>
        <div css={styles.buttonWrapper}>
          <Button variant={ButtonVariant.secondary} onClick={handleModalClose({ action: 'CLOSE' })}>
            {t('COM_EASYSTORE_APP_CANCEL')}
          </Button>
          <Button type="submit" variant={ButtonVariant.primary}>
            {t('COM_EASYSTORE_APP_ADD')}
          </Button>
        </div>
      </form>
    </ModalWrapper>
  );
};

export const useTaxOverrideModal = () => {
  const { showModal } = useModal();

  const openTaxOverrideModal = (props: Omit<TaxOverrideModalProps, 'closeModal'>) => {
    return showModal({ component: TaxOverrideModal, props });
  };

  return { openTaxOverrideModal };
};

const styles = {
  modalWrapperStyle: css`
    position: relative;
    width: 100%;
    min-width: 560px;
  `,
  modalBody: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
    margin-bottom: ${spacing[72]};
    padding: ${spacing[20]};
  `,
  buttonWrapper: css`
    position: absolute;
    bottom: 0;
    width: 100%;
    background-color: ${colorPalate.surface.default};
    box-shadow: ${shadow.popover};
    display: flex;
    padding: ${spacing[16]} ${spacing[20]};
    justify-content: end;
    gap: ${spacing[8]};
    align-items: center;
  `,
  bottomWrapper: css`
    display: flex;
    gap: ${spacing[12]};
  `,
  locationWrapper: css`
    width: 90%;
  `,
};

export default TaxOverrideModal;
