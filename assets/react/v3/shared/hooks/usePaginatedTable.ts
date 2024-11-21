import { ITEMS_PER_PAGE } from '@Config/constants';
import { useCallback, useState } from 'react';
import { JsonParam, NumberParam, StringParam, createEnumParam, useQueryParams, withDefault } from 'use-query-params';

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

const SortDirectionEnumParam = createEnumParam(['asc', 'desc']);

export const usePaginatedTable = ({ limit = ITEMS_PER_PAGE, updateQueryParams = true } = {}) => {
  const [query, setQuery] = useQueryParams({
    page: withDefault(NumberParam, 1),
    sortProperty: withDefault(StringParam, ''),
    sortDirection: withDefault(SortDirectionEnumParam, undefined),
    filter: withDefault<Filter, Filter>(JsonParam, {} as Filter),
  });
  const [paginationInfo, setPaginationInfo] = useState<PaginationInfo>({
    page: 1,
    sortProperty: '',
    sortDirection: undefined,
    filter: {},
  });
  const pageInfo = updateQueryParams ? query : paginationInfo;
  const offset = limit * Math.max(0, pageInfo.page - 1);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  const updatePaginationInfo = useCallback(
    (params: Partial<PaginationInfo>) => {
      if (updateQueryParams) {
        setQuery({ ...params });
      } else {
        setPaginationInfo((prevPageInfo) => ({ ...prevPageInfo, ...params }));
      }
    },
    [setQuery, setPaginationInfo, updateQueryParams],
  );

  const onPageChange = (pageNumber: number) => updatePaginationInfo({ page: pageNumber });
  const onFilterItems = useCallback((filter: Filter) => updatePaginationInfo({ page: 1, filter }), [updatePaginationInfo]);
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
