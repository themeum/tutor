import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorPalate, colorTokens, fontSize, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { useEffect, useState } from 'react';

import { __ } from '@wordpress/i18n';
import FormFieldWrapper from './FormFieldWrapper';

interface FormEditableAliasProps extends FormControllerProps<string> {
  label?: string;
  onChange?: (value: string) => void;
  baseURL: string;
}

const FormEditableAlias = ({ field, fieldState, label = '', baseURL }: FormEditableAliasProps) => {
  const { value = '' } = field;
  const fullUrl = `${baseURL}/${value}`;
  const [isEditing, setIsEditing] = useState(false);
  const [fieldValue, setFieldValue] = useState(fullUrl);
  const prefix = `${baseURL}/`;
  const [editValue, setEditValue] = useState(value);

  useEffect(() => {
    if (baseURL) {
      setFieldValue(`${baseURL}/${value}`);
    }
    if (value) {
      setEditValue(value);
    }
  }, [baseURL, value]);

  return (
    <FormFieldWrapper field={field} fieldState={fieldState}>
      {(inputProps) => {
        return (
          <div css={styles.aliasWrapper}>
            {label && <label css={styles.label}>{label}: </label>}
            <div css={styles.linkWrapper}>
              {!isEditing ? (
                <>
                  <a href={fieldValue} target="_blank" css={styles.link} rel="noreferrer">
                    {fieldValue}
                  </a>
                  <button css={styles.iconWrapper} type="button" onClick={() => setIsEditing((prev) => !prev)}>
                    <SVGIcon name="edit" width={24} height={24} style={styles.editIcon} />
                  </button>
                </>
              ) : (
                <>
                  <span css={styles.prefix} title={prefix}>
                    {prefix}
                  </span>
                  <div css={styles.editWrapper}>
                    <input
                      {...inputProps}
                      css={styles.editable}
                      type="text"
                      value={editValue}
                      onChange={(e) => setEditValue(e.target.value)}
                      autoComplete="off"
                    />

                    <Button
                      variant="secondary"
                      isOutlined
                      size="small"
                      buttonCss={styles.saveBtn}
                      onClick={() => {
                        setIsEditing(false);
                        field.onChange(editValue);
                      }}
                    >
                      {__('Save', 'tutor')}
                    </Button>
                    <Button
                      buttonContentCss={styles.cancelButton}
                      variant="text"
                      size="small"
                      onClick={() => {
                        setIsEditing(false);
                        setEditValue(value);
                      }}
                    >
                      {__('Cancel', 'tutor')}
                    </Button>
                  </div>
                </>
              )}
            </div>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

const styles = {
  aliasWrapper: css`
    display: flex;
    min-height: 32px;
    align-items: center;
    gap: ${spacing[4]};
  `,
  label: css`
    flex-shrink: 0;
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
  `,
  linkWrapper: css`
    display: flex;
    align-items: center;
    width: fit-content;
    font-size: ${fontSize[14]};
  `,
  link: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    text-decoration: none;
    ${styleUtils.text.ellipsis(1)}
    max-width: fit-content;
  `,
  iconWrapper: css`
    ${styleUtils.resetButton}
    margin-left: ${spacing[8]};
    height: 24px;
    width: 24px;
    background-color: ${colorTokens.background.white};
    border-radius: ${borderRadius[4]};

    :focus {
      box-shadow: ${shadow.focus};
    }
  `,
  editIcon: css`
    color: ${colorTokens.icon.default};
    :hover {
      color: ${colorTokens.icon.brand};
    }
  `,
  prefix: css`
    ${typography.caption()}
    color: ${colorTokens.text.subdued};
    ${styleUtils.text.ellipsis(1)}
    word-break: break-all;
    max-width: fit-content;
  `,
  editWrapper: css`
    margin-left: ${spacing[2]};
    display: flex;
    align-items: center;
    width: fit-content;
  `,
  editable: css`
    ${typography.caption()}
    background: ${colorTokens.background.white};
    width: 208px;
    height: 32px;
    border: 1px solid ${colorTokens.stroke.default};
    padding: ${spacing[8]} ${spacing[12]};
    border-radius: ${borderRadius[6]};
    margin-right: ${spacing[8]};
    outline: none;
    border: 1px solid ${colorPalate.border.default};

    :focus {
      ${styleUtils.inputFocus}
    }
  `,
  saveBtn: css`
    margin-right: ${spacing[8]};
  `,
  cancelButton: css`
    color: ${colorTokens.text.brand};
  `,
};

export default FormEditableAlias;
