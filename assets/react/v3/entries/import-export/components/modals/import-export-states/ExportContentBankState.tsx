import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import { type BulkSelectionFormData } from '@ImportExport/services/import-export';
import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { type FormWithGlobalErrorType } from '@TutorShared/hooks/useFormWithGlobalError';
import { styleUtils } from '@TutorShared/utils/style-utils';
import CollectionListTable from '../CollectionList/CollectionListTable';

interface ExportContentBankStateProps {
  bulkSelectionForm: FormWithGlobalErrorType<BulkSelectionFormData>;
}

const ExportContentBankState = ({ bulkSelectionForm }: ExportContentBankStateProps) => {
  return (
    <div css={styles.wrapper}>
      <div css={styles.formWrapper}>
        <div css={styles.formTitle}>{__('Select content bank items to export', 'tutor')}</div>

        <CollectionListTable form={bulkSelectionForm} />
      </div>
    </div>
  );
};

export default ExportContentBankState;

const styles = {
  wrapper: css`
    height: calc(100vh - 140px);
    max-height: 680px;
    padding: ${spacing[32]} 107px ${spacing[32]} 107px;
    background-color: ${colorTokens.surface.courseBuilder};
    border-top: 1px solid ${colorTokens.stroke.divider};

    ${Breakpoint.tablet} {
      padding: ${spacing[24]} ${spacing[16]};
      height: calc(100vh - 160px);
    }
  `,
  formWrapper: css`
    height: 100%;
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
