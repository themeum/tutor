import Button, { ButtonVariant } from '@Atoms/Button';
import Checkbox from '@Atoms/CheckBox';
import Divider from '@Atoms/Divider';
import FormInput from '@Components/fields/FormInput';
import { MultiSelectModalProps } from '@Components/fields/FormMultiSelectModalField';
import { shadow, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { useDebounce } from '@Hooks/useDebounce';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { useIsShiftHolding } from '@Hooks/useIsShiftHolding';
import { useTranslation } from '@Hooks/useTranslation';
import { styleUtils } from '@Utils/style-utils';
import { Option } from '@Utils/types';
import { useMemo, useState } from 'react';
import { Controller } from 'react-hook-form';

import ModalWrapper from './ModalWrapper';

interface FormValues {
  search: string;
}

const MultiSelectModal = <T,>({
  closeModal,
  options,
  selectedValues,
  onChange,
  title,
  searchLabel,
  searchPlaceholder = '',
}: MultiSelectModalProps<T>) => {
  const t = useTranslation();
  const [selectedItems, setSelectedItems] = useState<Option<T>[]>(selectedValues);
  const [lastSelectedIndex, setLastSelectedIndex] = useState(-1);
  const isShiftHolding = useIsShiftHolding();

  const form = useFormWithGlobalError<FormValues>({
    defaultValues: {
      search: '',
    },
  });
  const debouncedSearch = useDebounce(form.watch('search'));

  const items = useMemo(() => {
    setLastSelectedIndex(-1);

    if (!debouncedSearch) {
      return options;
    }

    return options.filter(({ label }) => label.toLowerCase().startsWith(debouncedSearch.toLowerCase()));
  }, [debouncedSearch, options]);

  const toggleCheckbox = (item: Option<T>, currentIndex: number) => (checked: boolean) => {
    setLastSelectedIndex(currentIndex);

    if (isShiftHolding && lastSelectedIndex > -1) {
      const itemsSlice = items.slice(
        Math.min(lastSelectedIndex, currentIndex),
        Math.max(lastSelectedIndex, currentIndex) + 1,
      );

      if (checked) {
        setSelectedItems((previousItems) => {
          return [...new Set([...previousItems, ...itemsSlice])];
        });
      } else {
        setSelectedItems((previousItems) => {
          return previousItems.filter((item) => !itemsSlice.includes(item));
        });
      }
    } else {
      if (checked) {
        setSelectedItems((previousItems) => [...previousItems, item]);
      } else {
        setSelectedItems((previousItems) => previousItems.filter(({ value }) => value !== item.value));
      }
    }
  };

  return (
    <ModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title}>
      <div css={styles.wrapper}>
        <div css={styles.searchWrapper}>
          <Controller
            name="search"
            control={form.control}
            render={(controllerProps) => (
              <FormInput {...controllerProps} label={searchLabel} placeholder={searchPlaceholder} />
            )}
          />
        </div>

        <div css={styles.itemsWrapper}>
          {items.map((option, index) => {
            return (
              <Checkbox
                key={String(option.value)}
                label={option.label}
                checked={selectedItems.some(({ value }) => value === option.value)}
                onChange={toggleCheckbox(option, index)}
              />
            );
          })}
        </div>

        <Divider css={styles.divider} />

        <div css={styles.footerWrapper}>
          <Checkbox
            label={t('COM_SPPAGEBUILDER_STORE_SELECT_ALL')}
            checked={items.length === selectedItems.length}
            onChange={(checked) => {
              if (checked) {
                setSelectedItems(items);
              } else {
                setSelectedItems([]);
              }
            }}
          />
          <div css={styles.footerButtonWrapper}>
            <Button variant={ButtonVariant.secondary} onClick={() => closeModal({ action: 'CLOSE' })}>
              {t('COM_SPPAGEBUILDER_STORE_CANCEL')}
            </Button>
            <Button
              variant={ButtonVariant.primary}
              onClick={() => {
                onChange(selectedItems);
                closeModal({ action: 'CONFIRM' });
              }}
            >
              {t('COM_SPPAGEBUILDER_STORE_DONE')}
            </Button>
          </div>
        </div>
      </div>
    </ModalWrapper>
  );
};

export default MultiSelectModal;

const styles = {
  wrapper: css`
    min-width: 456px;
    width: 100%;
  `,
  searchWrapper: css`
    width: 100%;
    padding: ${spacing[20]} ${spacing[20]} ${spacing[10]};
  `,
  itemsWrapper: css`
    padding: 0 ${spacing[20]};
    padding-bottom: ${spacing[10]};
    height: 156px;
    display: flex;
    flex-direction: column;
    gap: ${spacing[6]};

    ${styleUtils.overflowYAuto};
  `,
  divider: css`
    box-shadow: ${shadow.dividerTop};
  `,
  footerWrapper: css`
    width: 100%;
    display: flex;
    justify-content: space-between;
    gap: ${spacing[8]};
    padding: ${spacing[16]} ${spacing[20]};
  `,

  footerButtonWrapper: css`
    display: flex;
    gap: ${spacing[8]};
  `,
};
