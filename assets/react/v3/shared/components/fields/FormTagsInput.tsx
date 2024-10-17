import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { useState } from 'react';

import Checkbox from '@Atoms/CheckBox';
import Chip from '@Atoms/Chip';
import Show from '@Controls/Show';
import { useDebounce } from '@Hooks/useDebounce';
import { type Tag, useCreateTagMutation, useTagListQuery } from '@Services/tags';
import { __ } from '@wordpress/i18n';
import FormFieldWrapper from './FormFieldWrapper';

interface FormTagsInputProps extends FormControllerProps<Tag[] | null> {
  label?: string;
  placeholder?: string;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  isHidden?: boolean;
  helpText?: string;
  removeOptionsMinWidth?: boolean;
}

const FormTagsInput = ({
  field,
  fieldState,
  label,
  placeholder = '',
  disabled,
  readOnly,
  loading,
  helpText,
  removeOptionsMinWidth = false,
}: FormTagsInputProps) => {
  const fieldValue = field.value ?? [];

  const [searchText, setSearchText] = useState('');
  const debouncedSearchText = useDebounce(searchText);

  const [isOpen, setIsOpen] = useState(false);

  const tagListQuery = useTagListQuery({ search: debouncedSearchText });
  const createTagMutation = useCreateTagMutation();

  const { triggerRef, triggerWidth, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
    dependencies: [tagListQuery.data?.length],
  });

  const tags = tagListQuery.data ?? [];

  const handleCheckboxChange = (checked: boolean, tag: Tag) => {
    if (checked) {
      field.onChange([...fieldValue, tag]);
    } else {
      field.onChange(fieldValue.filter((item) => item.id !== tag.id));
    }
  };

  const handleAddTag = async () => {
    if (searchText.length) {
      const response = await createTagMutation.mutateAsync({
        name: searchText,
      });
      field.onChange([...fieldValue, response.data]);
      setIsOpen(false);
      setSearchText('');
    }
  };

  return (
    <FormFieldWrapper
      fieldState={fieldState}
      field={field}
      label={label}
      disabled={disabled}
      readOnly={readOnly}
      loading={loading}
      helpText={helpText}
    >
      {(inputProps) => {
        const { css: inputCss, ...restInputProps } = inputProps;

        return (
          <div css={styles.mainWrapper}>
            <div css={styles.inputWrapper} ref={triggerRef}>
              <input
                {...restInputProps}
                css={[inputCss, styles.input]}
                onClick={() => setIsOpen(true)}
                autoComplete="off"
                readOnly={readOnly}
                placeholder={placeholder}
                value={searchText}
                onChange={(event) => {
                  setSearchText(event.target.value);
                }}
              />
            </div>

            {fieldValue.length > 0 && (
              <div css={styles.tagsWrapper}>
                {fieldValue.map((tag: Tag) => (
                  <Chip key={tag.id} label={tag.name} onClick={() => handleCheckboxChange(false, tag)} />
                ))}
              </div>
            )}

            <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)}>
              <div
                css={[
                  styles.optionsWrapper,
                  {
                    left: position.left,
                    top: position.top,
                    maxWidth: triggerWidth,
                  },
                ]}
                ref={popoverRef}
              >
                <ul css={[styles.options(removeOptionsMinWidth)]}>
                  {searchText.length > 0 && (
                    <li>
                      <button type="button" css={styles.addTag} onClick={handleAddTag}>
                        <SVGIcon name="plus" width={24} height={24} />
                        <strong>{__('Add', 'tutor')}</strong> {searchText}
                      </button>
                    </li>
                  )}

                  <Show
                    when={tags.length > 0}
                    fallback={<div css={styles.notTag}>{__('No tag created yet.', 'tutor')}</div>}
                  >
                    {tags.map((tag) => (
                      <li key={String(tag.id)} css={styles.optionItem}>
                        <Checkbox
                          label={tag.name}
                          checked={!!fieldValue.find((item) => item.id === tag.id)}
                          onChange={(checked) => handleCheckboxChange(checked, tag)}
                        />
                      </li>
                    ))}
                  </Show>
                </ul>
              </div>
            </Portal>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormTagsInput;

const styles = {
  mainWrapper: css`
    width: 100%;
  `,
  notTag: css`
    ${typography.caption()};
    min-height: 80px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: ${colorTokens.text.subdued};
  `,
  inputWrapper: css`
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
  `,
  input: css`
    ${typography.body()};
    width: 100%;
    ${styleUtils.textEllipsis};

    :focus {
      outline: none;
      box-shadow: ${shadow.focus};
    }
  `,
  tagsWrapper: css`
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: ${spacing[4]};
    margin-top: ${spacing[8]};
  `,
  optionsWrapper: css`
    position: absolute;
    width: 100%;
  `,
  options: (removeOptionsMinWidth: boolean) => css`
    z-index: ${zIndex.dropdown};
    background-color: ${colorTokens.background.white};
    list-style-type: none;
    box-shadow: ${shadow.popover};
    padding: ${spacing[4]} 0;
    margin: 0;
    max-height: 400px;
    border-radius: ${borderRadius[6]};
    ${styleUtils.overflowYAuto};

    ${
      !removeOptionsMinWidth &&
      css`
      min-width: 200px;
    `
    }
  `,
  optionItem: css`
    min-height: 40px;
    height: 100%;
    width: 100%;
    display: flex;
    align-items: center;
    padding: ${spacing[8]};
    transition: background-color 0.3s ease-in-out;

    label {
      width: 100%;
    }

    &:hover {
      background-color: ${colorTokens.background.hover};
    }
  `,
  addTag: css`
    ${styleUtils.resetButton};
    ${typography.body()}
    line-height: ${lineHeight[24]};
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
    width: 100%;
    padding: ${spacing[8]};

    &:hover {
      background-color: ${colorTokens.background.hover};
    }
  `,
};
