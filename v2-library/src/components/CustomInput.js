import React from 'react';

export const CustomInput = React.forwardRef(
	({ onChange, placeholder, value, id, onClick, name }, ref) => {
		return (
			<div className="tutor-form-wrap">
				<span className="tutor-form-icon tutor-form-icon-reverse">
					<span className="tutor-icon-calender" aria-hidden={true}></span>
				</span>
				<input
					ref={ref}
					className="tutor-form-control"
					onChange={onChange}
					placeholder={placeholder}
					value={value}
					id={id}
					onClick={onClick}
					name={name}
				/>
			</div>
		);
	},
);
