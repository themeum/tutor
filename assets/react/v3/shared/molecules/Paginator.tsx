import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { isRTL } from '@TutorShared/config/constants';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';

interface PaginatorProps {
  currentPage: number;
  totalItems: number;
  itemsPerPage: number;
  onPageChange: (pageNumber: number) => void;
}

const Paginator = ({ currentPage, onPageChange, totalItems, itemsPerPage }: PaginatorProps) => {
  const totalPage = Math.max(Math.ceil(totalItems / itemsPerPage), 1);
  const [pageNumber, setPageNumber] = useState('');

  useEffect(() => {
    setPageNumber(currentPage.toString());
  }, [currentPage]);

  const pageChangeHandler = (pageNumber: number) => {
    if (pageNumber < 1 || pageNumber > totalPage) {
      return;
    }

    onPageChange(pageNumber);
  };

  return (
    <div css={styles.wrapper}>
      <div css={styles.pageStatus}>
        {__('Page', __TUTOR_TEXT_DOMAIN__)}
        <span>
          <input
            type="text"
            css={styles.paginationInput}
            value={pageNumber}
            onChange={(event) => {
              const { value } = event.currentTarget;
              const pageNumberValue = value.replace(/[^0-9]/g, '');
              const page = Number(pageNumberValue);
              if (page > 0 && page <= totalPage) {
                setPageNumber(pageNumberValue);
                onPageChange(page);
              } else if (!pageNumberValue) {
                setPageNumber(pageNumberValue);
              }
            }}
            autoComplete="off"
          />
        </span>
        {__('of', __TUTOR_TEXT_DOMAIN__)} <span>{totalPage}</span>
      </div>

      <div css={styles.pageController}>
        <button
          type="button"
          css={styles.paginationButton}
          onClick={() => pageChangeHandler(currentPage - 1)}
          disabled={currentPage === 1}
        >
          <SVGIcon name={!isRTL ? 'chevronLeft' : 'chevronRight'} width={32} height={32} />
        </button>
        <button
          type="button"
          css={styles.paginationButton}
          onClick={() => pageChangeHandler(currentPage + 1)}
          disabled={currentPage === totalPage}
        >
          <SVGIcon name={!isRTL ? 'chevronRight' : 'chevronLeft'} width={32} height={32} />
        </button>
      </div>
    </div>
  );
};

export default Paginator;

const styles = {
  wrapper: css`
    display: flex;
    justify-content: end;
    align-items: center;
    flex-wrap: wrap;
    gap: ${spacing[8]};
    height: 36px;
  `,
  pageStatus: css`
    ${typography.body()}
    color: ${colorTokens.text.title};
    min-width: 100px;
  `,
  paginationInput: css`
    outline: 0;
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[6]};
    margin: 0 ${spacing[8]};
    color: ${colorTokens.text.subdued};
    padding: 8px 12px;
    width: 72px;

    /* Chrome, Safari, Edge, Opera */
    &::-webkit-outer-spin-button,
    &::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: ${spacing[0]};
    }

    /* Firefox */
    &[type='number'] {
      -moz-appearance: textfield;
    }
  `,
  pageController: css`
    gap: ${spacing[8]};

    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
  `,
  paginationButton: css`
    ${styleUtils.resetButton};
    background: ${colorTokens.background.white};
    color: ${colorTokens.icon.default};
    border-radius: ${borderRadius[6]};

    height: 32px;
    width: 32px;
    display: grid;
    place-items: center;
    transition:
      background-color 0.2s ease-in-out,
      color 0.3s ease-in-out;

    svg {
      color: ${colorTokens.icon.default};
    }

    &:hover {
      background: ${colorTokens.background.default};

      & > svg {
        color: ${colorTokens.icon.brand};
      }
    }

    &:disabled {
      background: ${colorTokens.background.white};

      & > svg {
        color: ${colorTokens.icon.disable.default};
      }
    }
  `,
};
