import React from 'react';
import Header from './Header';
import Filter from './Filter';
import AddonsList from './AddonsList';
import { AddonsContextProvider } from '../context/AddonsContext';

const App = () => {
	// console.log(useAddons())
	// const {allAddons = []} = useAddons();

	// const handleSelectFilter = (e) => {
	// 	console.log(e.target.value);
	// };

	// const handleFilterBtnClick = (value) => {
	// 	console.log(value, allAddons);
	// 	switch (value) {
	// 		case 'active':
	// 			console.log(allAddons.filter((item) => item.is_enabled));

	// 			console.log(value);
	// 			break;
	// 		case 'deactive':
	// 			console.log(allAddons.filter((item) => item.is_enabled !== true));

	// 			console.log(value);
	// 			break;
	// 		case 'all':
	// 			console.log(allAddons);

	// 			console.log(value);
	// 			break;
	// 	}
	// 	// console.log('clicked', btn);
	// };

	return (
		<AddonsContextProvider>
			<main className="tutor-backend-settings-addons-list tutor-dashboard-page">
				<Header/>
				<div className="tutor-addons-list-body tutor-p-30">
					<Filter/>
					<AddonsList />
				</div>
			</main>
		</AddonsContextProvider>
	);
};

export default App;
