import React from 'react';
import { useAddons, useAddonsUpdate } from '../context/AddonsContext';

const { __ } = wp.i18n;
const capitalize = ( string ) => {
	return string.charAt(0).toUpperCase() + string.slice(1);
}
const Header = () => {
	const filterBtns = ['all', 'active', 'deactive', 'required'];
	const { addonList } = useAddons();
	const { activeTab, getTabStatus } = useAddonsUpdate();
	const activeCount = addonList?.reduce((sum, addon) => sum + Number(addon.is_enabled), 0);
	const deactiveCount = addonList?.reduce((sum, addon) => sum + Number(!addon.is_enabled), 0);
	const requiredCount = addonList?.reduce((sum, addon) => sum + Number(addon.hasOwnProperty('depend_plugins') || 0), 0);

	return (
		<header className="tutor-wp-dashboard-header tutor-px-24 tutor-mb-24">
			<div className="tutor-row tutor-align-lg-center">
				<div className="tutor-col-lg">
					<div className="tutor-p-12">
						<span className="tutor-fs-5 tutor-fw-medium tutor-mr-16">
							{__('Addons', 'tutor')}
						</span>
					</div>
				</div>
				
				<div className="tutor-col-lg-auto">
					<ul className="tutor-nav tutor-nav-admin">
						{filterBtns.map((btn, index) => {
							return (
								<li className="tutor-nav-item" key={index}>
									<a className={`tutor-nav-link${btn === activeTab ? ' is-active' : ''}`} href="#" onClick={() => getTabStatus(btn)}>
										{ capitalize(btn) }{' '}
										<span className="tutor-ml-4">
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
									</a>
								</li>
							);
						})}
					</ul>
				</div>
			</div>
		</header>
	);
};

export default Header;
