import React, { useState } from 'react';
import { useAddons, useAddonsUpdate } from '../context/AddonsContext';

const Header = () => {
	const allAddons = useAddons();
	const { setAllAddons } = useAddonsUpdate();

	console.log(allAddons);

	const filterBtns = ['all', 'active', 'deactive'];

	const [isActive, setIsActive] = useState(false);

	const [activeTab, setActiveTab] = useState({
		active: 'all',
	});

	const handleFilterBtnClick = (value) => {
		console.log(value);

		switch (value) {
			case 'active':
				const active = allAddons.filter((item) => item.is_enabled);
				// setAllAddons(active);
				console.log(value, active);
				break;
			case 'deactive':
				const deactive = allAddons.filter((item) => item.is_enabled !== true);
				// setAllAddons(deactive);
				console.log(value, deactive);
				break;
			case 'all':
				const all = allAddons;
				// setAllAddons(all);
				console.log(value, all);
				break;
		}
	};

	return (
		<header className="tutor-addons-list-header d-flex justify-content-between align-items-center tutor-px-30 tutor-py-20">
			<div className="title text-medium-h5 color-text-primary mb-md-0 mb-3">Addons List</div>
			<div className="filter-btns text-regular-body color-text-subsued">
				{filterBtns.map((btn, index) => {
					return (
						<button
							type="button"
							className={`filter-btn`}
							data-tab-filter-target={btn}
							key={index}
							// onClick={() => handleFilterBtnClick(btn)}
							onClick={() => handleFilterBtnClick(btn)}
						>
							{btn} <span className="item-count">(220)</span>
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
