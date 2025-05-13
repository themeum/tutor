import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import { type ModalProps } from '@TutorShared/components/modals/Modal';

import Logo from '@CourseBuilderComponents/layouts/header/Logo';
import { useExportableContentQuery } from '@ImportExport/services/import-export';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';

interface ExportModalProps extends ModalProps {
  onClose: () => void;
}

const ExportModal = ({ onClose }: ExportModalProps) => {
  const getExportableContentQuery = useExportableContentQuery();
  console.log('Here', getExportableContentQuery.isLoading);
  return (
    <BasicModalWrapper
      onClose={onClose}
      maxWidth={826}
      entireHeader={
        <div css={styles.header}>
          <div css={styles.headerTitle}>
            <Logo />
            <span>{__('Exporter', 'tutor')}</span>
          </div>
          <div>
            <Button
              variant="primary"
              size="small"
              icon={<SVGIcon name="export" width={24} height={24} />}
              onClick={onClose}
            >
              {__('Export', 'tutor')}
            </Button>
          </div>
        </div>
      }
    >
      <div css={styles.wrapper}>
        <div css={styles.formWrapper}>
          <div css={styles.formTitle}>{__('What do you want to export', 'tutor')}</div>
          <div>
            {getExportableContentQuery.isLoading ? (
              <div>{__('Loading...', 'tutor')}</div>
            ) : (
              JSON.stringify(getExportableContentQuery.data, null, 2)
            )}
          </div>
        </div>
      </div>
    </BasicModalWrapper>
  );
};

export default ExportModal;

const styles = {
  header: css`
    height: 64px;
    width: 100%;
    ${styleUtils.display.flex()}
    justify-content: space-between;
    align-items: center;
    padding-inline: 104px;
  `,
  headerTitle: css`
    ${styleUtils.display.flex()}
    align-items: center;
    gap: ${spacing[4]};
    ${typography.heading6('medium')}
    color: ${colorTokens.text.brand};
  `,
  wrapper: css`
    height: 760px;
    padding: ${spacing[32]} 107px ${spacing[32]} 107px;
    background-color: ${colorTokens.surface.courseBuilder};
    border-top: 1px solid ${colorTokens.stroke.divider};
  `,
  formWrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[12]};
    padding: ${spacing[16]} ${spacing[20]};
    border-radius: ${borderRadius.card};
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.divider};
  `,
  formTitle: css`
    ${typography.caption()};
    color: ${colorTokens.text.title};
  `,
};
