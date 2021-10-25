import React, { useState } from 'react';
import { useAddonsUpdate } from '../context/AddonsContext';

const AddonCard = ({ addon, addonId }) => {
	const author = 'Themeum';
	const url = 'https://www.themeum.com';
	const { handleOnChange } = useAddonsUpdate();

	return (
		<div
			className={`tutor-addons-card ${
				addon.depend_plugins ? 'not-subscribed' : ''
			} tutor-addons-card-${addonId + 1}`}
			style={{ transitionDelay: `${100 * addonId}ms` }}
		>
			<div className="card-body tutor-px-30 tutor-py-40">
				<div className="addon-logo">
					<img src={addon.thumb_url} alt={addon.name} />
				</div>
				<div className="addon-title tutor-mt-20">
					<h5 className="text-medium-h5 color-text-primary tutor-mb-4">{addon.name}</h5>
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
			<div className=" card-footer tutor-px-30 tutor-py-20 d-flex justify-content-between align-items-center">
				<div className="text-medium-small color-text-hints">	
					<p style={{margin: '2px 0'}} className="color-text-hints text-medium-small">{addon.ext_required ? 'Required for Push Notification' : addon.depend_plugins ? 'Required Plugin(s)' : 'No additional plugin(s) required'}</p>
						{addon.ext_required &&
							addon.ext_required ? addon.ext_required.map((extension, index) => {
								return (
									<p style={{marginTop: '2px', marginBottom: 0}} className="tutor-bs-d-flex color-text-primary text-medium-caption" key={index}>
										<span style={{fontSize: '24px', marginLeft:'-7px'}} className="ttr-bullet-point-filled"></span>
										<span style={{fontSize:'14px'}} dangerouslySetInnerHTML={{ __html: extension }} />
									</p>	
								);
							}) 
							: 
							addon.depend_plugins ? addon.plugins_required.map((plugin, index) => {
								return (
									<p style={{marginTop: '2px'}} className="tutor-bs-d-flex color-text-primary text-medium-caption" key={index}>
										<span style={{fontSize: '24px', marginLeft:'-7px'}} className="addon-list-icon ttr-bullet-point-filled"></span>
										<span style={{fontSize:'14px'}}>{plugin}</span>
									</p>
								);
							})
							:
							''
						}
					
				</div>
				{!addon.depend_plugins &&
				<div className="addon-toggle">
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
				</div>
				}
			</div>
		</div>
	);
};

export default AddonCard;
