import React, { useState } from 'react';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import CustomHeader from './CustomHeader';
import { CustomInput } from './CustomInput';
import { translateWeekday } from './utils';
const { __ } = wp.i18n;

const TutorDateTimePicker = (data) => {
	const [startDate, setStartDate] = useState(data.input_value ? new Date(data.input_value) : new Date());
	const [dropdownMonth, setDropdownMonth] = useState(false);
	const [dropdownYear, setDropdownYear] = useState(false);

	const handleCalendarClose = () => {
		setDropdownYear(false);
		setDropdownMonth(false);
	};

	const handleCalendarChange = (date) => {
		setStartDate(date);
		setDropdownYear(false);
		setDropdownMonth(false);
	};

	return (
		<div className="tutor-react-datepicker">
			{data.inline && <input type="hidden" name={data.input_name} value={startDate} />}
			<DatePicker
				inline={data.inline ? true : false}
				customInput={<CustomInput />}
				placeholderText="Y-M-d h:mm aa"
				selected={startDate}
				onChange={(date) => handleCalendarChange(date)}
				showPopperArrow={false}
				shouldCloseOnSelect={false}
				showTimeSelect
				onCalendarClose={handleCalendarClose}
				onClick={handleCalendarClose}
				timeCaption={__('Time', 'tutor')}
				dateFormat="Y-M-d h:mm aa"
				minDate={data.disable_previous ? new Date() : false}
				formatWeekDay={(nameOfDay) => translateWeekday(nameOfDay)}
				calendarStartDay={_tutorobject.start_of_week}
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

export default TutorDateTimePicker;
