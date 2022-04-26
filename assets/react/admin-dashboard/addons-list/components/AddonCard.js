import React from 'react';
import { useAddonsUpdate } from '../context/AddonsContext';

const { __ } = wp.i18n;

const AddonCard = ({ addon, addonId }) => {
	const { handleOnChange, addonLoading } = useAddonsUpdate();

	return (
		<div className="tutor-col-lg-6 tutor-col-xl-4 tutor-col-xxl-3 tutor-mb-32">
			<div
				className={`tutor-addons-card tutor-d-flex tutor-flex-column ${
					addon.plugins_required.length > 0 ? 'not-subscribed' : ''
				} tutor-addons-card-${addonId + 1}`}
				style={{ transitionDelay: `${100 * addonId}ms` }}
			>
				<div className="card-body tutor-px-32 tutor-py-40">
					<div className="addon-logo">
						<img src={addon.thumb_url} alt={addon.name} />
					</div>
					<div className="addon-title tutor-mt-20">
						<div className="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mb-4">{addon.name}</div>
					</div>
					<div className="addon-des tutor-fs-7 tutor-color-secondary tutor-mt-20">{addon.description}</div>
				</div>

				<div className="card-footer tutor-px-32 tutor-py-20 tutor-d-flex tutor-justify-between tutor-align-items-center tutor-mt-auto">
					<div className="tutor-fs-7 tutor-fw-medium tutor-color-muted">
						<div className="tutor-color-muted tutor-fs-7 tutor-fw-medium tutor-d-flex">
							<span>
								{addon.plugins_required?.length > 0 ?
									__('Required Plugin(s)', 'tutor')
								: addon.ext_required?.length > 0 ?
									__('Required for Push Notification', 'tutor')
								:
									__('No extra plugin required', 'tutor')
								}
							</span>
							{addon.ext_required && addon.ext_required ?
								<>
									<div className="tooltip-wrap tooltip-icon">
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
								</>
							: addon.depend_plugins ?
									<>
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
									</>
								: ''
							}
						</div>
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
								<span className="tutor-form-toggle-control" area-hidden="true"></span>
							</label>
						</div>
					)}
				</div>
			</div>
		</div>
	);
};

export default AddonCard;
