import Button, { ButtonVariant } from '@Atoms/Button';
import Chip from '@Atoms/Chip';
import SVGIcon from '@Atoms/SVGIcon';
import CategoryTreeModal from '@Components/modals/CategoryTreeModal';
import { useModal } from '@Components/modals/Modal';
import { borderRadius, colorPalate, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { useTranslation } from '@Hooks/useTranslation';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { Option } from '@Utils/types';
import { useEffect, useState } from 'react';

import FormFieldWrapper from './FormFieldWrapper';

interface FormAddCategoriesProps extends FormControllerProps<Option<number>[] | undefined> {
  label?: string;
  disabled?: boolean;
  loading?: boolean;
  helpText?: string;
}

const MAX_CATEGORY_CHIPS = 12;

const FormAddCategories = ({ label, field, fieldState, disabled, loading, helpText }: FormAddCategoriesProps) => {
  const t = useTranslation();
  const [selectedValues, setSelectedValues] = useState<Option<number>[]>([]);
  const { showModal } = useModal();

  useEffect(() => {
    setSelectedValues(field.value ?? []);
  }, [field.value]);

  const removeHandler = (value: Option<number>) => {
    const updatedValue = selectedValues.filter((item) => item.value !== value.value);

    setSelectedValues(updatedValue);
    field.onChange(updatedValue);
  };

  const showModalHandler = () => {
    showModal({
      component: CategoryTreeModal,
      props: {
        title: t('COM_SPPAGEBUILDER_STORE_COUPON_SELECT_CATEGORIES'),
        selectedValues: field.value ?? [],
        onChange: field.onChange,
      },
    });
  };

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      disabled={disabled}
      loading={loading}
      helpText={helpText}
    >
      {() => {
        return (
          <div css={styles.inputWrapper}>
            {selectedValues.length > 0 ? (
              <>
                <p css={typography.body()}>{t('COM_SPPAGEBUILDER_STORE_COUPON_SELECT_CATEGORIES')}</p>

                <div css={styles.chipWrapper}>
                  {selectedValues.slice(0, MAX_CATEGORY_CHIPS).map((value, index) => {
                    return (
                      <Chip key={index}>
                        <span>{value.label}</span>

                        <span css={styles.chipRemove}>
                          <button type="button" css={styleUtils.resetButton} onClick={() => removeHandler(value)}>
                            <SVGIcon name="times" width={10} height={10} style={styles.chipIcon} />
                          </button>
                        </span>
                      </Chip>
                    );
                  })}

                  {selectedValues.length > MAX_CATEGORY_CHIPS && (
                    <div css={styles.moreValues}>
                      {t('COM_SPPAGEBUILDER_STORE_COUPON_CATEGORY_COUNT', {
                        amount: selectedValues.length - MAX_CATEGORY_CHIPS,
                      })}
                    </div>
                  )}
                </div>

                <Button
                  icon={<SVGIcon name="pencil" height={16} width={16} />}
                  variant={ButtonVariant.plain}
                  onClick={showModalHandler}
                  buttonCss={styles.editButton}
                >
                  {t('COM_SPPAGEBUILDER_STORE_COUPON_EDIT_CATEGORIES')}
                </Button>
              </>
            ) : (
              <Button
                variant={ButtonVariant.plainMonochrome}
                icon={<SVGIcon name="plusCircle" width={19} height={19} />}
                disabled={disabled}
                onClick={showModalHandler}
                buttonCss={styles.addCategoryButton}
              >
                <span>{t('COM_SPPAGEBUILDER_STORE_COUPON_ADD_CATEGORIES')}</span>
              </Button>
            )}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormAddCategories;

const styles = {
  inputWrapper: css`
    width: 100%;
    border-radius: ${borderRadius[6]};
    border: 1px solid ${colorPalate.border.neutral};
    box-shadow: ${shadow.input};
    padding: ${spacing[10]} ${spacing[16]};
    background-color: ${colorPalate.surface.default};
  `,
  addCategoryButton: css`
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
  moreValues: css`
    ${typography.body()};
    ${styleUtils.flexCenter()};
    color: ${colorPalate.text.neutral};
  `,
  editButton: css`
    margin-top: ${spacing[16]};
  `,
};
