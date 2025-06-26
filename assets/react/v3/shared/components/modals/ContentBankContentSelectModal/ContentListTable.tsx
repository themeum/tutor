import { css } from '@emotion/react';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { __, _n, sprintf } from '@wordpress/i18n';

interface ContentListTableProps {
  collectionName?: string;
  totalItems?: number;
  onBack?: () => void;
}

const ContentListTable = ({ collectionName, totalItems = 0, onBack }: ContentListTableProps) => {
  return (
    <div css={styles.wrapper}>
      <div css={styles.headerWithAction}>
        <button css={styleUtils.backButton} onClick={onBack} aria-label={__('Go back to collection list', 'tutor')}>
          <SVGIcon name="arrowLeft" height={24} width={24} />
        </button>
        <div css={styles.headerTitle}>
          <span>{collectionName} </span>
          <Show when={totalItems}>
            <span>
              (
              {
                /* translators: %d is the total number of contents */
                sprintf(_n('%d Item', '%d Items', totalItems, 'tutor'))
              }
              )
            </span>
          </Show>
        </div>
      </div>

      {/* Table */}
    </div>
  );
};

export default ContentListTable;

const styles = {
  headerWithAction: css`
    ${styleUtils.display.flex('row')};
    align-items: center;
    gap: ${spacing[8]};
    padding: 0 ${spacing[16]} ${spacing[12]} ${spacing[16]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  headerTitle: css`
    ${typography.body('medium')};
    span {
      &:first-of-type {
        color: ${colorTokens.text.title};
      }
    }

    span:last-of-type:not(:only-of-type) {
      ${typography.tiny('medium')};
      color: ${colorTokens.text.hints};
    }
  `,
  wrapper: css`
    ${styleUtils.display.flex('column')};
  `,
};
