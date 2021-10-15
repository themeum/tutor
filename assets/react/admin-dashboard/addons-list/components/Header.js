import React from 'react';

const Header = () => {
    return (
        <header className="tutor-addons-list-header d-flex justify-content-between align-items-center tutor-px-30 tutor-py-20">
		<div className="title text-medium-h5 color-text-primary mb-md-0 mb-3">Addons List</div>
		<div className="filter-btns text-regular-body color-text-subsued">
			<button type="button" className="filter-btn is-active" data-tab-filter-target="all">
				All <span className="item-count">(220)</span>
			</button>
			<button type="button" className="filter-btn" data-tab-filter-target="active">
				Active<span className="item-count">(12)</span>
			</button>
			<button type="button" className="filter-btn" data-tab-filter-target="deactive">
				Deactive <span className="item-count">(5)</span>
			</button>
		</div>
	</header>
    );
}

export default Header;