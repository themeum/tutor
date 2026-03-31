import { styleUtils } from '@TutorShared/utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { borderRadius, Breakpoint, colorTokens, fontSize, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { convertToSlug } from '@TutorShared/utils/util';

import FormFieldWrapper from './FormFieldWrapper';

interface FormEditableAliasProps extends FormControllerProps<string> {
  label?: string;
  onChange?: (value: string) => void;
  baseURL: string;
}

const FormEditableAlias = ({ field, fieldState, label = '', baseURL, onChange }: FormEditableAliasProps) => {
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
                  <a
                    data-cy="course-slug"
                    href={fieldValue}
                    target="_blank"
                    css={styles.link}
                    title={fieldValue}
                    rel="noreferrer"
                  >
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
                      className="tutor-input-field"
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
                        field.onChange(convertToSlug(editValue.replace(baseURL, '')));
                        onChange?.(convertToSlug(editValue.replace(baseURL, '')));
                      }}
                    >
                      {__('Save', __TUTOR_TEXT_DOMAIN__)}
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
                      {__('Cancel', __TUTOR_TEXT_DOMAIN__)}
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

    ${Breakpoint.smallMobile} {
      flex-direction: column;
      gap: ${spacing[4]};
      align-items: flex-start;
    }
  `,
  label: css`
    flex-shrink: 0;
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0px;
  `,
  linkWrapper: css`
    display: flex;
    align-items: center;
    width: fit-content;
    font-size: ${fontSize[14]};

    ${Breakpoint.smallMobile} {
      gap: ${spacing[4]};
      flex-wrap: wrap;
    }
  `,
  link: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    text-decoration: none;
    ${styleUtils.text.ellipsis(1)}
    max-width: fit-content;
    word-break: break-all;
  `,
  iconWrapper: css`
    ${styleUtils.resetButton}
    margin-left: ${spacing[8]};
    height: 24px;
    width: 24px;
    background-color: ${colorTokens.background.white};
    border-radius: ${borderRadius[4]};

    :focus {
      ${styleUtils.inputFocus}
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
    &.tutor-input-field {
      ${typography.caption()}
      background: ${colorTokens.background.white};
      width: 208px;
      height: 32px;
      border: 1px solid ${colorTokens.stroke.default};
      padding: ${spacing[8]} ${spacing[12]};
      border-radius: ${borderRadius.input};
      margin-right: ${spacing[8]};
      outline: none;

      &:focus {
        border-color: ${colorTokens.stroke.default};
        box-shadow: none;
        outline: 2px solid ${colorTokens.stroke.brand};
        outline-offset: 1px;
      }
    }
  `,
  saveBtn: css`
    flex-shrink: 0;
    margin-right: ${spacing[8]};
  `,
  cancelButton: css`
    color: ${colorTokens.text.brand};
  `,
};

export default FormEditableAlias;
