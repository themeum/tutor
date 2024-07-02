import { getMonth, getYear } from 'date-fns';
import range from 'lodash.range';
import React, { useState } from 'react';
import DatePicker, { CalendarContainer } from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import { CustomInput } from '../CustomInput';

const TutorDateTimePicker = () => {
	const [startDate, setStartDate] = useState(new Date());
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

	const years = range(2000, getYear(new Date()) + 5, 1);

	const months = [
		'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December',
	];

	const handleTimeChange = (e) => {
		const [hour, minute] = e.target.value.split(':');
		const prevDate = new Date(startDate);
		prevDate.setHours(+hour, +minute);
		setStartDate(prevDate);
	};

	const ContainerWrapper = ({ className, children }) => {
		return (
			<CalendarContainer className={className}>
				<div style={{ position: 'relative' }} className="react-datepicker__custom-wrapper">
					{children}
					<div className="react-datepicker__input-time-container">
						<div className="">
							<input
								onChange={handleTimeChange}
								type="time"
								name="tutor-timepicker-input"
								id="tutor-timepicker-input"
							/>
						</div>
					</div>
				</div>
			</CalendarContainer>
		);
	};

	return (
		<div className="tutor-react-datepicker">
			<DatePicker
				customInput={<CustomInput />}
				placeholderText="DD-MM-YYYY"
				selected={startDate}
				onChange={(date) => handleCalendarChange(date)}
				showPopperArrow={false}
				shouldCloseOnSelect={false}
				onCalendarClose={handleCalendarClose}
				onClick={handleCalendarClose}
				timeInputLabel="Time:"
				dateFormat="dd/MM/yyyy h:mm aa"
				calendarContainer={ContainerWrapper}
				renderCustomHeader={({
					date,
					changeYear,
					changeMonth,
					decreaseMonth,
					increaseMonth,
					prevMonthButtonDisabled,
					nextMonthButtonDisabled,
				}) => {
					return (
						<div className="datepicker-header-custom">
							<div className={`dropdown-container dropdown-months ${dropdownMonth ? 'is-active' : ''}`}>
								<div className="dropdown-label" onClick={() => setDropdownMonth(!dropdownMonth)}>
									{months[getMonth(date)]}{' '}
									<svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path
											d="M8.25 9.75L12.5 14.25L16.75 9.75"
											stroke="#212327"
											strokeWidth="1.5"
											strokeLinecap="round"
											strokeLinejoin="round"
										/>
									</svg>
								</div>
								<ul className="dropdown-list">
									{months.map((option) => (
										<li
											key={option}
											data-value={option}
											className={`${option === months[getMonth(date)] ? 'is-current' : ''}`}
											onClick={(e) => {
												const {
													target: {
														dataset: { value },
													},
												} = e;
												changeMonth(months.indexOf(value));
												setDropdownMonth(false);
											}}
										>
											{option}
										</li>
									))}
								</ul>
							</div>

							<div className={`dropdown-container dropdown-years ${dropdownYear ? 'is-active' : ''}`}>
								<div className="dropdown-label" onClick={() => setDropdownYear(!dropdownYear)}>
									{getYear(date)}{' '}
									<svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path
											d="M8.25 9.75L12.5 14.25L16.75 9.75"
											stroke="#212327"
											strokeWidth="1.5"
											strokeLinecap="round"
											strokeLinejoin="round"
										/>
									</svg>
								</div>
								<ul className="dropdown-list">
									{years.map((option) => (
										<li
											key={option}
											data-value={option}
											className={`${option === getYear(date) ? 'is-current' : ''}`}
											onClick={(e) => {
												const {
													target: {
														dataset: { value },
													},
												} = e;
												changeYear(value);
												setDropdownYear(false);
											}}
										>
											{option}
										</li>
									))}
								</ul>
							</div>

							<div className="navigation-icon">
								<button
									onClick={() => {
										decreaseMonth();
										handleCalendarClose();
									}}
									disabled={prevMonthButtonDisabled}
								>
									<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path
											d="M25.9926 20.4027C26.0753 20.4857 26.1404 20.5844 26.184 20.6931C26.2283 20.8067 26.2507 20.9276 26.25 21.0495C26.2489 21.1627 26.2265 21.2746 26.184 21.3795C26.1411 21.4886 26.0759 21.5875 25.9926 21.6699L25.1544 22.5081C24.9787 22.6844 24.7431 22.7881 24.4944 22.7985C24.3734 22.7991 24.253 22.7802 24.138 22.7424C24.029 22.7024 23.93 22.6394 23.8476 22.5576L18.0001 16.6804L12.1361 22.5477C12.0565 22.6367 11.957 22.7057 11.8457 22.749C11.7307 22.7868 11.6103 22.8057 11.4893 22.8051C11.3672 22.797 11.2475 22.7668 11.1362 22.716C11.0281 22.6668 10.9297 22.5987 10.8458 22.5147L10.0076 21.6765C9.92317 21.595 9.8578 21.4958 9.81621 21.3861C9.77002 21.2742 9.74754 21.154 9.75021 21.033C9.75013 20.9197 9.77256 20.8076 9.81621 20.703C9.85865 20.5937 9.9239 20.4947 10.0076 20.4126L17.3566 13.057C17.4329 12.9565 17.5326 12.876 17.647 12.8227C17.7579 12.7728 17.8785 12.748 18.0001 12.7501C18.1224 12.7486 18.2433 12.7757 18.3532 12.8293C18.4698 12.8837 18.5742 12.9612 18.6601 13.057L25.9926 20.4027Z"
											fill="#CDCFD5"
										/>
									</svg>
								</button>
								<button
									onClick={() => {
										increaseMonth();
										handleCalendarClose();
									}}
									disabled={nextMonthButtonDisabled}
								>
									<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path
											d="M10.0076 16.6524C9.92386 16.5703 9.85861 16.4713 9.81617 16.362C9.77025 16.2489 9.7478 16.1276 9.75017 16.0056C9.74936 15.8922 9.77182 15.7799 9.81617 15.6756C9.85776 15.5659 9.92312 15.4667 10.0076 15.3852L10.8458 14.5404C10.9297 14.4564 11.0281 14.3883 11.1362 14.3391C11.2475 14.2883 11.3671 14.2581 11.4892 14.25C11.6103 14.2494 11.7306 14.2683 11.8456 14.3061C11.9542 14.3469 12.0531 14.4098 12.136 14.4909L18.0001 20.3714L23.8641 14.5074C23.9431 14.4177 24.0428 14.3486 24.1545 14.3061C24.2695 14.2683 24.3898 14.2494 24.5109 14.25C24.6329 14.2585 24.7525 14.2887 24.864 14.3391C24.9718 14.3888 25.07 14.4569 25.1544 14.5404L25.9926 15.3786C26.0759 15.461 26.1411 15.5599 26.184 15.669C26.2286 15.7813 26.251 15.9012 26.25 16.0221C26.2485 16.1352 26.2261 16.2471 26.184 16.3521C26.1403 16.4608 26.0752 16.5595 25.9926 16.6425L18.6601 23.9981C18.5838 24.0987 18.4841 24.1791 18.3697 24.2324C18.2588 24.2823 18.1382 24.3071 18.0166 24.305C17.8939 24.3071 17.7725 24.2788 17.6635 24.2225C17.5529 24.1674 17.4543 24.0912 17.3731 23.9981L10.0076 16.6524Z"
											fill="#CDCFD5"
										/>
									</svg>
								</button>
							</div>
						</div>
					);
				}}
			/>
		</div>
	);
};

export default TutorDateTimePicker;
