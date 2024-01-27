import { UseMutationResult } from '@tanstack/react-query';
import { useEffect } from 'react';
import { To, useNavigate } from 'react-router-dom';

export const useRedirectAfterMutation = <T, U>({
  mutation,
  navigateTo,
}: {
  mutation: UseMutationResult<T, Error, U, unknown>;
  navigateTo?: To;
}) => {
  const navigate = useNavigate();

  useEffect(() => {
    if (mutation.isSuccess && !!navigateTo) {
      navigate(navigateTo);
    }
  }, [mutation.isSuccess, navigate, navigateTo]);

  return mutation.mutateAsync;
};
