import { useState } from 'react';
import { useRoutes } from 'react-router-dom';
import { Global } from '@emotion/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

import ToastProvider from '@TutorShared/atoms/Toast';

import { ModalProvider } from '@TutorShared/components/modals/Modal';
import RTLProvider from '@TutorShared/components/RTLProvider';

import { SVGIconConfigProvider } from '@TutorShared/contexts/SVGIconConfigContext';
import { createGlobalCss } from '@TutorShared/utils/style-utils';

import routes from '@CourseBuilderConfig/routes';
import { CourseBuilderSlotProvider } from '@CourseBuilderContexts/CourseBuilderSlotContext';

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
        <SVGIconConfigProvider>
          <ToastProvider position="bottom-center">
            <CourseBuilderSlotProvider>
              <ModalProvider>
                <Global styles={createGlobalCss()} />
                {routers}
              </ModalProvider>
            </CourseBuilderSlotProvider>
          </ToastProvider>
        </SVGIconConfigProvider>
      </QueryClientProvider>
    </RTLProvider>
  );
};

export default App;
