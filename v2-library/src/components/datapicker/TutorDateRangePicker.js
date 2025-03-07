import { differenceInDays } from 'date-fns';
import React, { useEffect, useState } from 'react';
import DatePicker, { CalendarContainer } from 'react-datepicker';
import CustomHeader from './CustomHeader';
import { CustomInput } from './CustomInput';
import { translateWeekday } from './utils';
const { __, sprintf, _n } = wp.i18n;


const TutorDateRangePicker = () => {
	const dateFormat = 'Y-M-d';

	const [dropdownMonth, setDropdownMonth] = useState(false);
	const [dropdownYear, setDropdownYear] = useState(false);

	const [dateRange, setDateRange] = useState([null, null]);
	const [startDate, endDate] = dateRange;
	const dayCount = differenceInDays(endDate, startDate) + 1;

	const handleCalenderChange = (update) => {
		setDateRange(update);
	};

	const handleCalendarClose = () => {
		setDropdownYear(false);
		setDropdownMonth(false);
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

	const ContainerWrapper = ({ className, children }) => {
		return (
			<CalendarContainer className={className}>
				<div style={{ position: 'relative' }} className="react-datepicker__custom-wrapper">
					{children}
					<div className="react-datepicker__custom-footer">
						<div className="react-datepicker__selected-days-count">
							{dayCount ? sprintf(_n('%d day selected', '%d days selected', dayCount, 'tutor'), dayCount) : __('0 day selected', 'tutor')}
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
				customInput={<CustomInput />}
				placeholderText={` ${dateFormat} -- ${dateFormat} `}
				showPopperArrow={false}
				shouldCloseOnSelect={false}
				selectsRange={true}
				startDate={startDate}
				endDate={endDate}
				onChange={handleCalenderChange}
				onCalendarClose={handleCalendarClose}
				onClick={handleCalendarClose}
				dateFormat={dateFormat}
				formatWeekDay={(nameOfDay) => translateWeekday(nameOfDay)}
				calendarStartDay={wp.date?.getSettings().l10n.startOfWeek}
				calendarContainer={ContainerWrapper}
				renderCustomHeader={(props) => (
					<CustomHeader
						{...props}
						dropdownMonth={dropdownMonth}
						setDropdownMonth={setDropdownMonth}
						dropdownYear={dropdownYear}
						setDropdownYear={setDropdownYear}
						handleCalendarClose={handleCalendarClose}
					/>
				)}
			/>
		</div>
	);
};

export default TutorDateRangePicker;
