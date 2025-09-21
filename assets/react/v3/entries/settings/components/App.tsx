import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import React from 'react';
import { Route, Routes } from 'react-router-dom';

import { SettingsProvider } from '@Settings/contexts/SettingsContext';
import SettingsPage from '@Settings/pages/SettingsPage';

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
        <Routes>
          <Route path="/" element={<SettingsPage />} />
          <Route path="/:section" element={<SettingsPage />} />
        </Routes>
      </SettingsProvider>
    </QueryClientProvider>
  );
};

export default App;
