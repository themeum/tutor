import React, {useState} from 'react';
import { useAddonsUpdate } from '../context/AddonsContext';

const AddonCard = ({addon}) => {
	const [isChecked, setIsChecked] = useState(addon.is_enabled);
	const [isDataAddonActive, setIsDataAddonActive] = useState(isChecked);
	const author = 'Themeum';
	const url = 'https://www.themeum.com';
	const { setAllAddons } = useAddonsUpdate();

	// console.log('before Checked', isChecked);

	const handleOnChange = (event, addonName) => {
		let value = event.target.checked;
		console.log('before Checked', isChecked, 'check',value);
		//setIsChecked(value);
		//setIsDataAddonActive(!isChecked);

		//console.log(' after set Checked', isChecked, 'check',value);

		const toggleAddonStatus = async () => {
			const formData = new FormData();
			formData.set('action', 'addon_enable_disable');
			formData.set('isEnable', value);
			formData.set('addonFieldName', addonName);
			formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);

			try {
				const addons = await fetch(_tutorobject.ajaxurl, {
					method: 'POST',
					body: formData,
				});

				if (addons.ok) {
					const response = await addons.json();
					const data = response.data.addons;

					if (data && data.length) {
						//setAllAddons(data);
					}
				}
			} catch (error) {
				console.log(error);
			}
		};
		toggleAddonStatus();
	}

	return (
		<div
			className={`tutor-addons-card ${addon.depend_plugins || addon.ext_required ? 'not-subscribed' : ''}`}
			data-addon-active={isDataAddonActive}
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
							<p className="color-text-hints text-medium-small">Required Extension(s)</p>
							{
								addon.ext_required.map( (extension, index) => {
									return <p className="color-text-primary text-medium-caption tutor-mt-2" key={index} dangerouslySetInnerHTML={{ __html:extension}} />
								})
							}
							
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
									checked={isChecked}
									onChange={(event) => {handleOnChange( event, addon.basename )}}
								/>
								<span className="tutor-form-toggle-control"></span>
								<span className="tutor-form-toggle-label color-text-primary tutor-ml-5">Active</span>
							</label>
						</>
					)}
				</div>
				<div className="addon-version text-medium-small color-text-hints">
					Version : <span className="text-bold-small color-text-primary">{addon.version}</span>
				</div>
			</div>
		</div>
	);
};

export default AddonCard;
