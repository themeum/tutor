import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import React from 'react';

import SettingsPage from '@Settings/components/SettingsPage';
import { SettingsProvider } from '@Settings/contexts/SettingsContext';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

const App: React.FC = () => {
  return (
    <QueryClientProvider client={queryClient}>
      <SettingsProvider>
        <SettingsPage />
      </SettingsProvider>
    </QueryClientProvider>
  );
};

export default App;
