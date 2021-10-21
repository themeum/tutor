import React from 'react';
import Header from './Header';
import Search from './Search';
import AddonsList from './AddonsList';
import { AddonsContextProvider } from '../context/AddonsContext';

const App = () => {
	return (
		<AddonsContextProvider>
			<main className="tutor-backend-settings-addons-list tutor-dashboard-page">
				<Header/>
				<div className="tutor-addons-list-body tutor-p-30">
					<Search/>
					<AddonsList />
				</div>
			</main>
		</AddonsContextProvider>
	);
};

export default App;
