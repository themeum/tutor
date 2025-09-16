import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Table, { type Column } from '@TutorShared/molecules/Table';

import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import { type ModalProps } from '@TutorShared/components/modals/Modal';

import { type ImportContentResponse } from '@ImportExport/services/import-export';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { getObjectEntries } from '@TutorShared/utils/util';

interface ImportErrorListModalProps extends ModalProps {
  errors: ImportContentResponse['errors'];
}

const ImportErrorListModal = ({ errors, closeModal }: ImportErrorListModalProps) => {
  const errorTypeTextMap = {
    topics: __('Topic', 'tutor'),
    lesson: __('Lesson', 'tutor'),
    tutor_quiz: __('Quiz', 'tutor'),
    tutor_assignments: __('Assignment', 'tutor'),
    'cb-question': __('Content Bank Question', 'tutor'),
    'cb-lesson': __('Content Bank Lesson', 'tutor'),
    'cb-assignment': __('Content Bank Assignment', 'tutor'),
  };

  const columns: Column<string>[] = [
    {
      Header: '#',
      Cell: (_, index) => <span css={styles.index}>{index + 1}</span>,
      width: '50px',
    },
    {
      Header: __('Title', 'tutor'),
      Cell: (error) => <span>{error}</span>,
    },
  ];

  const renderErrorList = (title: string, errors: string[]) => {
    if (!errors.length) return null;

    return (
      <div css={styles.errors}>
        <h4 css={typography.body('medium')}>{title}</h4>
        <Table columns={columns} data={errors} isBordered isRounded headerHeight={40} />
      </div>
    );
  };

  return (
    <BasicModalWrapper
      title={__('Import Errors', 'tutor')}
      subtitle={__('Error occurred in the following items', 'tutor')}
      onClose={closeModal}
      icon={<SVGIcon name="warning" height={24} width={24} />}
      maxWidth={700}
    >
      <div css={styles.wrapper}>
        {getObjectEntries(errors || {}).map(([errorType, errorList = []]) => {
          const key = errorType as keyof typeof errorTypeTextMap;
          const title = errorTypeTextMap[key] ?? errorType;
          return renderErrorList(title, errorList);
        })}
      </div>
    </BasicModalWrapper>
  );
};

export default ImportErrorListModal;

const styles = {
  wrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[20]};
    padding: ${spacing[16]} ${spacing[24]} ${spacing[24]} ${spacing[24]};
    max-height: 90vh;
    ${styleUtils.overflowYAuto}

    table {
      th {
        ${typography.caption('medium')};
      }
      td {
        padding: ${spacing[8]} ${spacing[12]};
        ${typography.caption()};
      }
    }
  `,
  index: css`
    text-align: center;
    color: ${colorTokens.text.hints};
  `,
  errors: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
  `,
};
