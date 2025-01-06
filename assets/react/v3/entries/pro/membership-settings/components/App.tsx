import { lazy, Suspense, useState } from 'react';
import { Global } from '@emotion/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { QueryParamProvider } from 'use-query-params';
import { ReactRouter6Adapter } from 'use-query-params/adapters/react-router-6';

import ToastProvider from '@TutorShared/atoms/Toast';

import RTLProvider from '@TutorShared/components/RTLProvider';
import { ModalProvider } from '@TutorShared/components/modals/Modal';

import { createGlobalCss } from '@TutorShared/utils/style-utils';
import { LoadingSection } from '@/v3/shared/atoms/LoadingSpinner';
const MembershipSettings = lazy(() => import('./MembershipSettings'));

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
      <QueryParamProvider adapter={ReactRouter6Adapter}>
        <QueryClientProvider client={queryClient}>
          <ToastProvider position="bottom-right">
            <ModalProvider>
              <Global styles={createGlobalCss()} />
              <Suspense fallback={<LoadingSection />}>
                <MembershipSettings />
              </Suspense>
            </ModalProvider>
          </ToastProvider>
        </QueryClientProvider>
      </QueryParamProvider>
    </RTLProvider>
  );
}

export default App;
