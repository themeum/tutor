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
	const [allAddons, setAllAddons] = useState([_tutorobject.addons_data]);

	// Render the component with initial data at on mount.
	useEffect(() => {
		const fetchAddons = async () => {
			const formData = new FormData();
			formData.set('action', 'tutor_get_all_addons');
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
	}, []);

	return (
		<AddonsContext.Provider value={allAddons}>
			<AddonsUpdateContext.Provider value={{ setAllAddons }}>{props.children}</AddonsUpdateContext.Provider>
		</AddonsContext.Provider>
	);
};
