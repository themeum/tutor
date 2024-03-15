import { type MutationFunction, type QueryKey, useMutation, useQueryClient } from '@tanstack/react-query';

interface GenericMutationProps<TData, TVariables> {
	mutationFn: MutationFunction<TData, TVariables>;
	invalidateKeys?: (QueryKey | undefined)[];
}

export const useGenericMutation = <TData = unknown, TVariables = void>({
	mutationFn,
	invalidateKeys,
}: GenericMutationProps<TData, TVariables>) => {
	const queryClient = useQueryClient();

	return useMutation({
		mutationFn,
		onSuccess: () => {
			if (invalidateKeys?.length) {
				for (const queryKey of invalidateKeys) {
					queryClient.invalidateQueries({ queryKey });
				}
			}
		},
	});
};
