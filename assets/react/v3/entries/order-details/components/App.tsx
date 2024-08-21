import ToastProvider from '@Atoms/Toast';
import { ModalProvider } from '@Components/modals/Modal';
import RTLProvider from '@Components/RTLProvider';
import { createGlobalCss } from '@Utils/style-utils';
import { Global } from '@emotion/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useState } from 'react';
import Main from './layout/Main';

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
            <Main />
          </ModalProvider>
        </ToastProvider>
      </QueryClientProvider>
    </RTLProvider>
  );
}

export default App;
