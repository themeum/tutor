import ToastProvider from '@Core/ts/toast';
import { Global } from '@emotion/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import RTLProvider from '@TutorShared/components/RTLProvider';
import { SVGIconConfigProvider } from '@TutorShared/contexts/SVGIconConfigContext';
import { createGlobalCss } from '@TutorShared/utils/style-utils';
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
        <SVGIconConfigProvider>
          <ToastProvider position="bottom-right">
            <Global styles={createGlobalCss()} />
            <Main />
          </ToastProvider>
        </SVGIconConfigProvider>
      </QueryClientProvider>
    </RTLProvider>
  );
}

export default App;
