import React, { useState } from 'react';
import { useAddons } from '../context/AddonsContext';
import AddonCard from './AddonCard';

import emptyStateImg from '../../../../images/empty-state.svg';

const AddonsList = () => {
	const { allAddons, loading } = useAddons();

	return (
		<div className={`tutor-addons-list-items tutor-mt-30 ${allAddons.length < 3 ? 'is-less-items' : ''}`}>
			{allAddons.length ? (
				allAddons.map((addon, index) => {
					return <AddonCard addon={addon} key={index} />;
				})
			) : loading ? (
				<div className="tutor-addons-loading">{/* Loading... */}</div>
			) : (
				<div className="tutor-addons-card empty-state tutor-py-20">
					<div className="card-body">
						<div className="text-medium-caption tutor-mb-20">Nothing Found!</div>
						<img src={emptyStateImg} alt="empty state illustration" />
					</div>
				</div>
			)}
		</div>
	);
};

export default AddonsList;
