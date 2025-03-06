import React from 'react';

export const CustomInput = React.forwardRef(
	(props, ref) => {
		const { onChange, placeholder, value, id, onClick, name } = props;
		return (
			<div className="tutor-form-wrap">
				<span className="tutor-form-icon tutor-form-icon-reverse">
					<span className="tutor-icon-calender-line" aria-hidden={true}></span>
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
