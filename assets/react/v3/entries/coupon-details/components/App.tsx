import ToastProvider from '@Atoms/Toast';
import { ModalProvider } from '@Components/modals/Modal';
import RTLProvider from '@CourseBuilderComponents/layouts/RTLProvider';
import { createGlobalCss } from '@Utils/style-utils';
import { Global } from '@emotion/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useState } from 'react';
import { QueryParamProvider } from 'use-query-params';
import { ReactRouter6Adapter } from 'use-query-params/adapters/react-router-6';
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
			<QueryParamProvider adapter={ReactRouter6Adapter}>
				<QueryClientProvider client={queryClient}>
					<ToastProvider position="bottom-center">
						<ModalProvider>
							<Global styles={createGlobalCss()} />
							<Main />
						</ModalProvider>
					</ToastProvider>
				</QueryClientProvider>
			</QueryParamProvider>
		</RTLProvider>
	);
}

export default App;
