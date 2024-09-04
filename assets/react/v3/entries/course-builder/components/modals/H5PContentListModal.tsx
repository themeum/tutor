import Button from '@Atoms/Button';
import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { DateFormats } from '@Config/constants';
import { colorTokens } from '@Config/styles';
import { typography } from '@Config/typography';
import { type H5PContent, useGetH5PQuizContentsQuery } from '@CourseBuilderServices/quiz';
import type { Column } from '@Molecules/Table';
import Table from '@Molecules/Table';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';

interface H5PContentListModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  onAddContent: (content: H5PContent) => void;
}

const H5PContentListModal = ({ title, closeModal, onAddContent }: H5PContentListModalProps) => {
  const getH5PContentsQuery = useGetH5PQuizContentsQuery();

  if (getH5PContentsQuery.isLoading) {
    return <LoadingOverlay />;
  }

  const columns: Column<H5PContent>[] = [
    {
      Header: <div css={styles.tableLabel}>{__('Title', 'tutor')}</div>,
      Cell: (item) => {
        return <div css={styles.title}>{item.title}</div>;
      },
    },
    {
      Header: <div css={styles.tableLabel}>{__('Content Type', 'tutor')}</div>,
      Cell: (item) => {
        return <div>{item.content_type}</div>;
      },
    },
    {
      Header: <div css={styles.tableLabel}>{__('Author', 'tutor')}</div>,
      Cell: (item) => {
        return <div>{item.user_name}</div>;
      },
    },
    {
      Header: <div css={styles.tableLabel}>{__('Created At', 'tutor')}</div>,
      Cell: (item) => {
        return <div>{format(new Date(item.updated_at), DateFormats.yearMonthDayHourMinuteSecond)}</div>;
      },
    },
    {
      Header: <div css={styles.tableLabel}>{__('Actions', 'tutor')}</div>,
      Cell: (item) => {
        return (
          <div>
            <Button
              size="small"
              variant="secondary"
              onClick={() => {
                closeModal({ action: 'CONFIRM' });
                onAddContent(item);
              }}
            >
              {__('Add Content', 'tutor')}
            </Button>
          </div>
        );
      },
    },
  ];

  return (
    <BasicModalWrapper title={title} onClose={() => closeModal({ action: 'CLOSE' })}>
      <div css={styles.modalWrapper}>
        <div css={styles.tableWrapper}>
          <Table columns={columns} data={getH5PContentsQuery.data?.output || []} />
        </div>
      </div>
    </BasicModalWrapper>
  );
};

export default H5PContentListModal;

const styles = {
  modalWrapper: css`
    width: 720px;
  `,
  tableWrapper: css`
    max-height: calc(100vh - 350px);
    overflow: auto;

    tr {
      &:hover {
        &:hover {
          [data-button] {
            display: block;
          }
          [data-price] {
            display: none;
          }
        }
      }
    }
  `,
  tableLabel: css`
    text-align: left;
    ${typography.caption('semiBold')};
    color: ${colorTokens.text.subdued};
  `,
  title: css`
    text-align: left;
    ${typography.caption()};
  `,
};
