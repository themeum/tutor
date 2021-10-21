import React, { useState } from 'react';
import { useAddonsUpdate } from '../context/AddonsContext';

const AddonCard = ({ addon, addonId }) => {
	const author = 'Themeum';
	const url = 'https://www.themeum.com';
	const { handleOnChange } = useAddonsUpdate();

	return (
		<div
			className={`tutor-addons-card ${
				addon.depend_plugins || addon.ext_required ? 'not-subscribed' : ''
			} tutor-addons-card-${addonId + 1}`}
			style={{ transitionDelay: `${100 * addonId}ms` }}
		>
			<div className="card-body tutor-px-30 tutor-py-40">
				<div className="addon-logo">
					<img src={addon.thumb_url} alt={addon.name} />
				</div>
				<div className="addon-title tutor-mt-20">
					<h5 className="text-medium-h5 color-text-primary">{addon.name}</h5>
					<p className="text-medium-small color-text-hints tutor-mt-5">
						By{' '}
						<a href={url} className="color-brand-wordpress">
							{author}
						</a>
					</p>
				</div>
				<div className="addon-des text-regular-body color-text-subsued tutor-mt-20">
					<p>{addon.description}</p>
				</div>
			</div>
			<div className=" card-footer tutor-px-30 tutor-py-25 d-flex justify-content-between align-items-center">
				<div className="addon-toggle">
					{addon.ext_required ? (
						<>
							<label className="tutor-form-toggle">
								<input
									type="checkbox"
									className="tutor-form-toggle-input"
									name={addon.basename}
									checked={!!addon.is_enabled}
									onChange={(event) => handleOnChange(event, addon.basename)}
								/>
								<span className="tutor-form-toggle-control"></span>
								<span className="tutor-form-toggle-label tutor-form-toggle-checked color-text-primary tutor-ml-5">
									Active
								</span>
							</label>
							<p className="color-text-hints text-medium-small">Required Extension(s)</p>
							{addon.ext_required.map((extension, index) => {
								return (
									<p
										className="color-text-primary text-medium-caption tutor-mt-2"
										key={index}
										dangerouslySetInnerHTML={{ __html: extension }}
									/>
								);
							})}
						</>
					) : addon.depend_plugins ? (
						<>
							<p className="color-text-hints text-medium-small">Required Plugin(s)</p>
							<p className="color-text-primary text-medium-caption tutor-mt-2">Woocommerce Subscription</p>
						</>
					) : (
						<>
							<label className="tutor-form-toggle">
								<input
									type="checkbox"
									className="tutor-form-toggle-input"
									name={addon.basename}
									checked={addon.is_enabled}
									onChange={(event) => handleOnChange(event, addon.basename)}
								/>
								<span className="tutor-form-toggle-control"></span>
								<span className="tutor-form-toggle-label tutor-form-toggle-checked color-text-primary tutor-ml-5">
									Active
								</span>
							</label>
						</>
					)}
				</div>
				<div className="addon-version text-medium-small color-text-hints">
					Version : <span className="text-bold-small color-text-primary">{addon.tutor_version}</span>
				</div>
			</div>
		</div>
	);
};

export default AddonCard;
