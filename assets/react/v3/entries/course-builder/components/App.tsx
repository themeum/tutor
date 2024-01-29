import ToastProvider from '@Atoms/Toast';
import { ModalProvider } from '@Components/modals/Modal';
import { Global } from '@emotion/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { createGlobalCss } from '@Utils/style-utils';
import { useState } from 'react';
import Layout from '@CourseBuilderComponents/layouts/Layout';
import { useRoutes } from 'react-router-dom';
import routes from '@CourseBuilderConfig/routes';

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
      })
  );

  const routers = useRoutes(routes);

  return (
    <QueryClientProvider client={queryClient}>
      <ToastProvider position="bottom-center">
        <ModalProvider>
          <Global styles={createGlobalCss()} />
          {routers}
        </ModalProvider>
      </ToastProvider>
    </QueryClientProvider>
  );
};

export default App;
