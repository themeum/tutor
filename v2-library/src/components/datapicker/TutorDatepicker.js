import { __ } from '@wordpress/i18n';
import { lazy, Suspense, useEffect, useState } from 'react';
import CustomHeader from './CustomHeader';
import { CustomInput } from './CustomInput';
import { stringToDate, translateWeekday, urlPrams } from './utils';

const DatePicker = lazy(() => import(/* webpackChunkName: "tutor-react-datepicker" */'react-datepicker'));

const fallbackElement = (
	<div class="tutor-form-wrap">
		<span class="tutor-form-icon tutor-form-icon-reverse">
			<span class="tutor-icon-calender-line" aria-hidden="true"></span>
		</span>
		<input class="tutor-form-control" placeholder={__('Loading...', 'tutor')} />
	</div>
);

const TutorDatepicker = (data) => {
	let isPreviousDateAllowed = data?.input_name !== 'meeting_date';
	if (data.disable_past_date) {
		isPreviousDateAllowed = false;
	}
	const dateFormat = 'Y-M-d';
	const default_date = data.input_value || null;
	const url = new URL(window.location.href);
	const params = url.searchParams;

	const [startDate, setStartDate] = useState(default_date ? stringToDate(default_date, 'dd-mm-yyyy', '-') : undefined);
	const [dropdownMonth, setDropdownMonth] = useState(false);
	const [dropdownYear, setDropdownYear] = useState(false);

	const handleCalendarClose = () => {
		setDropdownYear(false);
		setDropdownMonth(false);
	};

	const handleCalendarChange = (date) => {
		let year = date?.getFullYear();
		let month = date?.getMonth();
		let day = date?.getDate();

		setStartDate(date);
		setDropdownYear(false);
		setDropdownMonth(false);
		window.location = urlPrams('date', `${year}-${month + 1}-${day}`, date);
	};

	useEffect(() => {
		if (params.has('date') && !!params.get('date')) {
			setStartDate(new Date(params.get('date')));
		}
	}, []);

	return (
		<div className="tutor-react-datepicker">
			<Suspense fallback={fallbackElement}>
				<DatePicker
					customInput={<CustomInput />}
					minDate={isPreviousDateAllowed ? null : new Date()}
					isClearable={Boolean(data.is_clearable)}
					placeholderText={dateFormat}
					selected={startDate}
					name={data.input_name || ''}
					onChange={(date) => (data.prevent_redirect ? setStartDate(date) : handleCalendarChange(date))}
					showPopperArrow={false}
					shouldCloseOnSelect={true}
					onCalendarClose={handleCalendarClose}
					onClick={handleCalendarClose}
					dateFormat={dateFormat}
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
			</Suspense>
		</div>
	);
};

export default TutorDatepicker;
