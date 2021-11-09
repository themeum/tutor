import React, { useState } from 'react';

import DatePicker, { CalendarContainer } from 'react-datepicker';
import { differenceInDays } from 'date-fns';

// import 'react-datepicker/dist/react-datepicker.css';
// import './TutorDatepicker.scss';
// import '../../../bundle/main.min.css';

const TutorDateRangePicker = () => {
	const [dateRange, setDateRange] = useState([null, null]);
	const [startDate, endDate] = dateRange;
	const dayCount = differenceInDays(endDate, startDate);

	const handleCalenderChange = (update) => {
		setDateRange(update);
	};

	const ContainerWrapper = ({ className, children }) => {
		return (
			<CalendarContainer className={className}>
				<div style={{ position: 'relative' }} className="react-datepicker__custom-wrapper">
					{children}
					<div className="react-datepicker__custom-footer">
						<div className="react-datepicker__selected-days-count">
							{dayCount ? (dayCount > 1 ? `${dayCount} days selected` : `${dayCount} day selected`) : '0 day selected'}
						</div>
						<div className="tutor-btns">
							<button class="tutor-btn tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-md">
								Cancel
							</button>
							<button class="tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-md">Apply</button>
						</div>
					</div>
				</div>
			</CalendarContainer>
		);
	};

	return (
		<div className="tutor-react-datepicker tutor-react-datepicker__selects-range">
			<DatePicker
				placeholderText="DD-MM-YYYY"
				showPopperArrow={false}
				shouldCloseOnSelect={false}
				selectsRange={true}
				startDate={startDate}
				endDate={endDate}
				onChange={(update) => {
					handleCalenderChange(update);
				}}
				dateFormat="dd/MM/yyyy"
				calendarContainer={ContainerWrapper}
			/>
		</div>
	);
};

export default TutorDateRangePicker;
