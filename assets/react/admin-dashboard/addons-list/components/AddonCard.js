import React from 'react';
import { useAddonsUpdate } from '../context/AddonsContext';

const { __ } = wp.i18n;

const AddonCard = ({ addon, addonId }) => {
	const { handleOnChange, addonLoading } = useAddonsUpdate();

	return (
		<div className="tutor-col-lg-6 tutor-col-xl-4 tutor-col-xxl-3 tutor-mb-32">
			<div
				className={`tutor-card tutor-card-md tutor-addon-card ${
					addon.plugins_required.length > 0 ? 'not-subscribed' : ''
				} tutor-addon-card-${addonId + 1}`}
				style={{ transitionDelay: `${100 * addonId}ms` }}
			>
				<div className="tutor-card-body">
					<div className="tutor-addon-logo tutor-mb-32">
						<div className="tutor-ratio tutor-ratio-1x1">
							<img src={addon.thumb_url} alt={addon.name} />
						</div>
					</div>
					<div className="tutor-addon-title tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mb-20">
						{addon.name}
					</div>
					<div className="tutor-addon-description tutor-fs-7 tutor-color-secondary">
						{addon.description}
					</div>
				</div>

				<div className="tutor-card-footer tutor-d-flex tutor-justify-between tutor-align-center tutor-mt-auto">
					<div className="tutor-fs-7 tutor-fw-medium tutor-color-muted">
						<div className="tutor-color-muted tutor-fs-7 tutor-fw-medium tutor-d-flex tutor-align-center">
							<span>
								{addon.plugins_required?.length > 0 ?
									__('Required Plugin(s)', 'tutor')
								: addon.ext_required?.length > 0 ?
									__('Required for Push Notification', 'tutor')
								: addon.required_settings === true ?
									addon.required_title ? addon.required_title : ''
								:
									__('No extra plugin required', 'tutor')
								}
							</span>
							{addon.ext_required?.length > 0 ?
								<div className="tooltip-wrap tooltip-icon tutor-mr-8">
									<span className="tooltip-txt tooltip-top">
										{addon.ext_required.map((extension, index) => {
											return (
												<div key={index}>
													<span dangerouslySetInnerHTML={{ __html: extension }} />
												</div>
											);
										})}
									</span>
								</div>
								
							: addon.depend_plugins && addon.plugins_required.length ?
								<div className="tooltip-wrap tooltip-icon">
									<span className="tooltip-txt tooltip-top">
										{
											addon.plugins_required.map((plugin, index) => {
												return (
													<div key={index}>
														<span>{plugin}</span>
													</div>
												);
											})
										}
									</span>
								</div>
							: addon.required_settings === true && addon.required_message ?
								<div className="tooltip-wrap tooltip-icon">
									<span className="tooltip-txt tooltip-top">
										<div>
											<span>{addon.required_message ? addon.required_message : ''}</span>
										</div>
									</span>
								</div>	
							: ''
							}
						</div>
					</div>

					{
						!addon.disable_on_off && addon.plugins_required?.length === 0 && !addon.required_settings ?
							<div className="addon-toggle">
								<label className="tutor-form-toggle">
									<input
										type="checkbox"
										className="tutor-form-toggle-input"
										name={addon.basename}
										checked={addon.is_enabled}
										onChange={(event) => handleOnChange(event, addon.basename)}
									/>
									<span className="tutor-form-toggle-control" area-hidden="true"></span>
								</label>
							</div>
						:''
					}
				</div>
			</div>
		</div>
	);
};

export default AddonCard;
