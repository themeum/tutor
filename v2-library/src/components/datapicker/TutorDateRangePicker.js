import { differenceInDays } from 'date-fns';
import React, { useEffect, useState } from 'react';
import DatePicker, { CalendarContainer } from 'react-datepicker';
import { CustomInput } from '../CustomInput';
const { __, _x, _n, _nx } = wp.i18n;


const TutorDateRangePicker = () => {
	const dateFormat = window._tutorobject ? window._tutorobject.wp_date_format : 'Y-M-d';

	const [dateRange, setDateRange] = useState([null, null]);
	const [startDate, endDate] = dateRange;
	const dayCount = differenceInDays(endDate, startDate) + 1;

	const handleCalenderChange = (update) => {
		setDateRange(update);
	};

	/**
	 * On apply get formatted date from startDate & endDate
	 * update url & reload
	 */
	const applyDateRange = () => {
		const url = new URL(window.location.href);
		const params = url.searchParams;

		if (startDate && endDate) {
			let startYear = startDate.getFullYear();
			let startMonth = startDate.getMonth() + 1;
			let startDay = startDate.getDate();

			let endYear = endDate.getFullYear();
			let endMonth = endDate.getMonth() + 1;
			let endDay = endDate.getDate();
			// Set start & end date
			let startFormateDate = `${startYear}-${startMonth}-${startDay}`;
			let endFormateDate = `${endYear}-${endMonth}-${endDay}`;
			// Update url
			if (params.has('period')) {
				params.delete('period');
			}
			params.set('start_date', startFormateDate);
			params.set('end_date', endFormateDate);

			window.location = url;
		}
	};

	const handleCalendarClose = () => {
		console.log('adlkjaslkdf');
	};

	const ContainerWrapper = ({ className, children }) => {
		return (
			<CalendarContainer className={className}>
				<div style={{ position: 'relative' }} className="react-datepicker__custom-wrapper">
					{children}
					<div className="react-datepicker__custom-footer">
						<div className="react-datepicker__selected-days-count">
							{dayCount ? (dayCount > 1 ? `${dayCount} days selected` : `${dayCount} day selected`) : __( '0 day selected', 'tutor' )}
						</div>
						<div className="tutor-btns">
							<button
								type="button"
								className="tutor-btn tutor-btn-outline-primary"
								onClick={applyDateRange}
							>
							{__('Apply', 'tutor')}
							</button>
						</div>
					</div>
				</div>
			</CalendarContainer>
		);
	};

	useEffect(() => {
		const url = new URL(window.location.href);
		const params = url.searchParams;
		if (params.has('start_date') && params.has('end_date')) {
			setDateRange([new Date(params.get('start_date')), new Date(params.get('end_date'))]);
		}
	}, []);

	return (
		<div className="tutor-react-datepicker tutor-react-datepicker__selects-range" style={{ width: '100%' }}>
			<DatePicker
				customInput={<CustomInput/>}
				placeholderText={` ${dateFormat} -- ${dateFormat} `}
				showPopperArrow={false}
				shouldCloseOnSelect={false}
				selectsRange={true}
				startDate={startDate}
				endDate={endDate}
				onChange={(update) => {
					handleCalenderChange(update);
				}}
				dateFormat={dateFormat}
				calendarContainer={ContainerWrapper}
			/>
		</div>
	);
};

export default TutorDateRangePicker;
