import React, { useCallback, useState } from 'react';
import { useAddonsUpdate } from '../context/AddonsContext';

const debounce = (fn, delay = 500) => {
	let timer = null;
	return function() {
		const context = this,
			args = arguments;
		clearTimeout(timer);
		timer = setTimeout(() => {
			fn.apply(context, args);
		}, delay);
	};
};

const Search = () => {
	const { filterAddons } = useAddonsUpdate();
	const [search, setSearch] = useState('');
	const setDebouncedSearch = useCallback(
		debounce((value) => {
			filterAddons(value);
		}),
		[]
	);

	const handleChange = (e) => {
		const { value } = e.target;
		setSearch(value);
		setDebouncedSearch(value);
	};

	return (
		<div className="tutor-addons-list-select-filter d-flex justify-content-end align-items-center">
			{/* <div className="filter-custom-field d-flex">
				<select name="filter-select" className="tutor-form-select" onChange={(e) => handleSelectFilter(e)}>
					<option value="all">All</option>
					<option value="active">Active</option>
					<option value="deactive">Deactive</option>
				</select>
				<input type="search" className="filter-search tutor-form-control" placeholder="Search by name" />
			</div>

			<span className="ttr-search-filled"></span>
			<button type="button" className="search-btn tutor-btn tutor-is-sm tutor-is-outline">
				Filter
			</button> */}
			<div className="tutor-input-group tutor-form-control-has-icon tutor-form-control-has-icon-right">
				<span className="ttr-search-filled tutor-input-group-icon"></span>
				<input
					type="search"
					className="tutor-form-control"
					placeholder="Search…"
					value={search}
					onChange={handleChange}
				/>
			</div>
		</div>
	);
};

export default Search;
