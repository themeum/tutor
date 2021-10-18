import React from 'react';
import { useAddons, useAddonsUpdate } from '../context/AddonsContext';

const Header = () => {
	const filterBtns = ['all', 'active', 'deactive', 'required'];
	const {addonList} = useAddons();
	const { activeTab, getAddonsData } = useAddonsUpdate();
	const activeCount = addonList?.reduce((sum, addon) => sum + Number(addon.is_enabled), 0);
	const deactiveCount = addonList?.reduce((sum, addon) => sum + Number(!addon.is_enabled), 0);
	const requiredCount = addonList?.reduce((sum, addon) => sum + Number(addon.hasOwnProperty("depend_plugins") || 0), 0);

	return (
		<header className="tutor-addons-list-header d-flex justify-content-between align-items-center tutor-px-30 tutor-py-20">
			<div className="title text-medium-h5 color-text-primary mb-md-0 mb-3">Addons List</div>
			<div className="filter-btns text-regular-body color-text-subsued">
				{filterBtns.map((btn, index) => {
					return (
						<button
							type="button"
							className={`filter-btn ${btn === activeTab ? 'is-active' : ''}`}
							key={index}
							onClick={() => getAddonsData(btn)}
						>
							{btn} <span className="item-count">({'active' === btn ? activeCount : 'deactive' === btn ? deactiveCount : 'required' === btn ? requiredCount : addonList?.length })</span>
						</button>
					);
				})}
			</div>
		</header>
	);
};

export default Header;
