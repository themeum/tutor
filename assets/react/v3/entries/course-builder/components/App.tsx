import routes from '@CourseBuilderConfig/routes';
import { CourseBuilderSlotProvider } from '@CourseBuilderContexts/CourseBuilderSlotContext';
import ToastProvider from '@TutorShared/atoms/Toast';
import RTLProvider from '@TutorShared/components/RTLProvider';
import { ModalProvider } from '@TutorShared/components/modals/Modal';
import { createGlobalCss } from '@TutorShared/utils/style-utils';
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
          <CourseBuilderSlotProvider>
            <ModalProvider>
              <Global styles={createGlobalCss()} />
              {routers}
            </ModalProvider>
          </CourseBuilderSlotProvider>
        </ToastProvider>
      </QueryClientProvider>
    </RTLProvider>
  );
};

export default App;
