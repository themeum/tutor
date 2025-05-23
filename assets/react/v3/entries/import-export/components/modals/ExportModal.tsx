import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';

import CourseListModal from '@ImportExport/components/modals/CourseListModal';
import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Logo from '@TutorShared/components/Logo';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import { type ModalProps } from '@TutorShared/components/modals/Modal';

import {
  defaultExportFormData,
  useExportableContentQuery,
  type ExportContentResponse,
  type ExportFormData,
  type ImportExportModalState,
} from '@ImportExport/services/import-export';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { type Course } from '@TutorShared/services/course';
import { styleUtils } from '@TutorShared/utils/style-utils';

// Import our new components
import ExportCompletedState from '@ImportExport/components/export/ExportCompletedState';
import ExportInitialState from '@ImportExport/components/export/ExportInitialState';
import ExportProgressState from '@ImportExport/components/export/ExportProgressState';

interface ExportModalProps extends ModalProps {
  onClose: () => void;
  onExport: (data: ExportFormData) => void;
  currentStep: ImportExportModalState;
  onDownload?: (fileName: string) => void;
  progress: number;
  fileSize?: number;
  message?: string;
  completedContents?: ExportContentResponse['completed_contents'];
  failedCourseIds?: ExportContentResponse['failed_course_ids'];
  failedBundleIds?: ExportContentResponse['failed_bundle_ids'];
}

interface BulkSelectionFormData {
  courses: Course[];
  'course-bundle': Course[];
}

const fileName = `tutor_data_${Date.now()}.json`;

const ExportModal = ({
  onClose,
  onExport,
  currentStep,
  onDownload,
  progress,
  fileSize,
  message,
  completedContents,
  failedCourseIds = [],
  failedBundleIds = [],
}: ExportModalProps) => {
  const form = useFormWithGlobalError<ExportFormData>({
    defaultValues: defaultExportFormData,
  });

  const bulkSelectionForm = useFormWithGlobalError<BulkSelectionFormData>({
    defaultValues: {
      courses: [],
      'course-bundle': [],
    },
  });

  const getExportableContentQuery = useExportableContentQuery();
  const exportableContent = getExportableContentQuery.data;

  const resetBulkSelection = (type: 'courses' | 'course-bundle') => {
    if (type === 'courses') {
      bulkSelectionForm.reset({
        courses: [],
        'course-bundle': bulkSelectionForm.getValues('course-bundle'),
      });
    }

    if (type === 'course-bundle') {
      bulkSelectionForm.reset({
        courses: bulkSelectionForm.getValues('courses'),
        'course-bundle': [],
      });
    }
  };

  useEffect(() => {
    if (getExportableContentQuery.isSuccess && getExportableContentQuery.data) {
      const courseIds = getExportableContentQuery.data.filter((item) => item.key === 'courses')[0].ids || [];
      const bundleIds = getExportableContentQuery.data.filter((item) => item.key === 'course-bundle')[0].ids || [];

      form.setValue('courses__ids', courseIds);
      form.setValue('course-bundle__ids', bundleIds);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [getExportableContentQuery.isSuccess]);

  const handleClose = () => {
    form.reset();
    onClose();
  };

  // Define the component mapping for course selection
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
  };

  const handleExport = form.handleSubmit((data) => {
    const { courses, 'course-bundle': bundles } = bulkSelectionForm.getValues();
    onExport?.({
      ...data,
      courses__ids: courses.length > 0 ? courses.map((course) => course.id) : form.getValues('courses__ids'),
      'course-bundle__ids':
        bundles.length > 0 ? bundles.map((bundle) => bundle.id) : form.getValues('course-bundle__ids'),
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
    progress: <ExportProgressState progress={progress} message={message} fileName={fileName} />,
    success: (
      <ExportCompletedState
        state="success"
        fileName={fileName}
        fileSize={fileSize}
        message={message}
        completedContents={completedContents}
        failedCourseIds={failedCourseIds}
        failedBundleIds={failedBundleIds}
        onDownload={onDownload}
        onClose={handleClose}
      />
    ),
    error: <ExportCompletedState state="error" fileName={fileName} message={message} onClose={handleClose} />,
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
              <Logo
                wrapperCss={css`
                  padding-left: 0;
                `}
              />
              <span>{__('Exporter', 'tutor')}</span>
            </div>
            <div>
              <Button
                variant="primary"
                size="small"
                icon={<SVGIcon name="export" width={24} height={24} />}
                disabled={
                  !Object.entries(form.getValues()).some(([key, value]) => {
                    if (!key.includes('__')) {
                      return value === true;
                    }
                    return false;
                  })
                }
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
  `,
  headerTitle: css`
    ${styleUtils.display.flex()}
    align-items: center;
    gap: ${spacing[4]};
    ${typography.heading6('medium')}
    color: ${colorTokens.text.brand};
  `,
};
