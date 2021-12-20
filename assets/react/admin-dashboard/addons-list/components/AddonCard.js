import React from 'react';
import { useAddonsUpdate } from '../context/AddonsContext';

const { __ } = wp.i18n;

const AddonCard = ({ addon, addonId }) => {
	const author = 'Themeum';
	const url = 'https://www.themeum.com';
	const { handleOnChange } = useAddonsUpdate();

	return (
		<div
			className={`tutor-addons-card ${
				addon.plugins_required.length > 0 ? 'not-subscribed' : ''
			} tutor-addons-card-${addonId + 1}`}
			style={{ transitionDelay: `${100 * addonId}ms` }}
		>
			<div className="card-body tutor-px-30 tutor-py-40">
				<div className="addon-logo">
					<img src={addon.thumb_url} alt={addon.name} />
				</div>
				<div className="addon-title tutor-mt-20">
					<div className="text-medium-h5 color-text-primary tutor-mb-4">{addon.name}</div>
					<div className="text-medium-small color-text-hints tutor-mt-5">
						By{' '}
						<a href={url} className="color-brand-wordpress">
							{author}
						</a>
					</div>
				</div>
				<div className="addon-des text-regular-body color-text-subsued tutor-mt-20">{addon.description}</div>
			</div>
			<div className=" card-footer tutor-px-30 tutor-py-20 d-flex justify-content-between align-items-center">
				<div className="text-medium-small color-text-hints">
					<div className="extra-plugins color-text-hints text-medium-small">
						{addon.plugins_required?.length > 0
							? __('Required Plugin(s)', 'tutor')
							: addon.ext_required?.length > 0
							? __('Required for Push Notification', 'tutor')
							: __('No extra plugin required', 'tutor')}
					</div>
					{addon.ext_required && addon.ext_required
						? addon.ext_required.map((extension, index) => {
								return (
									<div className="extension-wrapper tutor-bs-d-flex color-text-primary text-medium-caption" key={index}>
										<span className="addon-icon ttr-bullet-point-filled"></span>
										<span className="plugin-title" dangerouslySetInnerHTML={{ __html: extension }} />
									</div>
								);
						  })
						: addon.depend_plugins
						? addon.plugins_required.map((plugin, index) => {
								return (
									<div className="plugins-wrapper tutor-bs-d-flex color-text-primary text-medium-caption" key={index}>
										<span className="addon-icon ttr-bullet-point-filled"></span>
										<span className="plugin-title">{plugin}</span>
									</div>
								);
						  })
						: ''}
				</div>
				{addon.plugins_required?.length === 0 && (
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
								{__('Active', 'tutor')}
							</span>
						</label>
					</div>
				)}
			</div>
		</div>
	);
};

export default AddonCard;
