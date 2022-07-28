import React from 'react';
import Header from './Header';
import Search from './Search';
import AddonList from './AddonList';
import { AddonsContextProvider } from '../context/AddonsContext';

const App = () => {
	return (
		<AddonsContextProvider>
			<div className="tutor-backend-settings-addons-list tutor-dashboard-page">
				<div className="tutor-admin-wrap">
					<Header/>
					<div className="tutor-admin-body">
						<div className="tutor-addons-list-body">
							<Search/>
							<AddonList />
						</div>
					</div>
				</div>
			</div>
		</AddonsContextProvider>
	);
};

export default App;
