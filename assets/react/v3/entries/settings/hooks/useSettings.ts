import { useSettings as useSettingsContext } from '@Settings/contexts/SettingsContext';
import { settingsApi } from '@Settings/services/settingsApi';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import React from 'react';

export const useSettingsData = () => {
  const { dispatch } = useSettingsContext();

  // Get settings fields structure
  const settingsFieldsQuery = useQuery({
    queryKey: ['settings-fields'],
    queryFn: settingsApi.getSettingsFields,
  });

  // Get current settings values
  const settingsValuesQuery = useQuery({
    queryKey: ['settings-values'],
    queryFn: settingsApi.getSettingsValues,
  });

  // Handle successful data fetching
  React.useEffect(() => {
    if (settingsFieldsQuery.data?.success && settingsFieldsQuery.data.data) {
      dispatch({ type: 'SET_SECTIONS', payload: settingsFieldsQuery.data.data });
    }
  }, [settingsFieldsQuery.data, dispatch]);

  React.useEffect(() => {
    if (settingsValuesQuery.data?.success && settingsValuesQuery.data.data) {
      dispatch({ type: 'SET_VALUES', payload: settingsValuesQuery.data.data });
    }
  }, [settingsValuesQuery.data, dispatch]);

  return {
    isLoading: settingsFieldsQuery.isLoading || settingsValuesQuery.isLoading,
    error: settingsFieldsQuery.error || settingsValuesQuery.error,
    refetch: () => {
      settingsFieldsQuery.refetch();
      settingsValuesQuery.refetch();
    },
  };
};

export const useSettingsMutation = () => {
  const queryClient = useQueryClient();
  const { dispatch } = useSettingsContext();

  const saveSettingsMutation = useMutation({
    mutationFn: settingsApi.saveSettings,
    onMutate: () => {
      dispatch({ type: 'SET_SAVING', payload: true });
    },
    onSuccess: (data) => {
      dispatch({ type: 'SET_SAVING', payload: false });
      if (data.success) {
        dispatch({ type: 'RESET_DIRTY' });
        queryClient.invalidateQueries({ queryKey: ['settings-values'] });
      }
    },
    onError: () => {
      dispatch({ type: 'SET_SAVING', payload: false });
    },
  });

  const resetSettingsMutation = useMutation({
    mutationFn: settingsApi.resetSettings,
    onSuccess: (data) => {
      if (data.success) {
        queryClient.invalidateQueries({ queryKey: ['settings-values'] });
      }
    },
  });

  return {
    saveSettings: saveSettingsMutation.mutate,
    resetSettings: resetSettingsMutation.mutate,
    isSaving: saveSettingsMutation.isPending,
    isResetting: resetSettingsMutation.isPending,
  };
};

export const useSettingsSearch = () => {
  const searchMutation = useMutation({
    mutationFn: settingsApi.searchSettings,
  });

  return {
    searchSettings: searchMutation.mutate,
    searchResults: searchMutation.data?.data,
    isSearching: searchMutation.isPending,
  };
};
