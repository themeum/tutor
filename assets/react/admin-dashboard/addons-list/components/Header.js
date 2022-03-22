import React from 'react';
import { useAddons, useAddonsUpdate } from '../context/AddonsContext';

const { __ } = wp.i18n;

const Header = () => {
	const filterBtns = ['all', 'active', 'deactive', 'required'];
	const { addonList } = useAddons();
	const { activeTab, getTabStatus } = useAddonsUpdate();
	const activeCount = addonList?.reduce((sum, addon) => sum + Number(addon.is_enabled), 0);
	const deactiveCount = addonList?.reduce((sum, addon) => sum + Number(!addon.is_enabled), 0);
	const requiredCount = addonList?.reduce((sum, addon) => sum + Number(addon.hasOwnProperty('depend_plugins') || 0), 0);

	return (
		<header className="tutor-addons-list-header tutor-d-lg-flex tutor-justify-content-between tutor-align-items-center tutor-px-32 tutor-py-16">
			<div className="title tutor-fs-5 tutor-fw-medium tutor-mb-lg-0 tutor-mb-4">
				{__('Add-ons', 'tutor')}
			</div>
			<div className="filter-btns tutor-fs-6 tutor-color-black-60">
				{filterBtns.map((btn, index) => {
					return (
						<button
							type="button"
							className={`filter-btn ${btn === activeTab ? 'is-active' : ''}`}
							key={index}
							onClick={() => getTabStatus(btn)}
						>
							{btn}{' '}
							<span className="item-count">
								(
								{'active' === btn
									? activeCount
									: 'deactive' === btn
									? deactiveCount
									: 'required' === btn
									? requiredCount
									: addonList?.length}
								)
							</span>
						</button>
					);
				})}
			</div>
		</header>
	);
};

export default Header;
