import React from 'react';
import { useAddons } from '../context/AddonsContext';
import AddonCard from './AddonCard';

const AddonsList = () => {
	const allAddons = useAddons();

	console.log(allAddons);

	return (
		<div className="tutor-addons-list-items tutor-mt-40">
			{allAddons.map((addon, index) => {
				return <AddonCard addon={addon} key={index} />;
			})}
		</div>
	);
};

export default AddonsList;
