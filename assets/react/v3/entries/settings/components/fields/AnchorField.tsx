import { type SettingsField } from '@Settings/contexts/SettingsContext';
import Button from '@TutorShared/atoms/Button';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { css } from '@emotion/react';
import React from 'react';

interface AnchorFieldProps {
  field: SettingsField;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  value?: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onChange?: (value: any) => void;
}

const styles = {
  container: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,

  description: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;
  `,

  buttonContainer: css`
    display: flex;
    gap: ${spacing[12]};
    align-items: center;
  `,
};

const AnchorField: React.FC<AnchorFieldProps> = ({ field }) => {
  // Extract button configuration from field
  const buttonConfig = field.buttons || {};
  const editButton = buttonConfig.edit || {};

  const buttonText = editButton.text || field.label || 'Click Here';
  const buttonUrl = editButton.url || '#';

  const handleClick = () => {
    if (buttonUrl && buttonUrl !== '#') {
      window.open(buttonUrl, '_blank', 'noopener,noreferrer');
    }
  };

  return (
    <div css={styles.container}>
      {field.desc && (
        <p css={styles.description}>
          <div dangerouslySetInnerHTML={{ __html: field.desc }} />
        </p>
      )}

      <div css={styles.buttonContainer}>
        <Button variant="primary" size="regular" onClick={handleClick} disabled={!buttonUrl || buttonUrl === '#'}>
          {buttonText}
        </Button>
      </div>
    </div>
  );
};

export default AnchorField;
