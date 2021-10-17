import React, { createContext, useContext, useState, useEffect } from 'react';

// Custom contexts.
const AddonsContext = createContext();
const AddonsUpdateContext = createContext();

/**
 * Custom hook useAddons
 * @returns AddonsContext
 */
export const useAddons = () => {
	return useContext(AddonsContext);
};

/**
 * Custom hook useAddonsUpdate
 * @returns AddonsUpdateContext
 */
export const useAddonsUpdate = () => {
	return useContext(AddonsUpdateContext);
};

/**
 * Addons Context Provider
 * @param {*} props
 * @returns All ContextProviders with children
 */
export const AddonsContextProvider = (props) => {
	const [allAddons, setAllAddons] = useState([]);
	const [activeTab, setActiveTab] = useState('all');

	// Render the component with initial data at on mount.
	useEffect(() => {
		const fetchAddons = async () => {
			const formData = new FormData();
			formData.set('action', 'tutor_get_all_addons');
			formData.set('btn', activeTab);
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
						setAllAddons(data);
					}
				}
			} catch (error) {
				console.log(error);
			}
		};
		fetchAddons();
	}, [activeTab]);

	const handleOnChange = (event, addonName) => {
		let value = event.target.checked ? 1 : 0;

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
						setAllAddons(data);
					}
				}
			} catch (error) {
				console.log(error);
			}
		};
		toggleAddonStatus();
	};

	const getAddonsData = (btn) => {
		if ('active' === btn) {
			setActiveTab('active');
		} else if ( 'deactive' === btn ) {
			setActiveTab('deactive');
		} else if ('all' === btn) {
			setActiveTab('all');
		} else if ('required' === btn) {
			setActiveTab('required');
		}
	}

	return (
		<AddonsContext.Provider value={allAddons}>
			<AddonsUpdateContext.Provider value={{ activeTab, getAddonsData, setActiveTab, setAllAddons, handleOnChange }}>
				{props.children}
			</AddonsUpdateContext.Provider>
		</AddonsContext.Provider>
	);
};
