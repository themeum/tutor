import { Global } from '@emotion/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { lazy, Suspense, useState } from 'react';

import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import ToastProvider from '@TutorShared/atoms/Toast';
import RTLProvider from '@TutorShared/components/RTLProvider';
import { ModalProvider } from '@TutorShared/components/modals/Modal';
import { createGlobalCss } from '@TutorShared/utils/style-utils';

const Main = lazy(() => import('@ImportExport/components/Main'));

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
              <Main />
            </Suspense>
          </ModalProvider>
        </ToastProvider>
      </QueryClientProvider>
    </RTLProvider>
  );
}

export default App;
