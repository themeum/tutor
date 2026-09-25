import { useFormContext } from 'react-hook-form';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

import Alert from '@TutorShared/atoms/Alert';
import Button from '@TutorShared/atoms/Button';

import ConfirmationModal from '@TutorShared/components/modals/ConfirmationModal';
import { useModal } from '@TutorShared/components/modals/Modal';

import { tutorConfig } from '@TutorShared/config/config';
import { DEFAULT_QUIZ_NEGATIVE_MARK_VALUE, QUIZ_NEGATIVE_MARK_TYPES } from '@TutorShared/config/constants';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';

import type { QuizForm } from '@CourseBuilderServices/quiz';

interface NegativeMarkTypeMismatchModalContentProps {
  isSwitchingToPoints: boolean;
  currentPenalty: number | string;
}

const NegativeMarkTypeMismatchModalContent = ({
  isSwitchingToPoints,
  currentPenalty,
}: NegativeMarkTypeMismatchModalContentProps) => {
  const descriptionHtml = isSwitchingToPoints
    ? sprintf(
        // translators: 1: current penalty percentage (e.g. 15%), 2: new penalty placeholder ({new penalty})
        __('The penalty per wrong answer will change from %1$s to <strong>%2$s</strong> points.', 'tutor'),
        `${currentPenalty}%`,
        '{new penalty}',
      )
    : sprintf(
        // translators: 1: current penalty points (e.g. 15), 2: new penalty placeholder ({new penalty})
        __('The penalty per wrong answer will change from %1$s points to <strong>%2$s</strong>%%.', 'tutor'),
        currentPenalty,
        '{new penalty}',
      );

  return (
    <div css={styles.confirmModalContent}>
      <p css={styles.confirmModalDescription} dangerouslySetInnerHTML={{ __html: descriptionHtml }} />

      <ul css={styles.confirmModalBulletList}>
        <li>{__('This will apply only to new quiz attempts.', 'tutor')}</li>
        <li>{__('Previous attempts and their scores will remain unchanged.', 'tutor')}</li>
      </ul>
    </div>
  );
};

const NegativeMarkTypeMismatchNotice = () => {
  const form = useFormContext<QuizForm>();
  const { showModal } = useModal();

  const negativeMarkType = form.watch('quiz_option.negative_mark_type');
  const negativeMarkingEnabled = form.watch('quiz_option.enable_negative_marking');
  const negativeMarkValue = form.watch('quiz_option.negative_mark_value');

  const adminNegativeMarkType =
    tutorConfig.settings?.quiz_negative_mark_type === QUIZ_NEGATIVE_MARK_TYPES.FIXED
      ? QUIZ_NEGATIVE_MARK_TYPES.FIXED
      : QUIZ_NEGATIVE_MARK_TYPES.PERCENT;

  const showMismatchNotice = Boolean(negativeMarkingEnabled && adminNegativeMarkType !== negativeMarkType);

  if (!showMismatchNotice) {
    return null;
  }

  const isSwitchingToPoints = adminNegativeMarkType === QUIZ_NEGATIVE_MARK_TYPES.FIXED;

  const mismatchNoticeText = isSwitchingToPoints
    ? sprintf(
        // translators: %s is the current percentage penalty, e.g. 15
        __('The default penalty unit is now points. This quiz still uses a %s%% penalty per wrong answer.', 'tutor'),
        negativeMarkValue ?? 0,
      )
    : sprintf(
        // translators: %s is the current points penalty, e.g. 15
        __(
          'The default penalty unit is now percentage. This quiz still uses a %s pts penalty per wrong answer.',
          'tutor',
        ),
        negativeMarkValue ?? 0,
      );

  const handleOpenConfirmModal = async () => {
    const result = await showModal({
      component: ConfirmationModal,
      props: {
        title: isSwitchingToPoints
          ? __('Switch penalty unit to points?', 'tutor')
          : __('Switch penalty unit to percentage?', 'tutor'),
        confirmButtonText: isSwitchingToPoints ? __('Switch to Points', 'tutor') : __('Switch to Percentage', 'tutor'),
        confirmButtonVariant: 'primary',
        cancelButtonText: __('Cancel', 'tutor'),
        maxWidth: 480,
        description: (
          <NegativeMarkTypeMismatchModalContent
            isSwitchingToPoints={isSwitchingToPoints}
            currentPenalty={negativeMarkValue || DEFAULT_QUIZ_NEGATIVE_MARK_VALUE}
          />
        ),
      },
      closeOnOutsideClick: true,
      closeOnEscape: true,
    });

    if (result?.action === 'CONFIRM') {
      // Only the unit type changes, unit value remains unchanged
      form.setValue('quiz_option.negative_mark_type', adminNegativeMarkType, {
        shouldDirty: true,
        shouldValidate: true,
      });
    }
  };

  return (
    <div css={styles.noticeWrapper}>
      <Alert
        type="info"
        icon="bulbLine"
        action={
          <Button type="button" size="small" variant="primary" onClick={handleOpenConfirmModal}>
            {__('Update', 'tutor')}
          </Button>
        }
      >
        {mismatchNoticeText}
      </Alert>
    </div>
  );
};

export default NegativeMarkTypeMismatchNotice;

const styles = {
  noticeWrapper: css`
    width: 100%;
    margin-top: ${spacing[4]};
  `,
  confirmModalContent: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
  confirmModalDescription: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
    margin: 0;

    strong {
      color: ${colorTokens.text.title};
    }
  `,
  confirmModalBulletList: css`
    margin: 0;
    padding-inline-start: ${spacing[20]};
    list-style-type: disc;
    color: ${colorTokens.text.subdued};

    li {
      color: ${colorTokens.text.subdued};
      list-style-type: disc;

      &:last-child {
        margin-bottom: 0;
      }
    }
  `,
};
