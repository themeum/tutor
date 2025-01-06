import { Global } from '@emotion/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { lazy, Suspense, useState } from 'react';

import ToastProvider from '@TutorShared/atoms/Toast';

import RTLProvider from '@TutorShared/components/RTLProvider';
import { ModalProvider } from '@TutorShared/components/modals/Modal';

import { createGlobalCss } from '@TutorShared/utils/style-utils';
import { PaymentProvider } from '../contexts/payment-context';
import { LoadingSection } from '@/v3/shared/atoms/LoadingSpinner';
const PaymentSettings = lazy(() => import('./PaymentSettings'));

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
          <PaymentProvider>
            <ModalProvider>
              <Global styles={createGlobalCss()} />
              <Suspense fallback={<LoadingSection />}>
                <PaymentSettings />
              </Suspense>
            </ModalProvider>
          </PaymentProvider>
        </ToastProvider>
      </QueryClientProvider>
    </RTLProvider>
  );
}

export default App;
