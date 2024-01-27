import SVGIcon from '@Atoms/SVGIcon';
import { colorPalate, shadow, borderRadius, fontWeight, fontSize, lineHeight, spacing, zIndex } from '@Config/styles';
import { css } from '@emotion/react';
import { useTranslation } from '@Hooks/useTranslation';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { ProductStatus } from '@Utils/types';
import React, { useState } from 'react';

import FormFieldWrapper from './FormFieldWrapper';

interface FormProductStatusProps extends FormControllerProps<ProductStatus | undefined> {
  label: string;
  disabled?: boolean;
  loading?: boolean;
  helpText?: string;
  onChange?: () => void;
  isHidden?: boolean;
}

const styles = {
  statusContainer: css`
    position: relative;
  `,
  statusWrapper: css`
    ${styleUtils.resetButton};
    display: block;
    width: 100%;
    background: ${colorPalate.basic.white};
    border-radius: ${borderRadius[6]};
  `,
  statusItem: css`
    display: flex;
    align-items: center;
    border-radius: ${borderRadius[6]};
    box-shadow: ${shadow.card};
    padding: ${spacing[8]} ${spacing[16]} ${spacing[8]} ${spacing[12]};
    cursor: pointer;

    & svg:first-of-type {
      color: ${colorPalate.basic.primary.default};
    }

    & svg:last-of-type {
      color: ${colorPalate.icon.default};
    }
  `,
  statusContent: css`
    flex: 1;
    padding-left: ${spacing[10]};
  `,
  statusTitle: css`
    font-weight: ${fontWeight.medium};
    font-size: ${fontSize[16]};
    line-height: ${lineHeight[20]};
    color: ${colorPalate.text.default};
    margin-bottom: ${spacing[4]};
  `,
  statusDescription: css`
    font-weight: ${fontWeight.regular};
    font-size: ${fontSize[14]};
    line-height: ${lineHeight[20]};
    color: ${colorPalate.text.neutral};
  `,
  statusDropDownWrap: css`
    border-radius: ${borderRadius[6]};
    box-shadow: ${shadow.popover};
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    background: ${colorPalate.basic.white};
    overflow: hidden;
    z-index: ${zIndex.positive};
  `,
  statusDropDownItem: css`
    ${styleUtils.resetButton}
    width: 100%;
    display: flex;
    align-items: center;
    padding: ${spacing[8]} ${spacing[16]} ${spacing[8]} ${spacing[12]};
    cursor: pointer;

    & svg {
      color: ${colorPalate.text.neutral};
    }

    &:hover {
      background-color: ${colorPalate.surface.hover};
      & :is(svg, div > div:first-of-type) {
        color: ${colorPalate.basic.primary.default};
      }
    }
  `,
  statusDropDownTitle: css`
    font-weight: ${fontWeight.medium};
    font-size: ${fontSize[16]};
    line-height: ${lineHeight[20]};
    color: ${colorPalate.text.neutral};
    margin-bottom: ${spacing[4]};
  `,
};

const FormProductStatus = ({
  label,
  field,
  fieldState,
  disabled,
  loading,
  helpText,
  onChange,
  isHidden,
}: FormProductStatusProps) => {
  const t = useTranslation();
  const [showDropDown, setShowDropDown] = useState(false);

  const productStatusOptions: Record<string, { title: string; description: string; icon: React.ReactNode }> = {
    draft: {
      title: t('COM_SPPAGEBUILDER_STORE_DRAFT'),
      description: t('COM_SPPAGEBUILDER_STORE_DRAFT_DESCRIPTION'),
      icon: <SVGIcon name="penToSquare" width={32} height={32} />,
    },
    published: {
      title: t('COM_SPPAGEBUILDER_STORE_PUBLISH'),
      description: t('COM_SPPAGEBUILDER_STORE_PUBLISH_DESCRIPTION'),
      icon: <SVGIcon name="eye" width={32} height={32} />,
    },
  };

  const handleChangeStatus = (status: ProductStatus) => {
    setShowDropDown(false);
    field.onChange(status);

    if (onChange) {
      onChange();
    }
  };

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      disabled={disabled}
      loading={loading}
      helpText={helpText}
      isHidden={isHidden}
    >
      {(inputProps) => {
        const value = field.value || 'draft';

        return (
          <div css={styles.statusContainer}>
            <button
              {...inputProps}
              type="button"
              onClick={() => setShowDropDown((previous) => !previous)}
              css={styles.statusWrapper}
            >
              <div css={styles.statusItem}>
                {productStatusOptions[value].icon}
                <div css={styles.statusContent}>
                  <div css={styles.statusTitle}>{productStatusOptions[value].title}</div>
                  <div css={styles.statusDescription}>{productStatusOptions[value].description}</div>
                </div>
                <SVGIcon name="chevronDown" width={24} height={24} />
              </div>
            </button>

            {showDropDown && (
              <div css={styles.statusDropDownWrap}>
                <button type="button" onClick={() => handleChangeStatus(value)} css={styles.statusDropDownItem}>
                  {productStatusOptions[value].icon}
                  <div css={styles.statusContent}>
                    <div css={styles.statusDropDownTitle}>{productStatusOptions[value].title}</div>
                    <div css={styles.statusDescription}>{productStatusOptions[value].description}</div>
                  </div>
                </button>

                <button
                  onClick={() => handleChangeStatus(value === 'draft' ? 'published' : 'draft')}
                  css={styles.statusDropDownItem}
                >
                  {value === 'draft' ? productStatusOptions.published.icon : productStatusOptions.draft.icon}
                  <div css={styles.statusContent}>
                    <div css={styles.statusDropDownTitle}>
                      {value === 'draft' ? productStatusOptions.published.title : productStatusOptions.draft.title}
                    </div>
                    <div css={styles.statusDescription}>
                      {value === 'draft'
                        ? productStatusOptions.published.description
                        : productStatusOptions.draft.description}
                    </div>
                  </div>
                </button>
              </div>
            )}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormProductStatus;
