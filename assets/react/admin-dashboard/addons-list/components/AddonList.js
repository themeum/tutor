import React from 'react';
import { useAddons } from '../context/AddonsContext';
import AddonCard from './AddonCard';

const { __ } = wp.i18n;

const emptyStateImg = `${_tutorobject.tutor_url}assets/images/addon-empty-state.svg`;

const AddonList = () => {
	const { allAddons, loading } = useAddons();

	return (
		<div
			className={`tutor-addons-list-items tutor-row tutor-gx-xxl-4 tutor-mt-30 ${allAddons.length < 3 ? 'is-less-items' : ''} ${
				allAddons.length ? 'is-active' : ''
			}`}
		>
			{allAddons.length ? (
				allAddons.map((addon, index) => {
					return <AddonCard addon={addon} key={index} addonId={index} />;
				})
			) : loading ? (
				<div className="tutor-col-12">
					<div className="tutor-addons-loading" area-hidden="true"></div>
				</div>	
			) : (
				<div className="tutor-col-12">
					<div className="tutor-addons-card tutor-p-30">
						<div className="tutor-d-flex tutor-flex-column tutor-justify-content-center tutor-text-center">
							<div className="tutor-mb-30">
								<img src={emptyStateImg} alt={__('Empty State Illustration', 'tutor')} />
							</div>
							<div className="tutor-text-regular-h6 tutor-color-text-subsued">{__('No Addons Found!', 'tutor')}</div>
						</div>
					</div>
				</div>
			)}
		</div>
	);
};

export default AddonList;
