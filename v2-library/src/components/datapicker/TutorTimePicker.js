import React from 'react';

const TutorTimePicker = ({ handleTimeChange }) => {
	return (
		<div className="">
			<input
				onChange={handleTimeChange}
				onFocus={console.log('foucs')}
				type="time"
				name="tutor-timepicker-input"
				id="tutor-timepicker-input"
			/>
		</div>
	);
};

export default TutorTimePicker;
