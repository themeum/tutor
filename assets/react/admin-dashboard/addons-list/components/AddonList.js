import React from 'react';
import { useAddons } from '../context/AddonsContext';
import AddonCard from './AddonCard';

const { __ } = wp.i18n;

const emptyStateImg = `${_tutorobject.tutor_url}assets/images/addon-empty-state.svg`;

const AddonList = () => {
	const { allAddons, loading } = useAddons();

	return (
		<div
			className={`tutor-addons-list-items tutor-row tutor-gx-xxl-4 tutor-mt-32 ${allAddons.length < 3 ? 'is-less-items' : ''} ${
				allAddons.length ? 'is-active' : ''
			}`}
		>
			{allAddons.length ? (
				allAddons.map((addon, index) => {
					return <AddonCard addon={addon} key={index} addonId={index} />;
				})
			) : loading ? (
				<div className="tutor-col-12 tutor-text-center tutor-py-60">
					<div className="tutor-spinner" area-hidden="true"></div>
				</div>	
			) : (
				<div className="tutor-col-12">
					<div className="tutor-addons-card tutor-p-32">
						<div className="tutor-d-flex tutor-flex-column tutor-justify-center tutor-text-center">
							<div className="tutor-mb-32">
								<img src={emptyStateImg} alt={__('Empty State Illustration', 'tutor')} />
							</div>
							<div className="tutor-fs-6 tutor-color-secondary">{__('No Addons Found!', 'tutor')}</div>
						</div>
					</div>
				</div>
			)}
		</div>
	);
};

export default AddonList;