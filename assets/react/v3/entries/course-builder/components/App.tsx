import ToastProvider from '@Atoms/Toast';
import { ModalProvider } from '@Components/modals/Modal';
import RTLProvider from '@Components/RTLProvider';
import routes from '@CourseBuilderConfig/routes';
import { createGlobalCss } from '@Utils/style-utils';
import { Global } from '@emotion/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useState } from 'react';
import { useRoutes } from 'react-router-dom';

const App = () => {
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

  const routers = useRoutes(routes);

  return (
    <RTLProvider>
      <QueryClientProvider client={queryClient}>
        <ToastProvider position="bottom-center">
          <ModalProvider>
            <Global styles={createGlobalCss()} />
            {routers}
          </ModalProvider>
        </ToastProvider>
      </QueryClientProvider>
    </RTLProvider>
  );
};

export default App;
