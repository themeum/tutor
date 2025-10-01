import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useState } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { useToast } from '@TutorShared/atoms/Toast';
import { UploadButton } from '@TutorShared/molecules/FileUploader';

import { hasAnyCourseWithChildren } from '@ImportExport/utils/utils';
import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import FormRadioGroup from '@TutorShared/components/fields/FormRadioGroup';
import { tutorConfig } from '@TutorShared/config/config';
import { Addons } from '@TutorShared/config/constants';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { useDebounce } from '@TutorShared/hooks/useDebounce';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import useIntersectionObserver from '@TutorShared/hooks/useIntersectionObserver';
import { useGetCollectionsInfinityQuery } from '@TutorShared/services/content-bank';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type Collection } from '@TutorShared/utils/types';
import { formatBytes, isAddonEnabled } from '@TutorShared/utils/util';

interface ImportInitialStateProps {
  files: File[];
  currentStep: string;
  onClose: () => void;
  onImport: ({
    file,
    collectionId,
  }: {
    file: File;
    collectionId?: number; // only needed if importing into Content Bank
  }) => void;
}

interface ImportForm {
  files: File[];
  importIntoContentBank: boolean;
  collectionSearch: string;
  collectionId: string;
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const readJsonFile = (file: File): Promise<any> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();

    reader.onload = (event) => {
      try {
        const content = event.target?.result as string;
        const jsonData = JSON.parse(content);
        resolve(jsonData);
      } catch {
        reject(new Error(__('Invalid JSON file format', 'tutor')));
      }
    };

    reader.onerror = () => {
      reject(new Error(__('Failed to read file', 'tutor')));
    };

    reader.readAsText(file);
  });
};

const isTutorPro = !!tutorConfig.tutor_pro_url;

const ImportInitialState = ({ files: propsFiles, currentStep, onClose, onImport }: ImportInitialStateProps) => {
  const [isReadingFile, setIsReadingFile] = useState(false);
  const [isFileValid, setIsFileValid] = useState(true);
  const { showToast } = useToast();
  const [hasContent, setHasContent] = useState({
    settings: false,
    courseContent: false,
  });

  const form = useFormWithGlobalError<ImportForm>({
    defaultValues: {
      files: propsFiles,
      importIntoContentBank: false,
      collectionSearch: '',
      collectionId: '',
    },
  });
  const searchTerm = form.watch('collectionSearch');
  const search = useDebounce(searchTerm, 300);
  const isContentBankSelectionEnabled = form.watch('importIntoContentBank');

  const { intersectionEntry, intersectionElementRef } = useIntersectionObserver<HTMLDivElement>({
    dependencies: [isContentBankSelectionEnabled],
  });
  const getCollectionListQuery = useGetCollectionsInfinityQuery({
    search,
    page: 1,
    per_page: 10,
    isEnabled: !!isContentBankSelectionEnabled,
  });

  const files = form.watch('files');

  useEffect(() => {
    if (files.length === 0) {
      return;
    }

    if (files[0].type !== 'application/json') {
      return;
    }

    setIsReadingFile(true);
    readJsonFile(files[0])
      .then((data) => {
        const hasSettings = data?.data.find((item: { content_type: string }) => item.content_type === 'settings');
        const isCourseContentAvailable = hasAnyCourseWithChildren(data);

        setIsReadingFile(false);
        setHasContent((prev) => ({
          ...prev,
          settings: hasSettings || false,
          courseContent: isCourseContentAvailable || false,
        }));
        form.setValue('files', files);
        setIsFileValid(true);
      })
      .catch(() => {
        setIsReadingFile(false);
        setIsFileValid(false);
      })
      .finally(() => {
        setIsReadingFile(false);
      });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (intersectionEntry?.isIntersecting && getCollectionListQuery.hasNextPage) {
      getCollectionListQuery.fetchNextPage();
    }
  }, [intersectionEntry?.isIntersecting, getCollectionListQuery, isContentBankSelectionEnabled]);

  const handleUpload = (uploadedFiles: File[]) => {
    if (uploadedFiles.length) {
      form.setValue('files', uploadedFiles);
    }
  };

  const handleUploadError = (errorMessages: string[]) => {
    showToast({
      message: errorMessages.join(', '),
      type: 'danger',
    });
  };

  const file = files[0];
  const collections = useMemo(() => {
    return (
      getCollectionListQuery.data?.pages?.reduce((acc, page) => {
        if (page.data && Array.isArray(page.data)) {
          return [...acc, ...page.data];
        }
        return acc;
      }, [] as Collection[]) || []
    );
  }, [getCollectionListQuery.data]);

  const collectionOptions = useMemo(() => {
    return collections.map((collection) => ({
      label: collection.post_title,
      value: String(collection.ID),
      labelCss: styles.collectionItem,
    }));
  }, [collections]);

  if (files.length === 0) {
    return null;
  }

  return (
    <>
      <div css={styles.selectedInfo}>
        <div css={styles.fileInfo}>
          <div css={styles.progressHeader}>
            <div css={typography.small()}>
              {isReadingFile ? __('Reading file...', 'tutor') : __('Selected', 'tutor')}
            </div>

            <Show
              when={isReadingFile}
              fallback={
                <Show when={isFileValid}>
                  <div css={styles.progressCount}>{__('Ready to import', 'tutor')}</div>
                </Show>
              }
            >
              <div css={styles.progressCount}>{__('Please wait...', 'tutor')}</div>
            </Show>
          </div>

          <div css={styles.file}>
            <div css={styles.fileIcon}>
              <SVGIcon name="attachmentLine" width={24} height={24} />
            </div>
            <div css={styles.fileRight}>
              <div css={styles.fileDetails}>
                <div css={styles.fileName}>{file.name}</div>
                <div css={styles.fileSize}>{formatBytes(file.size)}</div>
              </div>

              <div>
                <UploadButton
                  data-cy="replace-file"
                  variant="tertiary"
                  size="small"
                  onUpload={handleUpload}
                  onError={handleUploadError}
                  acceptedTypes={isTutorPro ? ['.json', '.zip'] : ['.json']}
                >
                  {__('Replace', 'tutor')}
                </UploadButton>
              </div>
            </div>
          </div>
        </div>

        {/* @TODO: wii be removed later `&& hasContent.courseContent}` */}
        <Show when={isTutorPro && isAddonEnabled(Addons.CONTENT_BANK)}>
          <div css={styles.contentBank}>
            <Controller
              control={form.control}
              name="importIntoContentBank"
              render={(controllerProps) => (
                <FormCheckbox
                  {...controllerProps}
                  label={__('Import items into Content Bank without creating courses', 'tutor')}
                />
              )}
            />

            <Show when={isContentBankSelectionEnabled}>
              <div css={styles.collectionListWrapper}>
                <div css={styles.collectionListHeader}>
                  <Controller
                    control={form.control}
                    name="collectionSearch"
                    render={(controllerProps) => (
                      <FormInputWithContent
                        {...controllerProps}
                        placeholder={__('Search...', 'tutor')}
                        content={<SVGIcon name="search" width={24} height={24} />}
                        contentPosition="left"
                        showVerticalBar={false}
                      />
                    )}
                  />
                </div>

                <div css={styles.collectionList}>
                  <Show
                    when={!getCollectionListQuery.isLoading && collectionOptions.length > 0}
                    fallback={
                      <Show
                        when={getCollectionListQuery.isLoading}
                        fallback={
                          <Show when={collections.length === 0}>
                            <div css={styles.notFound}>{__('No collections found', 'tutor')}</div>
                          </Show>
                        }
                      >
                        <LoadingSection />
                      </Show>
                    }
                  >
                    <Controller
                      control={form.control}
                      name="collectionId"
                      render={(controllerProps) => <FormRadioGroup {...controllerProps} options={collectionOptions} />}
                    />
                  </Show>

                  <div ref={intersectionElementRef} />
                </div>
              </div>
            </Show>
          </div>
        </Show>

        <Show when={!isFileValid}>
          <div css={styles.alert}>
            <SVGIcon name="warning" width={40} height={40} />
            <p>{__('WARNING! Invalid file. Please upload a valid JSON file and try again.', 'tutor')}</p>
          </div>
        </Show>

        <div css={styles.alert}>
          <SVGIcon name="infoFill" width={40} height={40} />
          <p>
            {isContentBankSelectionEnabled
              ? __('Note: Only lessons, quizzes, and assignments will be uploaded to the Content Bank.', 'tutor')
              : __('WARNING! This will overwrite all existing settings, please proceed with caution.', 'tutor')}
          </p>
        </div>
      </div>
      <div css={styles.footer}>
        <div css={styles.actionButtons}>
          <Button variant="text" size="small" onClick={onClose}>
            {__('Cancel', 'tutor')}
          </Button>
          <Button
            data-cy="import-csv"
            disabled={files.length === 0 || isReadingFile || !isFileValid || (!isTutorPro && !hasContent.settings)}
            variant="primary"
            size="small"
            loading={isReadingFile || currentStep === 'progress'}
            onClick={async () =>
              onImport({
                file: files[0],
                collectionId: isContentBankSelectionEnabled ? Number(form.watch('collectionId')) : undefined,
              })
            }
          >
            {__('Import', 'tutor')}
          </Button>
        </div>
      </div>
    </>
  );
};

export default ImportInitialState;

const styles = {
  progressHeader: css`
    ${styleUtils.display.flex()};
    justify-content: space-between;
  `,
  progressCount: css`
    ${styleUtils.flexCenter()};
    ${typography.tiny('bold')};
    padding: ${spacing[2]} ${spacing[8]};
    background-color: ${colorTokens.background.status.success};
    color: ${colorTokens.text.success};
    border-radius: ${borderRadius[12]};
  `,
  selectedInfo: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[20]};
    padding: ${spacing[20]} ${spacing[16]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  fileInfo: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[10]};
  `,
  file: css`
    ${styleUtils.display.flex()};
    height: 64px;
    border: 1px solid ${colorTokens.stroke.divider};
    overflow: hidden;
    border-radius: ${borderRadius[6]};
    width: 100%;
  `,
  fileIcon: css`
    background-color: #f7f7f7;
    ${styleUtils.flexCenter()};
    width: 64px;
    height: 100%;
    border-right: 1px solid ${colorTokens.stroke.divider};
    flex-shrink: 0;

    svg {
      color: ${colorTokens.icon.disable.background};
    }
  `,
  fileRight: css`
    flex-grow: 1;
    ${styleUtils.display.flex()};
    justify-content: space-between;
    align-items: center;
    padding: ${spacing[10]} ${spacing[16]} ${spacing[10]} ${spacing[20]};
  `,
  fileDetails: css`
    flex-grow: 1;
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  fileName: css`
    ${typography.small('medium')};
    color: ${colorTokens.text.subdued};
    ${styleUtils.text.ellipsis(1)};
  `,
  fileSize: css`
    ${typography.tiny()};
    color: ${colorTokens.text.hints};
  `,
  alert: css`
    ${styleUtils.display.flex()};
    align-items: flex-start;
    gap: ${spacing[8]};
    padding: ${spacing[20]};
    border-radius: ${borderRadius[6]};
    background-color: ${colorTokens.background.status.warning};

    svg {
      color: ${colorTokens.icon.warning};
      flex-shrink: 0;
    }

    p {
      ${typography.caption()};
      color: ${colorTokens.text.warning};
    }
  `,
  footer: css`
    ${styleUtils.display.flex()};
    align-items: center;
    justify-content: flex-end;
    padding: ${spacing[12]} ${spacing[16]};
  `,
  actionButtons: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};
  `,
  contentBank: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
  `,
  collectionListWrapper: css`
    ${styleUtils.display.flex('column')};
    border-radius: ${borderRadius[8]};
    border: 1px solid ${colorTokens.stroke.divider};
  `,
  collectionListHeader: css`
    padding: ${spacing[12]};
  `,
  collectionList: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
    max-height: 200px;
    ${styleUtils.overflowYAuto};
  `,
  collectionItem: css`
    padding: ${spacing[6]} ${spacing[16]};
  `,
  notFound: css`
    padding: ${spacing[12]};
    text-align: center;
    color: ${colorTokens.text.subdued};
  `,
};
