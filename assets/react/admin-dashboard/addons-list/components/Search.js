import React from 'react';
import { useAddonsUpdate } from '../context/AddonsContext';
const { __ } = wp.i18n;

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
	const { search, setSearch } = useAddonsUpdate();

	const handleChange = (e) => {
		const { value } = e.target;
		setSearch(value);
	};

	return (
		<div className="tutor-addons-list-select-filter">
			<div className="tutor-form-wrap">
				<span className="tutor-icon-search tutor-form-icon" area-hidden="true" />
				<input
					type="search"
					className="tutor-form-control"
					placeholder={__('Search…', 'tutor')}
					value={search}
					onChange={handleChange}
				/>
			</div>
		</div>
	);
};

export default Search;
