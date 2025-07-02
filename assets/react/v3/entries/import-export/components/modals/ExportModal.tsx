import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';

import CourseListModal from '@ImportExport/components/modals/CourseListModal';
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
  useExportableContentQuery,
  type ExportableContent,
  type ExportFormData,
  type ImportExportContentResponseBase,
  type ImportExportModalState,
} from '@ImportExport/services/import-export';

import { tutorConfig } from '@TutorShared/config/config';
import { type Collection } from '@TutorShared/utils/types';
import CollectionListModal from './CollectionList';
import ExportContentBankState from './import-export-states/ExportContentBankState';

interface ExportModalProps extends ModalProps {
  onClose: () => void;
  onExport: ({ data, exportableContent }: { data: ExportFormData; exportableContent: ExportableContent[] }) => void;
  currentStep: ImportExportModalState;
  onDownload?: (fileName: string) => void;
  progress: number;
  fileSize?: number;
  message?: string;
  completedContents?: ImportExportContentResponseBase['completed_contents'];
  isFromContentBank?: boolean;
}

interface BulkSelectionFormData {
  courses: Course[];
  'course-bundle': Course[];
  collections: Collection[];
}

const isTutorPro = !!tutorConfig.tutor_pro_url;

const ExportModal = ({
  onClose,
  onExport,
  currentStep,
  onDownload,
  progress,
  fileSize,
  message,
  completedContents,
  isFromContentBank,
}: ExportModalProps) => {
  const form = useFormWithGlobalError<ExportFormData>({
    defaultValues: defaultExportFormData,
  });

  const bulkSelectionForm = useFormWithGlobalError<BulkSelectionFormData>({
    defaultValues: {
      courses: [],
      'course-bundle': [],
      collections: [],
    },
  });

  const getExportableContentQuery = useExportableContentQuery();
  const exportableContent = isTutorPro
    ? getExportableContentQuery.data
    : ([
        {
          key: 'courses',
          label: 'Courses',
          contents: [
            {
              label: 'Lessons',
              key: 'lesson',
            },
            {
              label: 'Quizzes',
              key: 'tutor_quiz',
            },
            {
              label: 'Assignments',
              key: 'tutor_assignments',
            },
            {
              label: 'Attachments',
              key: 'attachments',
            },
          ],
        },
        {
          key: 'course-bundle',
          label: 'Bundles',
          contents: [],
        },
        {
          key: 'collections',
          label: 'Collections',
          contents: [],
        },
        {
          key: 'settings',
          label: 'Settings',
          contents: [],
        },
        {
          key: 'keep_media_files',
          label: 'Keep media files',
          contents: [],
        },
      ] as ExportableContent[]);

  const resetBulkSelection = (type: 'courses' | 'course-bundle' | 'collections') => {
    if (type === 'courses') {
      bulkSelectionForm.reset({
        courses: [],
        'course-bundle': bulkSelectionForm.getValues('course-bundle'),
        collections: bulkSelectionForm.getValues('collections'),
      });
    }

    if (type === 'course-bundle') {
      bulkSelectionForm.reset({
        courses: bulkSelectionForm.getValues('courses'),
        'course-bundle': [],
        collections: bulkSelectionForm.getValues('collections'),
      });
    }

    if (type === 'collections') {
      bulkSelectionForm.reset({
        courses: bulkSelectionForm.getValues('courses'),
        'course-bundle': bulkSelectionForm.getValues('course-bundle'),
        collections: [],
      });
    }
  };

  const getContentIds = (data: ExportableContent[], key: string): number[] => {
    const contentItem = data?.find((item) => item.key === key);
    return contentItem?.ids || [];
  };

  useEffect(() => {
    if (!getExportableContentQuery.isSuccess || !getExportableContentQuery.data) {
      return;
    }

    const data = getExportableContentQuery.data;
    const courseIds = getContentIds(data, 'courses');
    const bundleIds = getContentIds(data, 'course-bundle');
    const collectionIds = getContentIds(data, 'collections');

    form.setValue('courses__ids', courseIds);
    form.setValue('course-bundle__ids', bundleIds);
    form.setValue('collections__ids', collectionIds);
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
    collections: {
      modal: {
        component: CollectionListModal,
        props: {
          title: __('Select Collections', 'tutor'),
          form: bulkSelectionForm,
        },
      },
      bulkSelectionButtonLabel:
        bulkSelectionForm.getValues('collections').length > 0
          ? __('Edit Selected Collections', 'tutor')
          : __('Select Specific Collections', 'tutor'),
    },
  };

  const handleExport = form.handleSubmit((data) => {
    const { courses, 'course-bundle': bundles, collections } = bulkSelectionForm.getValues();
    onExport?.({
      data: {
        ...data,
        ...(isFromContentBank
          ? {
              collections: true,
            }
          : {
              collections: data.collections,
            }),
        courses__ids: courses.length > 0 ? courses.map((course) => course.id) : form.getValues('courses__ids'),
        'course-bundle__ids':
          bundles.length > 0 ? bundles.map((bundle) => bundle.id) : form.getValues('course-bundle__ids'),
        collections__ids:
          collections.length > 0 ? collections.map((collection) => collection.ID) : form.getValues('collections__ids'),
      },
      exportableContent: getExportableContentQuery.data || [],
    });
  });

  const modalContent = {
    initial: isFromContentBank ? (
      <ExportContentBankState bulkSelectionForm={bulkSelectionForm} />
    ) : (
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
        fileSize={fileSize}
        message={message}
        completedContents={completedContents}
        onDownload={onDownload}
        onClose={handleClose}
        type="export"
      />
    ),
    error: <ImportExportCompletedState state="error" message={message} onClose={handleClose} type="export" />,
  };

  const disableExportButton = () => {
    if (isFromContentBank) {
      return bulkSelectionForm.getValues('collections').length === 0;
    }

    return !Object.entries(form.getValues()).some(([key, value]) => {
      if (!key.includes('__')) {
        return value === true;
      }
      return false;
    });
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
