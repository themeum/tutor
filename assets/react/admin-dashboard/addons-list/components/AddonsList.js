import React from 'react';
import { useAddons } from '../context/AddonsContext';
import AddonCard from './AddonCard';

const AddonsList = () => {
	const { allAddons, loading } = useAddons();
	return (
		<div className="tutor-addons-list-items tutor-mt-30">
			{allAddons.length ? (
				allAddons.map((addon, index) => {
					return <AddonCard addon={addon} key={index} />;
				})
			) : loading ? (
				<p>Loading...</p>
			) : (
				<p>Empty</p>
			)}
		</div>
	);
};

export default AddonsList;
