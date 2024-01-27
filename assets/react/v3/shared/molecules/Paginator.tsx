import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorPalate, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { useTranslation } from '@Hooks/useTranslation';
import { styleUtils } from '@Utils/style-utils';
import { useEffect, useState } from 'react';

interface PaginatorProps {
  currentPage: number;
  totalItems: number;
  itemsPerPage: number;
  onPageChange: (pageNumber: number) => void;
}

const Paginator = ({ currentPage, onPageChange, totalItems, itemsPerPage }: PaginatorProps) => {
  const t = useTranslation();
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
        {t('COM_SPPAGEBUILDER_STORE_PAGINATION_PAGE')}
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
        {t('COM_SPPAGEBUILDER_STORE_PAGINATION_PAGE_OF')} <span>{totalPage}</span>
      </div>

      <div css={styles.pageController}>
        <button
          type="button"
          css={styles.paginationButton}
          onClick={() => pageChangeHandler(currentPage - 1)}
          disabled={currentPage === 1}
        >
          <SVGIcon name="chevronLeft" width={24} height={24} />
        </button>
        <button
          type="button"
          css={styles.paginationButton}
          onClick={() => pageChangeHandler(currentPage + 1)}
          disabled={currentPage === totalPage}
        >
          <SVGIcon name="chevronRight" width={24} height={24} />
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
    color: ${colorPalate.text.neutral};
    min-width: 100px;
  `,
  paginationInput: css`
    outline: 0;
    border: 1px solid ${colorPalate.border.neutral};
    border-radius: ${borderRadius[6]};
    margin: 0 ${spacing[8]};
    color: ${colorPalate.text.neutral};
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
    background: ${colorPalate.basic.white};
    color: ${colorPalate.icon.default};
    border-radius: ${borderRadius[6]};

    height: 32px;
    width: 32px;
    display: grid;
    place-items: center;
    transition: background 0.2s ease-in-out, color 0.3s ease-in-out;

    &:hover {
      background: ${colorPalate.basic.primary.default};

      & > svg {
        color: ${colorPalate.basic.white};
      }
    }

    &:disabled {
      background: ${colorPalate.surface.disabled};

      & > svg {
        color: ${colorPalate.icon.disabled};
      }
    }
  `,
};
