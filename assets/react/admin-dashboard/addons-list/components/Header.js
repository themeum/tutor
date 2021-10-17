import React from 'react';
import { useAddons, useAddonsUpdate } from '../context/AddonsContext';

const Header = () => {
	const filterBtns = ['all', 'active', 'deactive', 'required'];
	const allAddons = useAddons();
	const { activeTab, getAddonsData } = useAddonsUpdate();

	let activeAddonCount = 0;
	let deactiveAddons = 0;
	let requiredAddons = 0;
	allAddons.forEach(addon => {
		if (true === addon.is_enabled) {
			activeAddonCount++;
		}

		return activeAddonCount;
	})
	allAddons.forEach(addon => {
		if (true !== addon.is_enabled) {
			deactiveAddons++;
		}
		return deactiveAddons;
	})
	allAddons.forEach(addon => {
		if (addon.plugins_required.length > 0 ) {
			requiredAddons++;
		}
		return requiredAddons;
	})

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
							{btn} <span className="item-count">({'active' === btn ? activeAddonCount : 'deactive' === btn ? deactiveAddons : 'required' === btn ? requiredAddons : allAddons.length })</span>
						</button>
					);
				})}
				{/* <button type="button" className="filter-btn is-active" data-tab-filter-target="all">
				All <span className="item-count">(220)</span>
			</button>
			<button type="button" className="filter-btn" data-tab-filter-target="active">
				Active<span className="item-count">(12)</span>
			</button>
			<button type="button" className="filter-btn" data-tab-filter-target="deactive">
				Deactive <span className="item-count">(5)</span>
			</button> */}
			</div>
		</header>
	);
};

export default Header;
