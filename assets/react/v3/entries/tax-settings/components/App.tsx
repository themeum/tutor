import { Global } from '@emotion/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { lazy, Suspense, useState } from 'react';

import ToastProvider from '@TutorShared/atoms/Toast';

import RTLProvider from '@TutorShared/components/RTLProvider';
import { ModalProvider } from '@TutorShared/components/modals/Modal';

import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import { createGlobalCss } from '@TutorShared/utils/style-utils';
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
