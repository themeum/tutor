import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useState } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import type { Column } from '@TutorShared/molecules/Table';
import Table from '@TutorShared/molecules/Table';

import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';

import Checkbox from '@TutorShared/atoms/CheckBox';
import { DateFormats } from '@TutorShared/config/constants';
import { colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { useDebounce } from '@TutorShared/hooks/useDebounce';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { useGetH5PLessonContentsQuery, useGetH5PQuizContentsQuery } from '@TutorShared/services/h5p';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type H5PContent, type ID, type TopicContentType } from '@TutorShared/utils/types';

interface H5PContentListModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  onAddContent: (contents: H5PContent[]) => void;
  contentType: TopicContentType;
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
      Cell: (item) => {
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
      Header: <div css={styles.tableLabel}>{__('Title', __TUTOR_TEXT_DOMAIN__)}</div>,
      Cell: (item) => {
        return <div css={styles.title}>{item.title}</div>;
      },
    },
    {
      Header: <div css={styles.tableLabel}>{__('Content Type', __TUTOR_TEXT_DOMAIN__)}</div>,
      Cell: (item) => {
        return <div css={typography.caption()}>{item.content_type}</div>;
      },
    },
    {
      Header: <div css={styles.tableLabel}>{__('Created At', __TUTOR_TEXT_DOMAIN__)}</div>,
      Cell: (item) => {
        return (
          <div css={typography.caption()}>
            {format(new Date(item.updated_at), DateFormats.yearMonthDayHourMinuteSecond)}
          </div>
        );
      },
    },
  ];

  return (
    <BasicModalWrapper
      title={
        /* translators: %s is the number of selected items */
        selectedContents.length > 0 ? sprintf(__('%s selected', __TUTOR_TEXT_DOMAIN__), selectedContents.length) : title
      }
      onClose={() => closeModal({ action: 'CLOSE' })}
      maxWidth={920}
    >
      <div css={styles.searchWrapper}>
        <Controller
          control={form.control}
          name="search"
          render={(controllerProps) => (
            <FormInputWithContent
              {...controllerProps}
              placeholder={__('Search by title', __TUTOR_TEXT_DOMAIN__)}
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
            {__('Cancel', __TUTOR_TEXT_DOMAIN__)}
          </Button>
          <Button
            type="submit"
            size="small"
            variant="primary"
            onClick={() => {
              onAddContent(selectedContents);
              closeModal({ action: 'CONFIRM' });
            }}
            disabled={!selectedContents.length}
          >
            {__('Add', __TUTOR_TEXT_DOMAIN__)}
          </Button>
        </div>
      </Show>
    </BasicModalWrapper>
  );
};

export default H5PContentListModal;

const styles = {
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
