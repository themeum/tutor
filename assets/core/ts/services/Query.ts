import { type ServiceMeta } from '@Core/ts/types';
import Alpine from 'alpinejs';

/**
 * QueryCache: Manages cached query data with timestamps
 */
class QueryCache {
  private cache = new Map<string, { data: unknown; timestamp: number }>();

  get(key: string): { data: unknown; timestamp: number } | undefined {
    return this.cache.get(key);
  }

  set(key: string, value: unknown): void {
    this.cache.set(key, {
      data: value,
      timestamp: Date.now(),
    });
  }

  invalidate(key: string): void {
    this.cache.delete(key);
  }

  clear(): void {
    this.cache.clear();
  }

  getInfo(key: string): string {
    const entry = this.cache.get(key);
    if (!entry) return 'No cache';
    const age = Math.round((Date.now() - entry.timestamp) / 1000);
    return `Cached ${age}s ago`;
  }
}

/**
 * Query state interface
 */
interface QueryState<TData = unknown, TError = Error> {
  data: TData | null;
  error: TError | null;
  isLoading: boolean;
  isFetching: boolean;
  fetchData(): Promise<void>;
  refetch(): Promise<void>;
  init(): Promise<void>;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}

/**
 * Mutation state interface
 */
interface MutationState<TData = unknown, TVariables = unknown, TError = Error> {
  data: TData | null;
  error: TError | null;
  isPending: boolean;
  isError: boolean;
  isSuccess: boolean;
  mutate(variables: TVariables): Promise<TData>;
  mutateAsync(variables: TVariables): Promise<TData>;
  reset(): void;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}

/**
 * Query options interface
 */
interface QueryOptions {
  cacheTime?: number;
  staleTime?: number;
}

/**
 * Mutation options interface
 */
interface MutationOptions<TData = unknown, TVariables = unknown, TError = Error> {
  onSuccess?: (data: TData, variables: TVariables) => void | Promise<void>;
  onError?: (error: TError, variables: TVariables) => void;
}

/**
 * QueryService: Provides TanStack Query-like functionality using Alpine.reactive
 * Supports data fetching with caching, mutations, and cache invalidation
 */
class QueryService {
  private queryCache = new QueryCache();

  /**
   * Create a query with automatic caching and refetching capabilities
   * @param queryKey - Unique identifier for the query
   * @param queryFn - Function that returns a promise with the data
   * @param options - Optional configuration for cache and stale time
   * @returns Reactive query state object
   */
  useQuery<TData = unknown, TError = Error>(
    queryKey: string,
    queryFn: () => Promise<TData>,
    options: QueryOptions = {},
  ): QueryState<TData, TError> {
    const staleTime = options.staleTime || 0;
    const queryCache = this.queryCache;

    // Create reactive state object
    const state = Alpine.reactive({
      data: null as TData | null,
      error: null as TError | null,
      isLoading: true,
      isFetching: false,

      async fetchData() {
        (this as unknown as QueryState<TData, TError>).isFetching = true;
        try {
          const result = await queryFn();
          (this as unknown as QueryState<TData, TError>).data = result;
          (this as unknown as QueryState<TData, TError>).error = null;
          queryCache.set(queryKey, result);
        } catch (err) {
          (this as unknown as QueryState<TData, TError>).error = {
            message: (err as Error).message || 'Failed to fetch data',
            code: (err as { code?: string }).code,
          } as TError;
        } finally {
          (this as unknown as QueryState<TData, TError>).isLoading = false;
          (this as unknown as QueryState<TData, TError>).isFetching = false;
        }
      },

      async refetch() {
        (this as unknown as QueryState<TData, TError>).isLoading = false;
        (this as unknown as QueryState<TData, TError>).isFetching = true;
        await this.fetchData();
      },

      async init() {
        const cached = queryCache.get(queryKey);
        if (cached && Date.now() - cached.timestamp < staleTime) {
          (this as unknown as QueryState<TData, TError>).data = cached.data as TData;
          (this as unknown as QueryState<TData, TError>).isLoading = false;
          return;
        }
        await this.fetchData();
      },
    }) as unknown as QueryState<TData, TError>;

    // Auto-initialize
    state.init();

    return state;
  }

  /**
   * Create a mutation for data modification operations
   * @param mutationFn - Function that performs the mutation
   * @param options - Optional callbacks for success and error handling
   * @returns Reactive mutation state object
   */
  useMutation<TData = unknown, TVariables = unknown, TError = Error>(
    mutationFn: (variables: TVariables) => Promise<TData>,
    options: MutationOptions<TData, TVariables, TError> = {},
  ): MutationState<TData, TVariables, TError> {
    const state = Alpine.reactive({
      data: null as TData | null,
      error: null as TError | null,
      isPending: false,
      isError: false,
      isSuccess: false,

      async mutate(variables: TVariables) {
        (this as unknown as MutationState<TData, TVariables, TError>).isPending = true;
        (this as unknown as MutationState<TData, TVariables, TError>).isError = false;
        (this as unknown as MutationState<TData, TVariables, TError>).isSuccess = false;
        (this as unknown as MutationState<TData, TVariables, TError>).error = null;

        try {
          const result = await mutationFn(variables);
          (this as unknown as MutationState<TData, TVariables, TError>).data = result;
          (this as unknown as MutationState<TData, TVariables, TError>).isSuccess = true;

          if (options.onSuccess) {
            await options.onSuccess(result, variables);
          }

          return result;
        } catch (err) {
          (this as unknown as MutationState<TData, TVariables, TError>).error = {
            message: (err as Error).message || 'Mutation failed',
            code: (err as { code?: string }).code,
          } as TError;
          (this as unknown as MutationState<TData, TVariables, TError>).isError = true;

          if (options.onError) {
            options.onError((this as unknown as MutationState<TData, TVariables, TError>).error as TError, variables);
          }

          throw err;
        } finally {
          (this as unknown as MutationState<TData, TVariables, TError>).isPending = false;
        }
      },

      async mutateAsync(variables: TVariables) {
        return await this.mutate(variables);
      },

      reset() {
        (this as unknown as MutationState<TData, TVariables, TError>).data = null;
        (this as unknown as MutationState<TData, TVariables, TError>).error = null;
        (this as unknown as MutationState<TData, TVariables, TError>).isPending = false;
        (this as unknown as MutationState<TData, TVariables, TError>).isError = false;
        (this as unknown as MutationState<TData, TVariables, TError>).isSuccess = false;
      },
    }) as unknown as MutationState<TData, TVariables, TError>;

    return state;
  }

  /**
   * Invalidate a query cache entry
   * @param queryKey - The query key to invalidate
   */
  invalidateQuery(queryKey: string): void {
    this.queryCache.invalidate(queryKey);
  }

  /**
   * Get cache information for a query
   * @param queryKey - The query key to get info for
   * @returns String describing cache status
   */
  getCacheInfo(queryKey: string): string {
    return this.queryCache.getInfo(queryKey);
  }

  /**
   * Clear all cached queries
   */
  clearCache(): void {
    this.queryCache.clear();
  }
}

export const queryServiceMeta: ServiceMeta = {
  name: 'query',
  instance: new QueryService(),
};
