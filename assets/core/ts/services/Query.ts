import { type ServiceMeta } from '@Core/ts/types';
import Alpine from 'alpinejs';

type QueryKey = string | readonly unknown[];

/**
 * QueryCache: Manages cached query data with timestamps
 */
class QueryCache {
  private cache = new Map<string, { data: unknown; timestamp: number }>();

  /**
   * Generate a cache key from QueryKey
   */
  private generateKey(queryKey: QueryKey): string {
    if (typeof queryKey === 'string') return queryKey;
    return JSON.stringify(queryKey);
  }

  get(key: QueryKey): { data: unknown; timestamp: number } | undefined {
    return this.cache.get(this.generateKey(key));
  }

  set(key: QueryKey, value: unknown): void {
    this.cache.set(this.generateKey(key), {
      data: value,
      timestamp: Date.now(),
    });
  }

  invalidate(key: QueryKey): void {
    this.cache.delete(this.generateKey(key));
  }

  invalidatePattern(pattern: string): void {
    const keys = Array.from(this.cache.keys());
    keys.forEach((key) => {
      if (key.includes(pattern)) {
        this.cache.delete(key);
      }
    });
  }

  clear(): void {
    this.cache.clear();
  }

  getInfo(key: QueryKey): string {
    const entry = this.cache.get(this.generateKey(key));
    if (!entry) return 'No cache';
    const age = Math.round((Date.now() - entry.timestamp) / 1000);
    return `Cached ${age}s ago`;
  }
}

/**
 * Query state interface
 */
export interface QueryState<TData = unknown, TError = Error> {
  data: TData | null;
  error: TError | null;
  isLoading: boolean;
  isFetching: boolean;
  isStale: boolean;
  fetchData(): Promise<void>;
  refetch(): Promise<void>;
  init(): Promise<void>;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}

/**
 * Mutation state interface
 */
export interface MutationState<TData = unknown, TVariables = unknown, TError = Error> {
  data: TData | null;
  error: TError | null;
  isPending: boolean;
  isError: boolean;
  isSuccess: boolean;
  mutate(variables?: TVariables): Promise<TData>;
  mutateAsync(variables?: TVariables): Promise<TData>;
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
  enabled?: boolean;
}

/**
 * Mutation options interface
 */
interface MutationOptions<TData = unknown, TVariables = unknown, TError = Error> {
  onMutate?: (variables: TVariables) => void | Promise<void>;
  onSuccess?: (data: TData, variables: TVariables) => void | Promise<void>;
  onError?: (error: TError, variables: TVariables) => void;
  onSettled?: (data: TData | null, error: TError | null, variables: TVariables) => void;
}

/**
 * QueryService: Provides TanStack Query-like functionality using Alpine.reactive
 * Supports data fetching with caching, mutations, and cache invalidation
 */
export class QueryService {
  private queryCache = new QueryCache();

  /**
   * Create a query with automatic caching and refetching capabilities
   * @param queryKey - Unique identifier for the query (string or array)
   * @param queryFn - Function that returns a promise with the data
   * @param options - Optional configuration for cache and stale time
   * @returns Reactive query state object
   */
  useQuery<TData = unknown, TError = Error>(
    queryKey: QueryKey,
    queryFn: () => Promise<TData>,
    options: QueryOptions = {},
  ): QueryState<TData, TError> {
    const staleTime = options.staleTime || 0;
    const enabled = options.enabled !== undefined ? options.enabled : true;
    const queryCache = this.queryCache;

    // Create reactive state object
    const state = Alpine.reactive({
      data: null as TData | null,
      error: null as TError | null,
      isLoading: enabled,
      isFetching: false,
      isStale: false,

      async fetchData(): Promise<void> {
        (this as unknown as QueryState<TData, TError>).isFetching = true;
        try {
          const result = await queryFn();
          (this as unknown as QueryState<TData, TError>).data = result;
          (this as unknown as QueryState<TData, TError>).error = null;
          (this as unknown as QueryState<TData, TError>).isStale = false;
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
        if (!enabled) {
          (this as unknown as QueryState<TData, TError>).isLoading = false;
          return;
        }

        const cached = queryCache.get(queryKey);
        if (cached && Date.now() - cached.timestamp < staleTime) {
          (this as unknown as QueryState<TData, TError>).data = cached.data as TData;
          (this as unknown as QueryState<TData, TError>).isLoading = false;
          (this as unknown as QueryState<TData, TError>).isStale = false;
          return;
        }

        if (cached) {
          (this as unknown as QueryState<TData, TError>).isStale = true;
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
          // onMutate callback - for optimistic updates
          if (options.onMutate) {
            await options.onMutate(variables);
          }

          const result = await mutationFn(variables);
          (this as unknown as MutationState<TData, TVariables, TError>).data = result;
          (this as unknown as MutationState<TData, TVariables, TError>).isSuccess = true;

          if (options.onSuccess) {
            await options.onSuccess(result, variables);
          }

          // onSettled callback - always called
          if (options.onSettled) {
            options.onSettled(result, null, variables);
          }

          return result;
        } catch (err) {
          const error = {
            message: (err as Error).message || 'Mutation failed',
            code: (err as { code?: string }).code,
          } as TError;

          (this as unknown as MutationState<TData, TVariables, TError>).error = error;
          (this as unknown as MutationState<TData, TVariables, TError>).isError = true;

          if (options.onError) {
            options.onError(error, variables);
          }

          // onSettled callback - always called
          if (options.onSettled) {
            options.onSettled(null, error, variables);
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
  invalidateQuery(queryKey: QueryKey): void {
    this.queryCache.invalidate(queryKey);
  }

  /**
   * Invalidate all queries matching a pattern
   * @param pattern - Pattern to match against query keys
   */
  invalidateQueries(pattern: string): void {
    this.queryCache.invalidatePattern(pattern);
  }

  /**
   * Get cache information for a query
   * @param queryKey - The query key to get info for
   * @returns String describing cache status
   */
  getCacheInfo(queryKey: QueryKey): string {
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
