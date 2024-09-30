import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useEffect, useState } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import Table from '@Molecules/Table';
import type { Column } from '@Molecules/Table';

import FormInputWithContent from '@Components/fields/FormInputWithContent';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';

import Checkbox from '@Atoms/CheckBox';
import { DateFormats } from '@Config/constants';
import { colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { type ContentType, type ID, useGetH5PLessonContentsQuery } from '@CourseBuilderServices/curriculum';
import { type H5PContent, useGetH5PQuizContentsQuery } from '@CourseBuilderServices/quiz';
import { useDebounce } from '@Hooks/useDebounce';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { styleUtils } from '@Utils/style-utils';

interface H5PContentListModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  onAddContent: (contents: H5PContent[]) => void;
  contentType: ContentType;
  addedContentIds: ID[];
}

const H5PContentListModal = ({
  title,
  closeModal,
  onAddContent,
  contentType,
  addedContentIds = [],
}: H5PContentListModalProps) => {
  const form = useFormWithGlobalError<{
    search: string;
  }>({
    defaultValues: {
      search: '',
    },
  });
  const search = useDebounce(form.watch('search'), 300);
  const getH5PQuizzesQuery = useGetH5PQuizContentsQuery(search, contentType);
  const getH5PContentsQuery = useGetH5PLessonContentsQuery(search, contentType);
  const [selectedContents, setSelectedContents] = useState<H5PContent[]>([]);

  const content = contentType === 'tutor_h5p_quiz' ? getH5PQuizzesQuery.data : getH5PContentsQuery.data;
  const filteredContent = content?.output.filter((item) => !addedContentIds.includes(String(item.id)));

  const columns: Column<H5PContent>[] = [
    {
      Header: (
        <div data-index css={styles.tableLabel}>
          {filteredContent?.length ? (
            <Checkbox
              onChange={(isChecked) => {
                if (isChecked) {
                  setSelectedContents(
                    filteredContent?.filter((item) => !addedContentIds.includes(String(item.id))) || [],
                  );
                } else {
                  setSelectedContents([]);
                }
              }}
              checked={
                selectedContents.length ===
                (filteredContent?.filter((item) => !addedContentIds.includes(String(item.id))) || []).length
              }
              isIndeterminate={
                selectedContents.length > 0 &&
                selectedContents.length <
                  (filteredContent?.filter((item) => !addedContentIds.includes(String(item.id))) || []).length
              }
            />
          ) : (
            '#'
          )}
        </div>
      ),
      Cell: (item, index) => {
        return (
          <div css={typography.caption()}>
            <Checkbox
              onChange={(isChecked) => {
                if (isChecked) {
                  setSelectedContents([...selectedContents, item]);
                } else {
                  setSelectedContents(selectedContents.filter((content) => content.id !== item.id));
                }
              }}
              checked={
                selectedContents.map((content) => content.id).includes(item.id) &&
                !addedContentIds.includes(String(item.id))
              }
            />
          </div>
        );
      },
    },
    {
      Header: <div css={styles.tableLabel}>{__('Title', 'tutor')}</div>,
      Cell: (item) => {
        return <div css={styles.title}>{item.title}</div>;
      },
    },
    {
      Header: <div css={styles.tableLabel}>{__('Content Type', 'tutor')}</div>,
      Cell: (item) => {
        return <div css={typography.caption()}>{item.content_type}</div>;
      },
    },
    {
      Header: <div css={styles.tableLabel}>{__('Created At', 'tutor')}</div>,
      Cell: (item) => {
        return (
          <div css={typography.caption()}>
            {format(new Date(item.updated_at), DateFormats.yearMonthDayHourMinuteSecond)}
          </div>
        );
      },
    },
  ];

  useEffect(() => {
    document.body.style.overflow = 'hidden';
    return () => {
      document.body.style.overflow = 'auto';
    };
  }, []);

  return (
    <BasicModalWrapper title={title} onClose={() => closeModal({ action: 'CLOSE' })}>
      <div css={styles.modalWrapper}>
        <div css={styles.searchWrapper}>
          <Controller
            control={form.control}
            name="search"
            render={(controllerProps) => (
              <FormInputWithContent
                {...controllerProps}
                placeholder={__('Search by title', 'tutor')}
                showVerticalBar={false}
                content={<SVGIcon name="search" width={24} height={24} />}
              />
            )}
          />
        </div>
        <div css={styles.tableWrapper}>
          <Table
            columns={columns}
            data={filteredContent || []}
            loading={getH5PQuizzesQuery.isLoading || getH5PContentsQuery.isLoading}
          />
        </div>

        <Show when={filteredContent?.length}>
          <div css={styles.footer}>
            <Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
              {__('Cancel', 'tutor')}
            </Button>
            <Button
              type="submit"
              size="small"
              variant="primary"
              onClick={() => {
                onAddContent(selectedContents);
                closeModal({ action: 'CONFIRM' });
              }}
            >
              {__('Add', 'tutor')}
            </Button>
          </div>
        </Show>
      </div>
    </BasicModalWrapper>
  );
};

export default H5PContentListModal;

const styles = {
  modalWrapper: css`
    width: 920px;
  `,
  searchWrapper: css`
    display: flex;
    padding: ${spacing[20]};
  `,
  tableWrapper: css`
    max-height: calc(100vh - 350px);
    overflow: auto;

    tr {
      td:first-of-type {
        padding-left: ${spacing[20]};
      }

      td:last-of-type {
        padding-right: ${spacing[20]};
      }
    }
  `,
  tableLabel: css`
    ${typography.body('medium')};
    text-align: left;
    color: ${colorTokens.text.primary};

    &[data-index] {
      padding-left: ${spacing[4]};
    }
  `,
  title: css`
    ${styleUtils.text.ellipsis(2)}
    width: 100%;
    text-align: left;
    ${typography.caption()};
    min-width: 340px;
    max-width: 400px;
  `,
  footer: css`
    box-shadow: ${shadow.dividerTop};
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: end;
    gap: ${spacing[16]};
    padding-inline: ${spacing[16]};
  `,
};
