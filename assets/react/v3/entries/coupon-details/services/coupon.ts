import { useToast } from '@Atoms/Toast';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { authApiInstance, wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import { ErrorResponse } from '@Utils/form';

export type CouponType = 'code' | 'automatic';

interface Course {
	id: number;
	title: string;
	image: '';
	author: string;
	regular_price: number;
	regular_price_formatted: string;
}

interface Category {
	id: number;
	title: string;
	image: '';
	number_of_courses: number;
}

export interface Coupon {
	id: number;
	coupon_type: CouponType;
	coupon_name: string;
	code: string;
	user_name: string;
	discount_type: 'percent' | 'amount';
	discount_value: number;
	applies_to:
		| 'all_courses_and_bundles'
		| 'all_courses'
		| 'all_bundles'
		| 'specific_courses'
		| 'specific_bundles'
		| 'specific_category';
	courses?: Course[];
	categories?: Category[];
	bundles?: Course[];
	usage_limit_status: boolean;
	usage_limit_value: number;
	is_limit_to_one_use_per_customer: boolean;
	purchase_requirements: 'no_minimum' | 'minimum_purchase' | 'minimum_quantity';
	purchase_requirements_value: number;
	start_date: string;
	start_time: string;
	is_end_enabled: boolean;
	end_date: string;
	end_time: string;
	created_at: string;
	updated_at?: string;
}

export const couponInitialValue: Coupon = {
	id: 0,
	coupon_type: 'code',
	coupon_name: '',
	code: '',
	user_name: 'User',
	discount_type: 'amount',
	discount_value: 0,
	applies_to: 'all_courses_and_bundles',
	courses: [],
	categories: [],
	bundles: [],
	usage_limit_status: false,
	usage_limit_value: 0,
	is_limit_to_one_use_per_customer: false,
	purchase_requirements: 'no_minimum',
	purchase_requirements_value: 0,
	start_date: '',
	start_time: '',
	is_end_enabled: false,
	end_date: '02/16/2024',
	end_time: '',
	created_at: '02/16/2024 10:00:00',
};

const mockCouponData: Coupon = {
	id: 11211,
	coupon_type: 'code',
	coupon_name: 'Winter sale',
	code: 'WINTER24',
	user_name: 'John Doe',
	discount_type: 'amount',
	discount_value: 20,
	applies_to: 'all_courses_and_bundles',
	courses: [
		{
			id: 1,
			title: 'Soccer for Beginners',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
		{
			id: 2,
			title: 'Soccer for Beginners',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
		{
			id: 3,
			title: 'Soccer for Beginners',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
		{
			id: 4,
			title: 'Soccer for Beginners',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
	],
	categories: [
		{
			id: 1,
			title: 'Soccer',
			image: '',
			number_of_courses: 10,
		},
		{
			id: 2,
			title: 'Basketball',
			image: '',
			number_of_courses: 5,
		},
		{
			id: 3,
			title: 'Tennis',
			image: '',
			number_of_courses: 15,
		},
		{
			id: 4,
			title: 'Volleyball',
			image: '',
			number_of_courses: 8,
		},
		{
			id: 5,
			title: 'Swimming',
			image: '',
			number_of_courses: 12,
		},
	],
	bundles: [
		{
			id: 1,
			title: 'Soccer Bundle',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
		{
			id: 2,
			title: 'Soccer Bundle',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
		{
			id: 3,
			title: 'Soccer Bundle',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
		{
			id: 4,
			title: 'Soccer Bundle',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
	],
	usage_limit_status: true,
	usage_limit_value: 100,
	is_limit_to_one_use_per_customer: true,
	purchase_requirements: 'minimum_purchase',
	purchase_requirements_value: 200,
	start_date: '02/16/2024',
	start_time: '10:00:00',
	is_end_enabled: true,
	end_date: '02/16/2024',
	end_time: '10:00:00',
	created_at: '02/16/2024 10:00:00',
	updated_at: '02/16/2024 10:00:00',
};

const getCouponDetails = (couponId: number) => {
	return mockCouponData;
	// biome-ignore lint/correctness/noUnreachable: <will be implemented later>
	return wpAjaxInstance
		.get<Coupon>(endpoints.COUPON_DETAILS, { params: { coupon_id: couponId } })
		.then((response) => response.data);
};

export const useCouponDetailsQuery = (couponId: number) => {
	return useQuery({
		enabled: !!couponId,
		queryKey: ['CouponDetails', couponId],
		queryFn: () => getCouponDetails(couponId),
	});
};

interface CouponResponse {
	id: number;
	message: string;
	status_code: number;
}

const createCoupon = (payload: Coupon) => {
	return authApiInstance.post<Coupon, CouponResponse>(endpoints.ADMIN_AJAX, {
		action: 'tutor_create_coupon',
		...payload,
	});
};

export const useCreateCouponMutation = () => {
	const { showToast } = useToast();

	return useMutation({
		mutationFn: createCoupon,
		onSuccess: (response) => {
			showToast({ type: 'success', message: response.message });
		},
		onError: (error: ErrorResponse) => {
			showToast({ type: 'danger', message: error.response.data.message });
		},
	});
};

const updateCoupon = (payload: Coupon) => {
	return authApiInstance.post<Coupon, CouponResponse>(endpoints.ADMIN_AJAX, {
		action: 'tutor_update_coupon',
		...payload,
	});
};

export const useUpdateCouponMutation = () => {
	const { showToast } = useToast();
	const queryClient = useQueryClient();

	return useMutation({
		mutationFn: updateCoupon,
		onSuccess: (response) => {
			showToast({ type: 'success', message: response.message });
			queryClient.invalidateQueries({
				queryKey: ['CouponDetails', response.id],
			});
		},
		onError: (error: ErrorResponse) => {
			showToast({ type: 'danger', message: error.response.data.message });
		},
	});
};
