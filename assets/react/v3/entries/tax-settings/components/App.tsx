import { Global } from '@emotion/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { lazy, Suspense, useState } from 'react';

import ToastProvider from '@Atoms/Toast';

import RTLProvider from '@Components/RTLProvider';
import { ModalProvider } from '@Components/modals/Modal';

import { createGlobalCss } from '@Utils/style-utils';
import { LoadingSection } from '@/v3/shared/atoms/LoadingSpinner';
const TaxSettingsPage = lazy(() => import('./TaxSettings'));

function App() {
  const [queryClient] = useState(
    () =>
      new QueryClient({
        defaultOptions: {
          queries: {
            retry: false,
            refetchOnWindowFocus: false,
            networkMode: 'always',
          },
          mutations: {
            retry: false,
            networkMode: 'always',
          },
        },
      }),
  );

  return (
    <RTLProvider>
      <QueryClientProvider client={queryClient}>
        <ToastProvider position="bottom-right">
          <ModalProvider>
            <Global styles={createGlobalCss()} />
            <Suspense fallback={<LoadingSection />}>
              <TaxSettingsPage />
            </Suspense>
          </ModalProvider>
        </ToastProvider>
      </QueryClientProvider>
    </RTLProvider>
  );
}

export default App;
