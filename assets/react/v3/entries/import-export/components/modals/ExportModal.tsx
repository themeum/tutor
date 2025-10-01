import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Logo from '@TutorShared/components/Logo';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import { type ModalProps } from '@TutorShared/components/modals/Modal';

import { Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { type Course } from '@TutorShared/services/course';
import { styleUtils } from '@TutorShared/utils/style-utils';

import ExportInitialState from '@ImportExport/components/modals/import-export-states/ExportInitialState';
import ImportExportCompletedState from '@ImportExport/components/modals/import-export-states/ImportExportCompletedState';
import ImportExportProgressState from '@ImportExport/components/modals/import-export-states/ImportExportProgressState';
import {
  defaultExportFormData,
  type ExportFormData,
  type ImportExportContentResponseBase,
  type ImportExportModalState,
} from '@ImportExport/services/import-export';

import CourseListModal from '@TutorShared/components/modals/CourseListModal';
import { tutorConfig } from '@TutorShared/config/config';
import { type ExportableContent, useExportableContentQuery } from '@TutorShared/services/import-export';
import { type Collection } from '@TutorShared/utils/types';
import CollectionListModal from './CollectionList';

interface ExportModalProps extends ModalProps {
  onClose: () => void;
  onExport: ({ data, exportableContent }: { data: ExportFormData; exportableContent: ExportableContent[] }) => void;
  currentStep: ImportExportModalState;
  onDownload?: (fileName: string) => void;
  progress: number;
  fileName?: string;
  fileSize?: number | string;
  message?: string;
  failedMessage?: string;
  completedContents?: ImportExportContentResponseBase['completed_contents'];
  collection?: Collection;
}

interface BulkSelectionFormData {
  courses: Course[];
  'course-bundle': Course[];
  content_bank: Collection[];
}

const isTutorPro = !!tutorConfig.tutor_pro_url;

const ExportModal = ({
  onClose,
  onExport,
  currentStep,
  onDownload,
  progress,
  fileName,
  fileSize,
  message = '',
  failedMessage = '',
  completedContents,
  collection,
}: ExportModalProps) => {
  const form = useFormWithGlobalError<ExportFormData>({
    defaultValues: {
      ...defaultExportFormData,
      content_bank: collection ? true : defaultExportFormData.content_bank,
    },
  });

  const bulkSelectionForm = useFormWithGlobalError<BulkSelectionFormData>({
    defaultValues: {
      courses: [],
      'course-bundle': [],
      content_bank: collection ? [collection] : [],
    },
  });

  const getExportableContentQuery = useExportableContentQuery({
    course_ids: bulkSelectionForm.getValues('courses').map((course) => course.id),
  });
  const exportableContent = isTutorPro
    ? getExportableContentQuery.data
    : ([
        {
          key: 'courses',
          label: __('Courses', 'tutor'),
          contents: [
            {
              label: __('Lessons', 'tutor'),
              key: 'lesson',
            },
            {
              label: __('Quizzes', 'tutor'),
              key: 'tutor_quiz',
            },
            {
              label: __('Assignments', 'tutor'),
              key: 'tutor_assignments',
            },
            {
              label: __('Attachments', 'tutor'),
              key: 'attachments',
            },
          ],
        },
        {
          key: 'course-bundle',
          label: __('Bundles', 'tutor'),
          contents: [],
        },
        {
          key: 'content_bank',
          label: __('Content Bank', 'tutor'),
          contents: [],
        },
        {
          key: 'settings',
          label: __('Settings', 'tutor'),
          contents: [],
        },
        {
          key: 'keep_media_files',
          label: __('Keep Media Files', 'tutor'),
          contents: [],
        },
        {
          key: 'keep_user_data',
          label: __('Keep User Data', 'tutor'),
          contents: [],
        },
      ] as ExportableContent[]);

  const resetBulkSelection = (type: 'courses' | 'course-bundle' | 'content_bank') => {
    if (type === 'courses') {
      bulkSelectionForm.reset({
        courses: [],
        'course-bundle': bulkSelectionForm.getValues('course-bundle'),
        content_bank: bulkSelectionForm.getValues('content_bank'),
      });
    }

    if (type === 'course-bundle') {
      bulkSelectionForm.reset({
        courses: bulkSelectionForm.getValues('courses'),
        'course-bundle': [],
        content_bank: bulkSelectionForm.getValues('content_bank'),
      });
    }

    if (type === 'content_bank') {
      bulkSelectionForm.reset({
        courses: bulkSelectionForm.getValues('courses'),
        'course-bundle': bulkSelectionForm.getValues('course-bundle'),
        content_bank: [],
      });
    }
  };

  const getContentIds = (data: ExportableContent[], key: string): number[] => {
    const contentItem = data?.find((item) => item.key === key);
    return contentItem?.ids || [];
  };

  useEffect(() => {
    if (currentStep === 'progress') {
      window.onbeforeunload = () => true;
    }

    return () => {
      window.onbeforeunload = null;
    };
  }, [currentStep]);

  useEffect(() => {
    if (!getExportableContentQuery.isSuccess || !getExportableContentQuery.data) {
      return;
    }

    const data = getExportableContentQuery.data;
    const courseIds = getContentIds(data, 'courses');
    const bundleIds = getContentIds(data, 'course-bundle');
    const collectionIds = getContentIds(data, 'content_bank');

    form.setValue('courses__ids', courseIds);
    form.setValue('course-bundle__ids', bundleIds);
    form.setValue('content_bank__ids', collectionIds);
  }, [getExportableContentQuery.isSuccess, getExportableContentQuery.data, form]);

  const handleClose = () => {
    form.reset();
    onClose();
  };

  const componentMapping = {
    courses: {
      modal: {
        component: CourseListModal,
        props: {
          title: __('Select Courses', 'tutor'),
          type: 'courses',
          form: bulkSelectionForm,
        },
      },
      bulkSelectionButtonLabel:
        bulkSelectionForm.getValues('courses').length > 0
          ? __('Edit Selected Courses', 'tutor')
          : __('Select Specific Courses', 'tutor'),
    },
    'course-bundle': {
      modal: {
        component: CourseListModal,
        props: {
          title: __('Select Bundles', 'tutor'),
          type: 'course-bundle',
          form: bulkSelectionForm,
        },
      },
      bulkSelectionButtonLabel:
        bulkSelectionForm.getValues('course-bundle').length > 0
          ? __('Edit Selected Bundles', 'tutor')
          : __('Select Specific Bundles', 'tutor'),
    },
    content_bank: {
      modal: {
        component: CollectionListModal,
        props: {
          title: __('Select Collections', 'tutor'),
          form: bulkSelectionForm,
          selectedCollectionFromContentBank: collection,
        },
      },
      bulkSelectionButtonLabel:
        bulkSelectionForm.getValues('content_bank').length > 0
          ? __('Edit Selected Content Bank Items', 'tutor')
          : __('Select Specific Content Bank Items', 'tutor'),
    },
  };

  const handleExport = form.handleSubmit((data) => {
    const { courses, 'course-bundle': bundles, content_bank: collections } = bulkSelectionForm.getValues();
    onExport?.({
      data: {
        ...data,
        ...(collection?.ID
          ? {
              content_bank: true,
            }
          : {
              content_bank: data['content_bank'],
            }),
        courses__ids: courses.length > 0 ? courses.map((course) => course.id) : form.getValues('courses__ids'),
        'course-bundle__ids':
          bundles.length > 0 ? bundles.map((bundle) => bundle.id) : form.getValues('course-bundle__ids'),
        content_bank__ids:
          collections.length > 0 ? collections.map((collection) => collection.ID) : form.getValues('content_bank__ids'),
      },
      exportableContent: getExportableContentQuery.data || [],
    });
  });

  const modalContent = {
    initial: (
      <ExportInitialState
        form={form}
        bulkSelectionForm={bulkSelectionForm}
        exportableContent={exportableContent || []}
        isLoading={getExportableContentQuery.isLoading}
        componentMapping={componentMapping}
        resetBulkSelection={resetBulkSelection}
      />
    ),
    progress: <ImportExportProgressState progress={progress} message={message} type="export" />,
    success: (
      <ImportExportCompletedState
        state="success"
        exportFileName={fileName}
        fileSize={fileSize}
        message={message}
        failedMessage={failedMessage}
        completedContents={completedContents}
        onDownload={onDownload}
        onClose={handleClose}
        type="export"
      />
    ),
    error: (
      <ImportExportCompletedState
        state="error"
        message={message}
        failedMessage={failedMessage}
        onClose={handleClose}
        type="export"
      />
    ),
  };

  const EXCLUDED_KEYS = ['keep_media_files', 'keep_user_data'];

  const disableExportButton = () => {
    const formValues = form.getValues();

    const mainContentTypesSelected = Object.entries(formValues).some(([key, value]) => {
      if (!key.includes('__') && !EXCLUDED_KEYS.includes(key)) {
        return value === true;
      }
      return false;
    });

    return !mainContentTypesSelected;
  };

  return (
    <BasicModalWrapper
      onClose={handleClose}
      maxWidth={currentStep === 'initial' ? 823 : 500}
      isCloseAble={currentStep !== 'progress'}
      entireHeader={
        <Show when={currentStep === 'initial'} fallback={<>&nbsp;</>}>
          <div css={styles.header}>
            <div css={styles.headerTitle}>
              <Logo wrapperCss={styles.logo} />
              <span>{__('Exporter', 'tutor')}</span>
            </div>
            <div>
              <Button
                variant="primary"
                size="small"
                icon={<SVGIcon name="export" width={24} height={24} />}
                disabled={currentStep === 'progress' || disableExportButton() || getExportableContentQuery.isLoading}
                onClick={handleExport}
              >
                {__('Export', 'tutor')}
              </Button>
            </div>
          </div>
        </Show>
      }
    >
      {modalContent[currentStep]}
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
    padding-inline: 88px;

    ${Breakpoint.tablet} {
      padding-inline: ${spacing[8]} ${spacing[36]};
    }
  `,
  headerTitle: css`
    ${styleUtils.display.flex()}
    align-items: center;
    gap: ${spacing[4]};
    ${typography.heading6('medium')}
    color: ${colorTokens.text.brand};
  `,
  logo: css`
    padding-left: 0;

    ${Breakpoint.smallTablet} {
      padding-left: 0;
    }
  `,
};
