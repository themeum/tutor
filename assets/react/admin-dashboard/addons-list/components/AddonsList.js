import React from 'react';
import { useAddons } from '../context/AddonsContext';
import AddonCard from './AddonCard';

const emptyStateImg = `${_tutorobject.tutor_url}assets/images/empty-state.svg`;

const AddonsList = () => {
	const { allAddons, loading } = useAddons();

	return (
		<div
			className={`tutor-addons-list-items tutor-mt-30 ${allAddons.length < 3 ? 'is-less-items' : ''} ${
				allAddons.length ? 'is-active' : ''
			}`}
		>
			{allAddons.length ? (
				allAddons.map((addon, index) => {
					return <AddonCard addon={addon} key={index} addonId={index} />;
				})
			) : loading ? (
				<div className="tutor-addons-loading"></div>
			) : (
				<div className="tutor-addons-card empty-state tutor-py-20">
					<div className="card-body">
						<img src={emptyStateImg} alt="empty state illustration" />
						<div className="text-medium-caption tutor-mb-20">Nothing Found!</div>
					</div>
				</div>
			)}
		</div>
	);
};

export default AddonsList;
