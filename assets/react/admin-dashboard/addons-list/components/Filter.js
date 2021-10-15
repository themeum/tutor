import React from 'react';

const Filter = () => {
    return (
        <div className="tutor-addons-list-body tutor-p-30">
            <div className="tutor-addons-list-select-filter d-flex justify-content-end align-items-center tutor-mt-5">
                <div className="filter-custom-field d-flex">
                    <select name="filter-select" className="tutor-form-select">
                        <option value="all" selected>All</option>
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                    </select>
                    <input
                        type="search"
                        className="filter-search tutor-form-control"
                        placeholder="Search by name"
                    />
                </div>
                <button type="button" className="search-btn tutor-btn tutor-is-sm tutor-is-outline">Filter</button>
            </div>
	    </div>
    );
}

export default Filter;