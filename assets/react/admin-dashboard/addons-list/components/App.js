import React from 'react';
import Header from './Header';
import Search from './Search';
import AddonList from './AddonList';
import { AddonsContextProvider } from '../context/AddonsContext';

const App = () => {
	return (
		<AddonsContextProvider>
			<main className="tutor-backend-settings-addons-list tutor-dashboard-page">
				<Header/>
				<div className="tutor-addons-list-body tutor-p-32">
					<Search/>
					<AddonList />
				</div>
			</main>
		</AddonsContextProvider>
	);
};

export default App;
