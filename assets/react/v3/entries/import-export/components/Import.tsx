import { css } from '@emotion/react';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { UploadButton } from '@TutorShared/molecules/FileUploader';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { noop } from '@TutorShared/utils/util';
import { __ } from '@wordpress/i18n';

const Import = () => {
  return (
    <div css={styles.wrapper}>
      <div css={styles.title}>{__('Import', 'tutor')}</div>

      <div css={styles.fileUpload}>
        <img
          src="https://tutor.lms-assets.com/2023/10/03/16/23/20/0c4b8f1d-2a5e-4f7b-8a6c-9d0e1f2b3a5c/file-upload.png"
          alt="File Upload"
          width={100}
          height={100}
        />

        <UploadButton acceptedTypes={['.csv', '.json']} variant="secondary" onError={noop} onUpload={noop}>
          {__('Choose a file', 'tutor')}
        </UploadButton>

        <div css={styles.description}>{__('Supported format: .CSV, .JSON', 'tutor')}</div>
      </div>
    </div>
  );
};

export default Import;

const styles = {
  wrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[12]};
  `,
  title: css`
    color: ${colorTokens.text.subdued};
  `,
  fileUpload: css`
    ${styleUtils.display.flex('column')}
    align-items: center;
    gap: ${spacing[8]};
    padding: ${spacing[16]} ${spacing[24]};
    padding-block: ${spacing[48]};
    background-color: ${colorTokens.background.white};
    position: relative;
    border-radius: ${borderRadius.card};

    ::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      border-radius: ${borderRadius.card};
      background-image:
        linear-gradient(to right, ${colorTokens.stroke.border} 50%, rgba(255, 255, 255, 0) 0%),
        linear-gradient(${colorTokens.stroke.border} 50%, rgba(255, 255, 255, 0) 0%),
        linear-gradient(to right, ${colorTokens.stroke.border} 50%, rgba(255, 255, 255, 0) 0%),
        linear-gradient(${colorTokens.stroke.border} 50%, rgba(255, 255, 255, 0) 0%);
      background-size:
        18px 2px,
        2px 18px;
      background-position: top, right, bottom, left;
      background-repeat: repeat-x, repeat-y;
    }
  `,
  description: css`
    ${typography.tiny()}
    color: ${colorTokens.text.subdued};
  `,
};
