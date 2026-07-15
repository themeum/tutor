import { useState } from 'react';
import { Global } from '@emotion/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

import ToastProvider from '@TutorShared/atoms/Toast';

import { ModalProvider } from '@TutorShared/components/modals/Modal';
import RTLProvider from '@TutorShared/components/RTLProvider';

import { SVGIconConfigProvider } from '@TutorShared/contexts/SVGIconConfigContext';
import { createGlobalCss } from '@TutorShared/utils/style-utils';

import TaxSettingsPage from './TaxSettings';

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
            <ModalProvider>
              <Global styles={createGlobalCss()} />
              <TaxSettingsPage />
            </ModalProvider>
          </ToastProvider>
        </SVGIconConfigProvider>
      </QueryClientProvider>
    </RTLProvider>
  );
}

export default App;
