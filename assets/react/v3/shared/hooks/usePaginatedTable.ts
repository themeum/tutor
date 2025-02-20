import { ITEMS_PER_PAGE } from '@TutorShared/config/constants';
import { useCallback, useState } from 'react';

type FilterKey =
  | 'search'
  | 'status'
  | 'sortBy'
  | 'stock'
  | 'categories'
  | 'orderStatus'
  | 'fulfilment'
  | 'paymentStatus';
export type Filter = Partial<Record<FilterKey, string>>;
interface PaginationInfo {
  page: number;
  sortProperty: string;
  sortDirection: 'asc' | 'desc' | undefined;
  filter: Filter;
}
export type PaginationProperties = ReturnType<typeof usePaginatedTable>;

export const usePaginatedTable = ({ limit = ITEMS_PER_PAGE } = {}) => {
  const [paginationInfo, setPaginationInfo] = useState<PaginationInfo>({
    page: 1,
    sortProperty: '',
    sortDirection: undefined,
    filter: {},
  });
  const pageInfo = paginationInfo;
  const offset = limit * Math.max(0, pageInfo.page - 1);

  const updatePaginationInfo = useCallback(
    (params: Partial<PaginationInfo>) => {
      setPaginationInfo((prevPageInfo) => ({ ...prevPageInfo, ...params }));
    },
    [setPaginationInfo],
  );

  const onPageChange = (pageNumber: number) => updatePaginationInfo({ page: pageNumber });
  const onFilterItems = useCallback(
    (filter: Filter) => updatePaginationInfo({ page: 1, filter }),
    [updatePaginationInfo],
  );
  const onColumnSort = (sortProperty: string) => {
    let sortInfo = {};
    if (sortProperty !== pageInfo.sortProperty) {
      sortInfo = { sortDirection: 'asc', sortProperty };
    } else {
      sortInfo = { sortDirection: pageInfo.sortDirection === 'asc' ? 'desc' : 'asc', sortProperty };
    }
    updatePaginationInfo(sortInfo);
  };

  return {
    pageInfo,
    onPageChange,
    onColumnSort,
    offset,
    itemsPerPage: limit,
    onFilterItems,
  };
};
