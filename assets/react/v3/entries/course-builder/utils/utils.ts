import type { CourseDetailsResponse, CourseFormData, CoursePayload } from '@CourseBuilderServices/course';

export const convertCourseDataToPayload = (data: CourseFormData): CoursePayload => {
	return {
		post_date: data.post_date,
		post_title: data.post_title,
		post_name: data.post_name,
		post_content: data.post_content,
		post_status: data.post_password.length ? 'public' : data.post_status,
		post_password: data.post_password,
		post_author: data.post_author?.id ?? null,
		...(data.video && {
			source_type: '',
			source: '',
		}),
		course_categories: data.course_categories,
		course_tags: data.course_tags.map((item) => item.id),
		thumbnail_id: data.thumbnail?.id ?? null,
		enable_qna: data.enable_qna ? 'yes' : 'no',
		is_public_course: data.is_public_course ? 'yes' : 'no',
		course_level: data.course_level,
		_tutor_course_settings: {
			maximum_students: Number(data.maximum_students),
			// enable_content_drip: data.enable_content_drip,
			// content_drip_type: data.content_drip_type,
		},
	};
};

export const convertCourseDataToFormData = (courseDetails: CourseDetailsResponse): CourseFormData => {
	return {
		post_date: courseDetails.post_date,
		post_title: courseDetails.post_title,
		post_name: courseDetails.post_name,
		post_content: courseDetails.post_content,
		post_status: courseDetails.post_status,
		post_password: courseDetails.post_password,
		post_author: {
			id: Number(courseDetails.post_author.ID),
			name: courseDetails.post_author.display_name,
			email: courseDetails.post_author.user_email,
			avatar_url: courseDetails.post_author.tutor_profile_photo_url,
		},
		thumbnail: {
			id: null,
			url: courseDetails.thumbnail,
		},
		video: {
			source_type: '',
			source: '',
		},
		course_price_type: courseDetails.course_price_type,
		course_price: courseDetails.course_price,
		course_sale_price: courseDetails.course_sale_price,
		course_categories: courseDetails.course_categories.map((item) => item.term_id),
		course_tags: courseDetails.course_tags.map((item) => {
			return {
				id: item.term_id,
				name: item.name,
			};
		}),
		course_instructors: [],
		enable_qna: courseDetails.enable_qna === 'yes' ? true : false,
		is_public_course: courseDetails.is_public_course === 'yes' ? true : false,
		course_level: courseDetails.course_level,
		maximum_students: courseDetails.course_settings.maximum_students,
		enrollment_expiration: '',
	};
};

export const getCourseId = () => {
	const params = new URLSearchParams(window.location.search);
	const courseId = params.get('course_id');
	return Number(courseId);
};
