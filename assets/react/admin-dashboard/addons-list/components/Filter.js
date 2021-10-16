import React from 'react';

const Filter = ({ handleSelectFilter }) => {
	return (
		<div className="tutor-addons-list-select-filter d-flex justify-content-end align-items-center tutor-mt-5">
			<div className="filter-custom-field d-flex">
				<select name="filter-select" className="tutor-form-select" onChange={(e) => handleSelectFilter(e)}>
					<option value="all">All</option>
					<option value="active">Active</option>
					<option value="deactive">Deactive</option>
				</select>
				<input type="search" className="filter-search tutor-form-control" placeholder="Search by name" />
			</div>
			<button type="button" className="search-btn tutor-btn tutor-is-sm tutor-is-outline">
				Filter
			</button>
		</div>
	);
};

export default Filter;
