import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorPalate, fontSize, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { useTranslation } from '@Hooks/useTranslation';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';

import FormFieldWrapper from './FormFieldWrapper';

interface FormEditableAliasProps extends FormControllerProps<string> {
  label?: string;
  onChange?: (value: string) => void;
  baseURL: string;
}

const FormEditableAlias = ({ field, fieldState, label = '', baseURL }: FormEditableAliasProps) => {
  const { value } = field;
  const fullUrl = `${baseURL}/${value}`;
  const [isEditing, setIsEditing] = useState(false);
  const [fieldValue, setFieldValue] = useState(fullUrl);
  const prefix = `${baseURL}/`;
  const [editValue, setEditValue] = useState(value);
  const t = useTranslation();

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
                  <Link to={value} css={styles.link}>
                    {fieldValue}
                  </Link>
                  <button css={styles.iconWrapper} type="button" onClick={() => setIsEditing((prev) => !prev)}>
                    <SVGIcon name="edit" width={24} height={24} style={styles.editIcon} />
                  </button>
                </>
              ) : (
                <>
                  <span css={styles.prefix}>{prefix}</span>
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
                      buttonCss={styles.saveBtn}
                      onClick={() => {
                        setIsEditing(false);
                        field.onChange(editValue);
                      }}
                    >
                      {t('COM_SPPAGEBUILDER_STORE_SAVE')}
                    </Button>
                    <Button
                      variant="text"
                      buttonCss={styles.cancelBtn}
                      onClick={() => {
                        setIsEditing(false);
                        setEditValue(value);
                      }}
                    >
                      {t('COM_SPPAGEBUILDER_STORE_VARIATION_OPTION_CANCEL_BUTTON')}
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
    min-height: 36px;
    align-items: center;
    gap: ${spacing[4]};
  `,
  label: css`
    ${typography.body()};
    color: ${colorPalate.text.neutral};
  `,
  linkWrapper: css`
    display: flex;
    align-items: center;
    width: fit-content;
    font-size: ${fontSize[14]};
  `,
  link: css`
    text-decoration: none;
  `,
  iconWrapper: css`
    ${styleUtils.resetButton}
    margin-left: ${spacing[8]};
    background-color: ${colorPalate.surface.hover};
    border-radius: ${borderRadius[4]};
  `,
  editIcon: css`
    color: ${colorPalate.icon.default};
    :hover {
      color: ${colorPalate.basic.interactive};
    }
  `,
  prefix: css`
    ${typography.body()}
    color: ${colorPalate.text.neutral};
  `,
  editWrapper: css`
    display: flex;
    align-items: center;
    width: fit-content;
  `,
  editable: css`
    ${typography.body()}
    background: ${colorPalate.surface.default};
    width: 208px;
    height: 36px;
    border: 1px solid ${colorPalate.border.neutral};
    padding: ${spacing[8]} ${spacing[12]};
    border-radius: ${borderRadius[6]};
    margin-right: ${spacing[16]};
    outline: none;
    active: {
      border: 1px solid ${colorPalate.border.neutral};
    }
  `,
  saveBtn: css`
    padding: ${spacing[8]} ${spacing[20]};
    box-shadow: none;
  `,
  cancelBtn: css`
    padding: ${spacing[8]} ${spacing[20]};
  `,
};

export default FormEditableAlias;
