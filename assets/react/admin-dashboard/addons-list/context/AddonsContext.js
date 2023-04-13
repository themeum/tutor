import React, { createContext, useContext, useEffect, useRef, useState } from 'react';

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
	const [loading, setLoding] = useState(true);
	const [allAddons, setAllAddons] = useState([]);
	const [activeTab, setActiveTab] = useState('all');
	const initialRenderRef = useRef(false);
	const allAddonsRef = useRef(null);
	const [addons, setAddons] = useState([]);
	const [addonLoading, setAddonLoading] = useState({});
	const [search, setSearch] = useState('');

	/**
	 *
	 * @param {Array} data
	 * @returns {Object}
	 */
	function getAddonsState(data) {
		return data?.reduce((store, obj) => {
			store = { ...store, [obj.basename]: Boolean(obj.is_enabled) };
			return store;
		}, {});
	}

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
					const addonsLoadingState = getAddonsState(data);
					// setAddonLoading(addonsLoadingState);
					setAllAddons(data);
					allAddonsRef.current = data;
					setLoding(false);
				}
			}
		} catch (error) {
			console.log(error);
			setLoding(false);
		}
	};

	// Render the component with initial data at on mount.
	useEffect(() => {
		fetchAddons();
	}, []);

	useEffect(() => {
		if (initialRenderRef.current) {
			if (activeTab === 'all') {
				setAllAddons(allAddonsRef.current);
			} else {
				const activeAddons = allAddonsRef.current.filter((addon) => {
					if (activeTab === 'active') return addon.is_enabled;
					else if (activeTab === 'deactive') return !addon.is_enabled;
					else if (activeTab === 'required') return addon?.depend_plugins;
				});
				setAllAddons(activeAddons);
			}
		} else if (!initialRenderRef.current) initialRenderRef.current = true;
	}, [activeTab]);

	useEffect(() => {
		setAddons(allAddonsRef.current);
	});

	function filterList(list, fn) {
		return list.filter(fn);
	}

	const handleOnChange = (event, addonBaseName) => {
		const { checked } = event.target;
		setAddonLoading((prev) => ({ ...prev, [addonBaseName]: true }));

		const updatedAddonList = allAddonsRef.current.map((addon) => {
			if (addon.basename === addonBaseName) return { ...addon, is_enabled: checked };
			return addon;
		});

		const filterUpdatedAddonList = filterList(updatedAddonList, (item) =>
			item.name.toLowerCase().includes(search.toLowerCase()),
		);

		if (activeTab === 'active') {
			const activeAddons = filterList(
				search.trim() === '' ? updatedAddonList : filterUpdatedAddonList,
				(addon) => addon.is_enabled,
			);

			setAllAddons(activeAddons);
		} else if (activeTab === 'deactive') {
			const deActiveAddons = filterList(
				search.trim() === '' ? updatedAddonList : filterUpdatedAddonList,
				(addon) => !addon.is_enabled,
			);

			setAllAddons(deActiveAddons);
		} else if (activeTab === 'required') {
			const requiredAddons = filterList(
				search.trim() === '' ? updatedAddonList : filterUpdatedAddonList,
				(addon) => addon?.depend_plugins,
			);

			setAllAddons(requiredAddons);
		} else if (activeTab === 'all') {
			setAllAddons(filterUpdatedAddonList);
		}

		allAddonsRef.current = updatedAddonList;

		const toggleAddonStatus = async () => {
			const prevData = getAddonsState(updatedAddonList);
			// console.log(prevData);

			const formData = new FormData();
			formData.set('action', 'addon_enable_disable');
			formData.set('isEnable', Number(checked));
			formData.set('addonFieldName', prevData);
			formData.set('addonFieldNames', JSON.stringify(prevData));
			formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);

			try {
				await fetch(_tutorobject.ajaxurl, {
					method: 'POST',
					body: formData,
				});
				setAddonLoading(prevData);
			} catch (error) {
				console.log(error);
			}
		};
		toggleAddonStatus();
	};

	const getTabStatus = (btn) => {
		switch (btn) {
			case 'active':
				setActiveTab('active');
				break;
			case 'deactive':
				setActiveTab('deactive');
				break;
			case 'required':
				setActiveTab('required');
				break;
			case 'all':
				setActiveTab('all');
				break;

			default:
				setActiveTab('all');
				break;
		}
	};

	useEffect(() => {
		const searchValue = search.trim();
		if (searchValue) {
			const filteredAddons = allAddonsRef.current.filter((addon) =>
				addon.name.toLowerCase().includes(searchValue.toLowerCase()),
			);
			setAllAddons(filteredAddons);
		} else {
			setAllAddons(allAddonsRef.current ?? []);
		}
	}, [search]);

	return (
		<AddonsContext.Provider value={{ allAddons, addonList: addons, loading }}>
			<AddonsUpdateContext.Provider
				value={{
					activeTab,
					getTabStatus,
					setActiveTab,
					setAllAddons,
					handleOnChange,
					addonLoading,
					search,
					setSearch,
				}}
			>
				{props.children}
			</AddonsUpdateContext.Provider>
		</AddonsContext.Provider>
	);
};
