import Button, { ButtonVariant } from '@Atoms/Button';
import Chip from '@Atoms/Chip';
import SVGIcon from '@Atoms/SVGIcon';
import { ModalProps, useModal } from '@Components/modals/Modal';
import MultiSelectModal from '@Components/modals/MultiSelectModal';
import { MAX_MULTISELECT_CHIPS } from '@Config/constants';
import { borderRadius, colorPalate, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { useTranslation } from '@Hooks/useTranslation';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { Option } from '@Utils/types';
import { useMemo } from 'react';

import FormFieldWrapper from './FormFieldWrapper';

interface FormMultiSelectModalField<T> extends FormControllerProps<T[]> {
  label?: string;
  options: Option<T>[];
  placeholder?: string;
  onChange?: (selectedOption: Option<T>) => void;
  disabled?: boolean;
  loading?: boolean;
  isSearchable?: boolean;
  isHidden?: boolean;
  addButtonLabel: string;
  editButtonLabel: string;
  selectedLabel?: string;
  modalTitle: string;
  modalSearchLabel: string;
  modalSearchPlaceholder?: string;
  maxChips?: number;
}

export interface MultiSelectModalProps<T> extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  options: Option<T>[];
  selectedValues: Option<T>[];
  onChange: (values: Option<T>[]) => void;
  searchLabel: string;
  searchPlaceholder?: string;
}

const getArrayOfItemValues = <T,>(items: Option<T>[]) => items.map(({ value }) => value);

const FormMultiSelectModalField = <T,>({
  field,
  label,
  fieldState,
  options,
  disabled,
  loading,
  isHidden,
  addButtonLabel,
  editButtonLabel,
  selectedLabel,
  modalTitle,
  modalSearchLabel,
  modalSearchPlaceholder,
  maxChips = MAX_MULTISELECT_CHIPS,
}: FormMultiSelectModalField<T>) => {
  const { showModal } = useModal();
  const t = useTranslation();

  const selectedValues = useMemo(() => {
    return options.filter(({ value }) => field.value.includes(value));
  }, [field.value, options]);

  const showModalHandler = () => {
    showModal<MultiSelectModalProps<T>>({
      component: MultiSelectModal,
      props: {
        title: modalTitle,
        searchLabel: modalSearchLabel,
        searchPlaceholder: modalSearchPlaceholder,
        options,
        selectedValues,
        onChange: (updatedItems) => field.onChange(getArrayOfItemValues(updatedItems)),
      },
    });
  };

  return (
    <FormFieldWrapper
      fieldState={fieldState}
      field={field}
      label={label}
      disabled={disabled || options.length === 0}
      loading={loading}
      isHidden={isHidden}
    >
      {() => {
        return (
          <div css={styles.inputWrapper({ isRemoveTopPadding: !selectedLabel && selectedValues.length > 0 })}>
            {selectedValues.length > 0 ? (
              <>
                {!!selectedLabel && <p css={typography.body()}>{selectedLabel}</p>}

                <div css={styles.chipWrapper}>
                  {selectedValues.slice(0, maxChips).map((item, index) => {
                    return (
                      <Chip key={index}>
                        <span>{item.label}</span>

                        <span css={styles.chipRemove}>
                          <button
                            type="button"
                            css={styleUtils.resetButton}
                            onClick={() => {
                              const updatedItems = selectedValues.filter(({ value }) => value !== item.value);

                              field.onChange(getArrayOfItemValues(updatedItems));
                            }}
                          >
                            <SVGIcon name="times" width={10} height={10} style={styles.chipIcon} />
                          </button>
                        </span>
                      </Chip>
                    );
                  })}

                  {selectedValues.length > maxChips && (
                    <div css={styles.moreItems}>
                      {t('COM_SPPAGEBUILDER_STORE_SELECTED_COUNT', {
                        remainingItems: selectedValues.length - maxChips,
                      })}
                    </div>
                  )}
                </div>

                <Button
                  icon={<SVGIcon name="pencil" height={16} width={16} />}
                  variant={ButtonVariant.plain}
                  onClick={showModalHandler}
                  buttonCss={styles.editItemsButton}
                >
                  {editButtonLabel}
                </Button>
              </>
            ) : (
              <Button
                variant={ButtonVariant.plain}
                icon={<SVGIcon name="plusCircle" width={19} height={19} />}
                disabled={disabled || options.length === 0}
                onClick={showModalHandler}
                buttonCss={styles.addItemButton}
              >
                {addButtonLabel}
              </Button>
            )}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormMultiSelectModalField;

const styles = {
  inputWrapper: ({ isRemoveTopPadding }: { isRemoveTopPadding: boolean }) => css`
    width: 100%;
    border-radius: ${borderRadius[6]};
    border: 1px solid ${colorPalate.border.neutral};
    box-shadow: ${shadow.input};
    padding: ${spacing[12]};
    background-color: ${colorPalate.surface.default};

    ${isRemoveTopPadding &&
    css`
      padding-top: 0;
    `}
  `,
  addItemButton: css`
    width: 100%;
    &,
    :hover {
      color: ${colorPalate.text.default};
    }
  `,
  chipWrapper: css`
    display: flex;
    flex-wrap: wrap;
    margin-top: ${spacing[16]};
    gap: ${spacing[8]};
  `,

  chipRemove: css`
    margin-left: ${spacing[8]};
  `,

  chipIcon: css`
    color: ${colorPalate.icon.neutral};
  `,

  moreItems: css`
    ${typography.body()};
    ${styleUtils.flexCenter()};
    color: ${colorPalate.text.neutral};
  `,

  editItemsButton: css`
    margin-top: ${spacing[16]};
  `,
};
