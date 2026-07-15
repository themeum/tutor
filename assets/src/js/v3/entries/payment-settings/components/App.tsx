import { useState } from 'react';
import { Global } from '@emotion/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

import ToastProvider from '@TutorShared/atoms/Toast';

import { ModalProvider } from '@TutorShared/components/modals/Modal';
import RTLProvider from '@TutorShared/components/RTLProvider';

import { SVGIconConfigProvider } from '@TutorShared/contexts/SVGIconConfigContext';
import { createGlobalCss } from '@TutorShared/utils/style-utils';

import { PaymentProvider } from '../contexts/payment-context';
import PaymentSettings from './PaymentSettings';

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
        <SVGIconConfigProvider>
          <ToastProvider position="bottom-right">
            <PaymentProvider>
              <ModalProvider>
                <Global styles={createGlobalCss()} />
                <PaymentSettings />
              </ModalProvider>
            </PaymentProvider>
          </ToastProvider>
        </SVGIconConfigProvider>
      </QueryClientProvider>
    </RTLProvider>
  );
}

export default App;
